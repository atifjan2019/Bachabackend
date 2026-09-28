<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CatalogController extends Controller
{
    /**
     * How long public catalog reads may be reused. The admin panel flushes the
     * application cache on every save, but shared/CDN caches can't be purged
     * from here — so keep browsers revalidating (max-age=0) while letting a
     * shared cache absorb bursts for a minute and serve slightly stale data
     * while it refreshes in the background.
     */
    private const CATALOG_CACHE = 'public, max-age=0, s-maxage=60, stale-while-revalidate=300';

    public function categories(Request $request): JsonResponse
    {
        // The storefront uses this for the sidebar, mega menu and filters: it
        // needs names, slugs, hierarchy, counts and the tile image. Long-form
        // descriptions and SEO metadata were over half the payload and nothing
        // on the storefront reads them, so they're opt-in via ?full=1.
        $slim = !$request->boolean('full');
        $columns = $slim
            ? ['id', 'name', 'slug', 'image', 'parent_id']
            : ['*'];

        $categories = Cache::remember(
            'api.categories.' . ($slim ? 'slim' : 'full'),
            300,
            function () use ($columns) {
                $tree = Category::query()
                    ->whereNull('parent_id')
                    ->select($columns)
                    ->with(['children' => fn ($q) => $q->select($columns)->orderBy('id')])
                    ->orderBy('id')
                    ->get();

                // products_count must describe what clicking the category
                // actually returns. Products are filed under leaf slugs, so a
                // parent counting only its own direct rows always reported 0
                // while its children held the entire inventory.
                $counts = $this->productCountsBySlug();
                $tree->each(function (Category $parent) use ($counts) {
                    $parent->children?->each(function (Category $child) use ($counts) {
                        $child->setAttribute(
                            'products_count',
                            $this->subtreeCount($child->slug, $counts)
                        );
                    });
                    $parent->setAttribute(
                        'products_count',
                        $this->subtreeCount($parent->slug, $counts)
                    );
                });

                return $tree;
            }
        );

        return response()->json(['data' => $categories])
            ->header('Cache-Control', self::CATALOG_CACHE);
    }

    /** Product totals keyed by the slug they are filed under. */
    private function productCountsBySlug(): array
    {
        return Product::query()
            ->selectRaw('category, COUNT(*) as aggregate')
            ->whereNotNull('category')
            ->groupBy('category')
            ->pluck('aggregate', 'category')
            ->all();
    }

    /** Products filed under this slug plus every slug beneath it. */
    private function subtreeCount(string $slug, array $counts): int
    {
        $total = 0;
        foreach ($this->descendantSlugs($slug) as $s) {
            $total += (int) ($counts[$s] ?? 0);
        }

        return $total;
    }

    /**
     * A category's own slug plus every descendant slug, at any depth.
     *
     * Products store a single category slug, so filtering by a parent has to
     * expand to the whole subtree — otherwise "Waistcoats for Men & Boys"
     * returns nothing while its child collections hold all 29 products, and the
     * storefront is forced to fan out one request per child to compensate.
     */
    private function descendantSlugs(string $slug): array
    {
        $rows = Cache::remember('api.category.tree', 300, fn () => Category::query()
            ->get(['id', 'parent_id', 'slug'])
            ->all());

        $byParent = [];
        $idForSlug = null;
        foreach ($rows as $row) {
            $byParent[$row->parent_id][] = $row;
            if ($row->slug === $slug) {
                $idForSlug = $row->id;
            }
        }

        // Unknown slug: pass it through so the query simply finds nothing,
        // matching the old behaviour for a bad ?category= value.
        if ($idForSlug === null) {
            return [$slug];
        }

        $slugs = [$slug];
        $queue = [$idForSlug];
        // Iterative walk with a visited guard so a malformed parent_id cycle
        // can't spin forever.
        $seen = [$idForSlug => true];
        while ($queue) {
            $id = array_pop($queue);
            foreach ($byParent[$id] ?? [] as $child) {
                if (isset($seen[$child->id])) {
                    continue;
                }
                $seen[$child->id] = true;
                $slugs[] = $child->slug;
                $queue[] = $child->id;
            }
        }

        return $slugs;
    }

    public function products(Request $request): JsonResponse
    {
        $request->validate([
            'category' => 'nullable|string|max:100',
            'search' => 'nullable|string|max:255',
            'is_new' => 'nullable|boolean',
            'featured' => 'nullable|boolean',
            'best_seller' => 'nullable|boolean',
            'label' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $perPage = (int) ($request->integer('per_page') ?: 20);
        $query = Product::query()->orderByDesc('id');

        // A grid card needs a name, price, one image and the merchandising
        // flags. Shipping full descriptions and accordion content made them
        // ~83% of a 122 KB listing response. Detail pages use /products/{slug},
        // which is untouched; ?full=1 restores the old shape for any client
        // that still wants it.
        if (!$request->boolean('full')) {
            $query->select([
                'id', 'name', 'slug', 'category', 'price', 'original_price',
                'image', 'gallery', 'sizes', 'is_new', 'is_featured',
                'is_best_seller', 'label', 'created_at',
            ]);
        }

        if ($request->filled('category')) {
            // Expand to the whole subtree: products are filed under leaf slugs,
            // so a parent category matched nothing on its own.
            $query->whereIn(
                'category',
                $this->descendantSlugs((string) $request->string('category'))
            );
        }

        if ($request->filled('search')) {
            $search = (string) $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!is_null($request->query('is_new'))) {
            $query->where('is_new', $request->boolean('is_new'));
        }

        if (!is_null($request->query('featured'))) {
            $query->where('is_featured', $request->boolean('featured'));
        }

        if (!is_null($request->query('best_seller'))) {
            $query->where('is_best_seller', $request->boolean('best_seller'));
        }

        if ($request->filled('label')) {
            $query->where('label', $request->string('label'));
        }

        $data = $query->paginate($perPage)->appends($request->query());

        return response()->json($data)
            ->header('Cache-Control', self::CATALOG_CACHE);
    }

    public function product(string $slugOrId): JsonResponse
    {
        $product = Product::query()
            ->where('slug', $slugOrId)
            ->orWhere('id', $slugOrId)
            ->firstOrFail();

        return response()->json(['data' => $product])
            ->header('Cache-Control', self::CATALOG_CACHE);
    }

    public function reviews(string $slugOrId): JsonResponse
    {
        $product = Product::query()
            ->where('slug', $slugOrId)
            ->orWhere('id', $slugOrId)
            ->firstOrFail();

        $reviews = $product->reviews()
            ->where('is_approved', true)
            ->orderByDesc('id')
            ->get(['id', 'author_name', 'rating', 'comment', 'created_at']);

        return response()->json([
            'data' => $reviews,
            'meta' => [
                'count' => $reviews->count(),
                'average' => round((float) $reviews->avg('rating'), 1),
            ],
        ]);
    }

    public function storeReview(Request $request, string $slugOrId): JsonResponse
    {
        $product = Product::query()
            ->where('slug', $slugOrId)
            ->orWhere('id', $slugOrId)
            ->firstOrFail();

        $validated = $request->validate([
            'author_name' => 'required|string|max:120',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $review = $product->reviews()->create([
            'author_name' => $validated['author_name'],
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_approved' => true,
        ]);

        return response()->json([
            'message' => 'Review submitted successfully.',
            'data' => $review->only(['id', 'author_name', 'rating', 'comment', 'created_at']),
        ], 201);
    }
}
