<?php

namespace App\Support\Storefront;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Cached catalogue reads shared by the layout (mega menu, filters) and the
 * shop pages. Mirrors what /api/v1/categories returns, including subtree
 * product counts, so the storefront and the API never disagree.
 */
class Catalog
{
    public const TTL = 300;

    /** Columns a grid card needs; detail pages load the full row. */
    public const CARD_COLUMNS = [
        'id', 'name', 'slug', 'sku', 'category', 'price', 'original_price',
        'image', 'gallery', 'sizes', 'colors', 'is_new', 'is_featured',
        'is_best_seller', 'label', 'created_at', 'updated_at',
    ];

    /**
     * Top-level categories with their children, each carrying `products_count`
     * for its whole subtree.
     *
     * @return Collection<int, Category>
     */
    public static function categoryTree(): Collection
    {
        return Cache::remember('storefront.categories.tree', self::TTL, function () {
            $columns = ['id', 'name', 'slug', 'image', 'parent_id'];
            $tree = Category::query()
                ->whereNull('parent_id')
                ->select($columns)
                ->with(['children' => fn ($q) => $q->select($columns)->orderBy('id')])
                ->orderBy('id')
                ->get();

            $counts = Category::subtreeProductCounts();
            $tree->each(function (Category $parent) use ($counts) {
                $parent->children?->each(
                    fn (Category $child) => $child->setAttribute('products_count', $counts[$child->id] ?? 0)
                );
                $parent->setAttribute('products_count', $counts[$parent->id] ?? 0);
            });

            return $tree;
        });
    }

    /** @return Collection<int, Category> Every category, parents and children, flattened. */
    public static function flatCategories(): Collection
    {
        return self::categoryTree()->flatMap(fn (Category $c) => collect([$c])->merge($c->children ?? []));
    }

    /** Locate a category anywhere in the tree by slug. */
    public static function findCategory(string $slug): ?Category
    {
        return self::flatCategories()->first(fn (Category $c) => $c->slug === $slug);
    }

    /** @return array<string,string> slug → name for every category. */
    public static function categoryNames(): array
    {
        return Cache::remember('storefront.categories.names', self::TTL, function () {
            try {
                return Category::query()->whereNotNull('slug')->pluck('name', 'slug')->all();
            } catch (\Throwable) {
                return [];
            }
        });
    }

    /** Column names present on products, so pre-migration servers keep working. */
    public static function cardColumns(): array
    {
        return array_values(array_filter(self::CARD_COLUMNS, function ($column) {
            return in_array($column, ['sku', 'colors'], true) ? Product::hasColumn($column) : true;
        }));
    }

    /** Query builder for grid cards: slim columns, newest first. */
    public static function cards(): Builder
    {
        return Product::query()->select(self::cardColumns())->orderByDesc('id');
    }

    /**
     * SQL expression for the price a customer pays. `price` is always the
     * amount charged: when `original_price` is higher the product is on sale
     * and `price` is the sale price; otherwise `price` is the only price. The
     * column is a string in the schema, so cast it before comparing/sorting.
     */
    public static function effectivePriceSql(): string
    {
        $driver = Product::query()->getConnection()->getDriverName();

        return $driver === 'mysql'
            ? "CAST(COALESCE(NULLIF(price, ''), '0') AS DECIMAL(12,2))"
            : "CAST(COALESCE(NULLIF(price, ''), '0') AS REAL)";
    }

    /** Cached storefront products (as presenters) for a merchandising flag. */
    public static function featured(int $limit = 8): Collection
    {
        return Cache::remember("storefront.products.featured.$limit", self::TTL, function () use ($limit) {
            return StorefrontProduct::collection(self::cards()->where('is_featured', true)->limit($limit)->get());
        });
    }

    /** Best sellers, falling back to the latest products so the section is never empty. */
    public static function bestSellers(int $limit = 8): Collection
    {
        return Cache::remember("storefront.products.best.$limit", self::TTL, function () use ($limit) {
            $sellers = StorefrontProduct::collection(self::cards()->where('is_best_seller', true)->limit($limit)->get());
            if ($sellers->isNotEmpty()) {
                return $sellers;
            }

            return StorefrontProduct::collection(self::cards()->limit($limit)->get());
        });
    }

    /** Up to $limit other products from the same category. */
    public static function related(StorefrontProduct $product, int $limit = 4): Collection
    {
        if ($product->category['slug'] === '') {
            return collect();
        }

        return StorefrontProduct::collection(
            self::cards()
                ->whereIn('category', Category::descendantSlugs($product->category['slug']))
                ->where('id', '<>', $product->id)
                ->limit($limit)
                ->get()
        );
    }
}
