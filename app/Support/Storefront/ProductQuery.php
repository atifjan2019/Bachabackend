<?php

namespace App\Support\Storefront;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * The shop listing query. The URL is the single source of truth (as in the
 * Next.js ProductsClient): category (repeatable or comma list), search, tab,
 * sort, min, max, size (repeatable), page. Everything runs server-side in SQL.
 */
class ProductQuery
{
    public const PRICE_MIN = 0;

    public const PRICE_MAX = 15000;

    public const PRICE_STEP = 500;

    public const PER_PAGE = 12;

    public const COLLECTIONS = ['all' => 'All', 'featured' => 'Featured', 'best_seller' => 'Best Sellers', 'new' => 'New Arrivals'];

    public const SORTS = ['newest' => 'Newest', 'price_asc' => 'Price: Low to High', 'price_desc' => 'Price: High to Low'];

    public const CACHE_TTL = 60;

    /** @var list<string> */
    public array $categories = [];

    public string $search = '';

    public string $collection = 'all';

    public string $sort = 'newest';

    public int $min = self::PRICE_MIN;

    public int $max = self::PRICE_MAX;

    /** @var list<string> */
    public array $sizes = [];

    public int $page = 1;

    private ?LengthAwarePaginator $results = null;

    public static function fromRequest(Request $request): self
    {
        $q = new self;
        $q->categories = self::listParam($request, 'category');
        $q->sizes = self::listParam($request, 'size');
        $q->search = trim((string) $request->query('search', ''));

        $tab = (string) $request->query('tab', 'all');
        $q->collection = array_key_exists($tab, self::COLLECTIONS) ? $tab : 'all';

        $sort = (string) $request->query('sort', 'newest');
        $q->sort = array_key_exists($sort, self::SORTS) ? $sort : 'newest';

        $min = $request->query('min');
        $max = $request->query('max');
        $q->min = is_numeric($min) ? max(self::PRICE_MIN, min(self::PRICE_MAX, (int) $min)) : self::PRICE_MIN;
        $q->max = is_numeric($max) ? max(self::PRICE_MIN, min(self::PRICE_MAX, (int) $max)) : self::PRICE_MAX;
        if ($q->min > $q->max) {
            [$q->min, $q->max] = [$q->max, $q->min];
        }

        $page = (int) $request->query('page', 1);
        $q->page = $page > 0 ? $page : 1;

        return $q;
    }

    /**
     * Repeated keys (?category=a&category=b), bracket arrays (category[]=a) and
     * comma lists (category=a,b) all normalise to one unique list.
     *
     * @return list<string>
     */
    private static function listParam(Request $request, string $key): array
    {
        $raw = [];
        $value = $request->query($key);
        if (is_array($value)) {
            $raw = array_merge($raw, array_values($value));
        } elseif (is_string($value)) {
            $raw[] = $value;
        }

        // PHP keeps only the last repeated bare key, so read the raw query string too.
        $qs = (string) $request->server('QUERY_STRING', '');
        foreach (explode('&', $qs) as $pair) {
            if ($pair === '' || ! str_contains($pair, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $pair, 2);
            if (urldecode($k) === $key) {
                $raw[] = urldecode($v);
            }
        }

        $out = [];
        foreach ($raw as $item) {
            foreach (explode(',', (string) $item) as $part) {
                $part = trim($part);
                if ($part !== '' && mb_strlen($part) <= 100 && ! in_array($part, $out, true)) {
                    $out[] = $part;
                }
            }
        }

        return $out;
    }

    /* ------------------------------------------------------------------ */
    /* URL building                                                        */
    /* ------------------------------------------------------------------ */

    /** Query params as they stand (defaults omitted). */
    public function params(): array
    {
        $params = [];
        if ($this->categories) {
            $params['category'] = $this->categories;
        }
        if ($this->search !== '') {
            $params['search'] = $this->search;
        }
        if ($this->collection !== 'all') {
            $params['tab'] = $this->collection;
        }
        if ($this->sort !== 'newest') {
            $params['sort'] = $this->sort;
        }
        if ($this->min > self::PRICE_MIN) {
            $params['min'] = $this->min;
        }
        if ($this->max < self::PRICE_MAX) {
            $params['max'] = $this->max;
        }
        if ($this->sizes) {
            $params['size'] = $this->sizes;
        }
        if ($this->page > 1) {
            $params['page'] = $this->page;
        }

        return $params;
    }

    /**
     * The listing URL with changes applied. null removes a key; arrays write
     * repeated params. Filter changes drop the page unless asked otherwise.
     */
    public function url(array $changes = [], bool $keepPage = false): string
    {
        $params = $this->params();
        if (! $keepPage) {
            unset($params['page']);
        }
        foreach ($changes as $key => $value) {
            unset($params[$key]);
            if ($value === null || $value === [] || $value === '') {
                continue;
            }
            $params[$key] = $value;
        }

        return self::buildUrl($params);
    }

    public static function buildUrl(array $params): string
    {
        $parts = [];
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $values = array_values(array_unique($value));
                if (count($values) === 1) {
                    $parts[] = rawurlencode($key).'='.rawurlencode((string) $values[0]);
                } else {
                    foreach ($values as $v) {
                        $parts[] = rawurlencode($key).'[]='.rawurlencode((string) $v);
                    }
                }
            } else {
                $parts[] = rawurlencode($key).'='.rawurlencode((string) $value);
            }
        }
        $base = route('shop.products');

        return $parts ? $base.'?'.implode('&', $parts) : $base;
    }

    /** Toggle a category slug on/off (resets size and page, as the React app did). */
    public function toggleCategoryUrl(string $slug): string
    {
        $next = in_array($slug, $this->categories, true)
            ? array_values(array_diff($this->categories, [$slug]))
            : [...$this->categories, $slug];

        return $this->url(['category' => $next, 'size' => null]);
    }

    public function toggleSizeUrl(string $size): string
    {
        $next = in_array($size, $this->sizes, true)
            ? array_values(array_diff($this->sizes, [$size]))
            : [...$this->sizes, $size];

        return $this->url(['size' => $next]);
    }

    public function clearAllUrl(): string
    {
        return $this->url(['category' => null, 'size' => null, 'min' => null, 'max' => null]);
    }

    public function pageUrl(int $page): string
    {
        return $this->url(['page' => $page > 1 ? $page : null], false);
    }

    /** Canonical path: a single category is a landing page; everything else is /products. */
    public function canonicalPath(): string
    {
        if (count($this->categories) === 1 && $this->search === '') {
            return '/products?category='.rawurlencode($this->categories[0]);
        }

        return '/products';
    }

    /** Hidden inputs that carry the current params through a GET form. */
    public function hiddenParams(array $except = []): array
    {
        $out = [];
        foreach ($this->params() as $key => $value) {
            if (in_array($key, $except, true) || $key === 'page') {
                continue;
            }
            foreach ((array) $value as $v) {
                $out[] = [is_array($value) ? $key.'[]' : $key, (string) $v];
            }
        }

        return $out;
    }

    /* ------------------------------------------------------------------ */
    /* State                                                               */
    /* ------------------------------------------------------------------ */

    public function priceActive(): bool
    {
        return $this->min > self::PRICE_MIN || $this->max < self::PRICE_MAX;
    }

    /** Sizes selected that actually exist on the browsed products. */
    public function activeSizes(): array
    {
        $options = $this->sizeOptions();

        return array_values(array_filter($this->sizes, fn ($s) => in_array($s, $options, true)));
    }

    public function activeFilterCount(): int
    {
        return count($this->categories) + count($this->activeSizes()) + ($this->priceActive() ? 1 : 0);
    }

    /** The single selected category (drives the hero), if exactly one is selected. */
    public function activeCategory(): ?Category
    {
        return count($this->categories) === 1 ? Catalog::findCategory($this->categories[0]) : null;
    }

    public function categoryName(string $slug): string
    {
        return Catalog::findCategory($slug)?->name ?? StorefrontProduct::categoryName($slug);
    }

    /* ------------------------------------------------------------------ */
    /* Query                                                               */
    /* ------------------------------------------------------------------ */

    /** Category / search / collection — the part shared with the size options. */
    private function baseQuery(): Builder
    {
        $query = Catalog::cards();

        if ($this->categories) {
            $slugs = [];
            foreach ($this->categories as $slug) {
                $slugs = array_merge($slugs, Category::descendantSlugs($slug));
            }
            $query->whereIn('category', array_values(array_unique($slugs)));
        }

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
                if (Product::hasColumn('sku')) {
                    $q->orWhere('sku', 'like', "%{$search}%");
                }
            });
        }

        match ($this->collection) {
            'featured' => $query->where('is_featured', true),
            'best_seller' => $query->where('is_best_seller', true),
            'new' => $query->where('is_new', true),
            default => null,
        };

        return $query;
    }

    private function filteredQuery(): Builder
    {
        $query = $this->baseQuery();
        $price = Catalog::effectivePriceSql();

        if ($this->min > self::PRICE_MIN) {
            $query->whereRaw("{$price} >= ?", [$this->min]);
        }
        if ($this->max < self::PRICE_MAX) {
            $query->whereRaw("{$price} <= ?", [$this->max]);
        }

        $sizes = $this->activeSizes();
        if ($sizes) {
            $query->where(function (Builder $q) use ($sizes) {
                foreach ($sizes as $size) {
                    $q->orWhere(fn (Builder $s) => self::whereHasSize($s, $size));
                }
            });
        }

        // Re-apply ordering: price sorts replace the default newest-first.
        $query->reorder();
        match ($this->sort) {
            'price_asc' => $query->orderByRaw("{$price} asc")->orderByDesc('id'),
            'price_desc' => $query->orderByRaw("{$price} desc")->orderByDesc('id'),
            default => $query->orderByDesc('id'),
        };

        return $query;
    }

    /**
     * `sizes` is stored as a JSON array ('["38","40"]') or a comma list
     * ('38, 40'); match the exact size in either form.
     */
    private static function whereHasSize(Builder $q, string $size): void
    {
        $driver = $q->getConnection()->getDriverName();
        $normalised = $driver === 'mysql'
            ? "CONCAT(',', REPLACE(sizes, ' ', ''), ',')"
            : "',' || REPLACE(sizes, ' ', '') || ','";

        $q->where('sizes', 'like', '%"'.$size.'"%')
            ->orWhereRaw("{$normalised} LIKE ?", ['%,'.$size.',%']);
    }

    private function cacheKey(string $suffix): string
    {
        return 'storefront.products.'.$suffix.'.'.md5(json_encode([
            'c' => $this->categories, 's' => $this->search, 't' => $this->collection, 'o' => $this->sort,
            'min' => $this->min, 'max' => $this->max, 'z' => $this->sizes, 'p' => $this->page,
        ]));
    }

    /** @return LengthAwarePaginator<StorefrontProduct> */
    public function results(): LengthAwarePaginator
    {
        if ($this->results) {
            return $this->results;
        }

        $cached = Cache::remember($this->cacheKey('page'), self::CACHE_TTL, function () {
            $query = $this->filteredQuery();
            $total = (clone $query)->reorder()->count();
            $models = $query->forPage($this->page, self::PER_PAGE)->get();

            return ['total' => $total, 'items' => StorefrontProduct::collection($models)];
        });

        $this->results = new LengthAwarePaginator(
            $cached['items'],
            $cached['total'],
            self::PER_PAGE,
            $this->page,
            ['path' => route('shop.products'), 'query' => $this->params()]
        );

        return $this->results;
    }

    /**
     * Sizes carried by the products being browsed (category / search / tab),
     * so a cap category shows no size filter. "One Size" is never an option.
     *
     * @return list<string>
     */
    public function sizeOptions(): array
    {
        $key = 'storefront.products.sizes.'.md5(json_encode(['c' => $this->categories, 's' => $this->search, 't' => $this->collection]));

        return Cache::remember($key, self::CACHE_TTL, function () {
            $set = [];
            $this->baseQuery()->reorder()->whereNotNull('sizes')->select(['id', 'sizes'])
                ->chunk(500, function (Collection $rows) use (&$set) {
                    foreach ($rows as $row) {
                        foreach (StorefrontProduct::stringList($row->sizes) as $size) {
                            if (strcasecmp($size, 'One Size') !== 0) {
                                $set[$size] = true;
                            }
                        }
                    }
                });

            // Array keys turn "38" into 38; the URL params are strings.
            return self::sortSizes(array_map('strval', array_keys($set)));
        });
    }

    /** Numeric sizes ascending, then letter sizes in garment order, then the rest. */
    public static function sortSizes(array $sizes): array
    {
        $order = ['XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL', '2XL', '3XL', '4XL', 'FREE SIZE', 'ONE SIZE'];
        usort($sizes, function ($a, $b) use ($order) {
            $aNum = is_numeric($a);
            $bNum = is_numeric($b);
            if ($aNum && $bNum) {
                return (float) $a <=> (float) $b;
            }
            if ($aNum) {
                return -1;
            }
            if ($bNum) {
                return 1;
            }
            $ia = array_search(strtoupper($a), $order, true);
            $ib = array_search(strtoupper($b), $order, true);
            if ($ia !== false && $ib !== false) {
                return $ia <=> $ib;
            }
            if ($ia !== false) {
                return -1;
            }
            if ($ib !== false) {
                return 1;
            }

            return strcmp($a, $b);
        });

        return array_values($sizes);
    }

    /**
     * Size options grouped for the filter: band groups when every option is a
     * figure size, otherwise one flat group.
     *
     * @return list<array{label: ?string, sizes: list<string>}>
     */
    public function sizeGroups(): array
    {
        $options = $this->sizeOptions();
        if ($options === []) {
            return [];
        }
        $grouped = Sizes::group($options);
        if ($grouped['other'] !== []) {
            return [['label' => null, 'sizes' => $options]];
        }

        return array_map(fn ($g) => ['label' => $g['band']['label'], 'sizes' => $g['sizes']], $grouped['banded']);
    }
}
