<?php

namespace App\Support\Storefront;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * A Product as the storefront renders it — exactly what the Next.js adapter
 * (lib/api/products.ts → adaptProduct) used to produce: normalised images,
 * sizes, colours, a variant per colour × size, sale pricing and stock flags.
 */
class StorefrontProduct
{
    public const LOW_STOCK_THRESHOLD = 3;

    public const PLACEHOLDER_COLOR = 'Default';

    public const PLACEHOLDER_HEX = '#333333';

    public int $id;

    public string $slug;

    public string $sku;

    public string $name;

    public string $description;

    public ?string $meta_title;

    public ?string $meta_description;

    /** @var list<array{name:string,hex:?string,image:?string,price:?float,stock:?int,note:?string}> Admin colours (empty for single-colour products). */
    public array $colors;

    /** @var list<string> Raw size strings as saved; empty when the product has no sizes. */
    public array $sizes;

    public float $price;

    public ?float $sale_price;

    /** @var array{slug:string,name:string} */
    public array $category;

    /** @var list<array{id:int,url:string,alt:string}> */
    public array $images;

    /** @var list<array{id:int,size:string,color:string,color_hex:string,stock:int,sku:string,price:?float,image:?string,note:?string}> */
    public array $variants;

    public bool $in_stock;

    public int $low_stock_threshold = self::LOW_STOCK_THRESHOLD;

    public ?string $created_at;

    public ?string $updated_at;

    public bool $is_new;

    public bool $is_featured;

    public bool $is_best_seller;

    public ?string $label;

    public ?string $video_url;

    /** @var list<array{title:string,content:string}> */
    public array $accordions;

    public static function make(Product $product): self
    {
        $self = new self;

        $self->id = (int) $product->id;
        $self->slug = (string) ($product->slug ?? '');
        $self->name = (string) ($product->name ?? '');
        $self->description = (string) ($product->description ?? '');
        $self->meta_title = self::blankToNull($product->meta_title ?? null);
        $self->meta_description = self::blankToNull($product->meta_description ?? null);
        $self->created_at = optional($product->created_at)->toIso8601String();
        $self->updated_at = optional($product->updated_at)->toIso8601String();
        $self->is_new = (bool) $product->is_new;
        $self->is_featured = (bool) $product->is_featured;
        $self->is_best_seller = (bool) $product->is_best_seller;
        $self->label = self::blankToNull($product->label);
        $self->video_url = self::blankToNull($product->video_url ?? null);
        $self->accordions = self::accordions($product->accordions);

        $sku = trim((string) ($product->sku ?? ''));
        $self->sku = $sku !== '' ? $sku : Product::defaultSku($self->id);

        // Pricing: original_price above price means the product is on sale.
        $price = (float) ($product->price ?? 0);
        $original = $product->original_price !== null && $product->original_price !== ''
            ? (float) $product->original_price
            : null;
        if ($original !== null && $original > $price) {
            $self->price = $original;
            $self->sale_price = $price;
        } else {
            $self->price = $price;
            $self->sale_price = null;
        }

        // Category: slug string on the product → {slug, name} via the categories table.
        $slug = is_string($product->category) ? $product->category : '';
        $self->category = [
            'slug' => $slug,
            'name' => self::categoryName($slug),
        ];

        // Images: main image first, then the gallery (array, JSON or comma list).
        $urls = array_values(array_filter(array_merge(
            [trim((string) ($product->image ?? ''))],
            self::stringList($product->gallery)
        ), fn ($u) => $u !== ''));
        // ALT text: the admin's words for the main image and each gallery
        // image (aligned by position), else the product name.
        $galleryAlts = is_array($product->gallery_alts ?? null) ? array_values($product->gallery_alts) : [];
        $mainAlt = trim((string) ($product->image_alt ?? ''));
        $self->images = [];
        foreach ($urls as $i => $url) {
            $alt = $i === 0 ? $mainAlt : trim((string) ($galleryAlts[$i - 1] ?? ''));
            $self->images[] = ['id' => $i + 1, 'url' => $url, 'alt' => $alt !== '' ? $alt : ($i === 0 ? $self->name : $self->name.' image '.($i + 1))];
        }

        $self->sizes = self::stringList($product->sizes);
        $self->colors = Product::parseColors($product->colors);

        // Variants: every colour × every size. No sizes → "One Size"; no colours →
        // a hidden placeholder colour. Stock is per colour when set, else 10.
        $sizes = $self->sizes !== [] ? $self->sizes : ['One Size'];
        $hasColors = $self->colors !== [];
        $colorOptions = $hasColors
            ? $self->colors
            : [['name' => self::PLACEHOLDER_COLOR, 'hex' => self::PLACEHOLDER_HEX, 'image' => null, 'price' => null, 'stock' => null, 'note' => null]];

        $self->variants = [];
        foreach ($colorOptions as $ci => $c) {
            foreach ($sizes as $si => $size) {
                $parts = array_filter([
                    $self->sku,
                    $hasColors ? self::codePart($c['name']) : null,
                    $self->sizes !== [] ? self::codePart($size) : null,
                ], fn ($p) => $p !== null && $p !== '');
                $self->variants[] = [
                    'id' => $self->id * 10000 + $ci * 100 + $si,
                    'size' => (string) $size,
                    'color' => (string) $c['name'],
                    'color_hex' => $c['hex'] ?? self::PLACEHOLDER_HEX,
                    'stock' => $c['stock'] ?? 10,
                    'sku' => implode('-', $parts),
                    'price' => $c['price'] ?? null,
                    'image' => $c['image'] ?? null,
                    'note' => $c['note'] ?? null,
                ];
            }
        }

        $self->in_stock = (bool) array_filter($self->variants, fn ($v) => $v['stock'] > 0);

        return $self;
    }

    /** @return Collection<int, self> */
    public static function collection(iterable $products): Collection
    {
        return collect($products)->map(fn (Product $p) => self::make($p))->values();
    }

    /* ------------------------------------------------------------------ */
    /* Derived values used by the views                                    */
    /* ------------------------------------------------------------------ */

    public function onSale(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    /** The price a customer pays (sale price when on sale). */
    public function displayPrice(): float
    {
        return $this->sale_price ?? $this->price;
    }

    public function discountPercent(): int
    {
        if (! $this->onSale() || $this->price <= 0) {
            return 0;
        }

        return (int) round(($this->price - $this->sale_price) / $this->price * 100);
    }

    public function savings(): float
    {
        return $this->onSale() ? $this->price - $this->sale_price : 0.0;
    }

    public function url(): string
    {
        return route('shop.product', $this->slug);
    }

    public function primaryImage(): ?string
    {
        return $this->images[0]['url'] ?? null;
    }

    /** True when there are real admin colours (the placeholder is never shown). */
    public function showColors(): bool
    {
        return $this->colors !== [];
    }

    public function hasSizes(): bool
    {
        return $this->sizes !== [];
    }

    /** Total stock across variants (per colour × sizes). */
    public function totalStock(): int
    {
        return (int) array_sum(array_column($this->variants, 'stock'));
    }

    /** Payload the Alpine product page needs (variants, images, colours). */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'name' => $this->name,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'display_price' => $this->displayPrice(),
            'category' => $this->category,
            'images' => $this->images,
            'sizes' => $this->sizes,
            'colors' => $this->colors,
            'show_colors' => $this->showColors(),
            'variants' => $this->variants,
            'in_stock' => $this->in_stock,
            'low_stock_threshold' => $this->low_stock_threshold,
            'url' => $this->url(),
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Parsing helpers                                                     */
    /* ------------------------------------------------------------------ */

    /** Code fragment for a variant: letters and digits only, upper-case. */
    public static function codePart(string $value): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $value) ?? '');
    }

    /** Title Case of a slug: "kurta-sets" → "Kurta Sets". */
    public static function titleFromSlug(string $slug): string
    {
        return Str::title(str_replace('-', ' ', $slug));
    }

    /** Category display name for a slug, from the (cached) categories table. */
    public static function categoryName(string $slug): string
    {
        if ($slug === '') {
            return '';
        }

        return Catalog::categoryNames()[$slug] ?? self::titleFromSlug($slug);
    }

    /**
     * Accept an array, a JSON-encoded array or a comma-separated string.
     *
     * @return list<string>
     */
    public static function stringList(mixed $raw): array
    {
        if (is_array($raw)) {
            $list = $raw;
        } elseif (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            $list = is_array($decoded) ? $decoded : explode(',', $raw);
        } else {
            return [];
        }

        $out = [];
        foreach ($list as $item) {
            if (is_array($item)) {
                $item = $item['url'] ?? $item['name'] ?? '';
            }
            $item = trim((string) $item);
            if ($item !== '') {
                $out[] = $item;
            }
        }

        return array_values($out);
    }

    /** @return list<array{title:string,content:string}> */
    private static function accordions(mixed $raw): array
    {
        if (is_string($raw)) {
            $raw = json_decode($raw, true);
        }
        if (! is_array($raw)) {
            return [];
        }
        $out = [];
        foreach ($raw as $row) {
            if (! is_array($row)) {
                continue;
            }
            $title = trim((string) ($row['title'] ?? ''));
            $content = trim((string) ($row['content'] ?? ''));
            if ($title === '' && $content === '') {
                continue;
            }
            $out[] = ['title' => $title, 'content' => $content];
        }

        return $out;
    }

    private static function blankToNull(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
