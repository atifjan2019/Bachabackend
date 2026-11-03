<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image',
        'image_alt',
        'slug',
        'meta_title',
        'meta_description',
        'parent_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /** Only categories the storefront may show. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Admin-chosen order first (lower number first, blank last), then the
     * order categories were created in — what the storefront always showed,
     * so nothing moves until the admin numbers it. `IS NULL` sorts false
     * before true on both MySQL and SQLite.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByRaw('sort_order IS NULL')->orderBy('sort_order')->orderBy('id');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category', 'slug');
    }

    /**
     * A category's own slug plus every descendant slug, at any depth.
     *
     * Products store a single category slug, so anything that asks "what is in
     * this category" has to expand to the whole subtree. Without it a parent
     * such as "Waistcoats for Men & Boys" reports nothing while its child
     * collections hold the entire inventory — which is what the storefront API
     * and the admin category list both used to do.
     */
    public static function descendantSlugs(string $slug): array
    {
        [$byParent, $idForSlug] = self::tree($slug);

        // Unknown slug: pass it through so the caller's query simply finds
        // nothing, matching the old behaviour for a bad ?category= value.
        if ($idForSlug === null) {
            return [$slug];
        }

        $slugs = [$slug];
        $queue = [$idForSlug];
        // Iterative walk with a visited guard, so a malformed parent_id cycle
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

    /**
     * Product totals per category id, counting the category's whole subtree.
     *
     * Two queries for the entire tree, so a list of categories doesn't turn
     * into one count query per row.
     *
     * With $storefront the totals count only what a shopper can actually see:
     * active products inside categories that aren't hidden.
     *
     * @return array<int,int>
     */
    public static function subtreeProductCounts(bool $storefront = false): array
    {
        $perSlug = Product::query()
            ->when($storefront, fn ($q) => $q->storefront())
            ->selectRaw('category, COUNT(*) as aggregate')
            ->whereNotNull('category')
            ->groupBy('category')
            ->pluck('aggregate', 'category')
            ->all();

        $counts = [];
        foreach (self::rows() as $row) {
            $total = 0;
            foreach (self::descendantSlugs($row->slug) as $slug) {
                $total += (int) ($perSlug[$slug] ?? 0);
            }
            $counts[$row->id] = $total;
        }

        return $counts;
    }

    /**
     * Slugs the storefront must not show: every inactive category plus its
     * whole subtree, since a child of a hidden parent has no way to be reached.
     * Products filed under any of these slugs are hidden with them.
     *
     * @return list<string>
     */
    public static function hiddenSlugs(): array
    {
        return Cache::remember('categories.hidden', 300, function () {
            $slugs = [];
            foreach (self::rows() as $row) {
                if (! $row->is_active && $row->slug !== null) {
                    foreach (self::descendantSlugs($row->slug) as $slug) {
                        $slugs[$slug] = true;
                    }
                }
            }

            return array_keys($slugs);
        });
    }

    /** id/parent_id/slug/is_active for every category, cached for the request burst. */
    private static function rows(): array
    {
        return Cache::remember(
            'categories.tree',
            300,
            fn () => self::query()->get(['id', 'parent_id', 'slug', 'is_active'])->all()
        );
    }

    /** @return array{0: array<int|null, list<self>>, 1: int|null} */
    private static function tree(string $slug): array
    {
        $byParent = [];
        $idForSlug = null;
        foreach (self::rows() as $row) {
            $byParent[$row->parent_id][] = $row;
            if ($row->slug === $slug) {
                $idForSlug = $row->id;
            }
        }

        return [$byParent, $idForSlug];
    }
}

