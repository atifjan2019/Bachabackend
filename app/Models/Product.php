<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Product extends Model
{
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'description',
        'meta_title',
        'meta_description',
        'category',
        'price',
        'original_price',
        'image',
        'gallery',
        'sizes',
        'colors',
        'video_url',
        'is_new',
        'is_featured',
        'is_best_seller',
        'label',
        'accordions',
    ];

    protected $casts = [
        'is_new' => 'boolean',
        'is_featured' => 'boolean',
        'is_best_seller' => 'boolean',
        'accordions' => 'array',
        'gallery' => 'array',
        'sizes' => 'array',
        'colors' => 'array',
        // Stored in string columns, so without these the API serialised money
        // as "6500" and every consumer had to parse it back. Cast keeps the
        // JSON contract numeric; null stays null for products with no sale.
        'price' => 'float',
        'original_price' => 'float',
    ];

    /**
     * Whether a column exists on products. Columns added by a release are
     * only read or written once its database update has run, so the admin
     * and the API keep working between the code pull and that update.
     */
    public static function hasColumn(string $name): bool
    {
        return (bool) Cache::remember(
            'products.columns.' . $name,
            300,
            fn () => Schema::hasColumn('products', $name)
        );
    }

    /**
     * The product code used on orders and in admin search, e.g. "BS-00042".
     * Products created before codes existed get one derived from the id.
     */
    public static function defaultSku(int $id): string
    {
        return 'BS-' . str_pad((string) $id, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Normalise an admin-entered code: trimmed, upper-case, only letters,
     * digits and dashes, so codes are easy to read out and search for.
     */
    public static function normaliseSku(?string $value): ?string
    {
        $value = strtoupper(trim((string) $value));
        $value = preg_replace('/[^A-Z0-9-]+/', '-', $value) ?? '';
        $value = trim(preg_replace('/-+/', '-', $value) ?? '', '-');

        return $value === '' ? null : $value;
    }

    /**
     * Parse the colour editor's submission into a clean list of
     * {name, hex, image, price, stock, note}. Accepts the JSON the admin form
     * posts, an already-decoded array, or a plain comma-separated list of
     * names so "Red, Blue" works too.
     *
     * @return array<int, array{name:string,hex:?string,image:?string,price:?float,stock:?int,note:?string}>
     */
    public static function parseColors(mixed $raw): array
    {
        if (is_string($raw)) {
            $trimmed = trim($raw);
            if ($trimmed === '') {
                return [];
            }
            $decoded = json_decode($trimmed, true);
            if (!is_array($decoded)) {
                $decoded = array_map(fn ($n) => ['name' => $n], explode(',', $trimmed));
            }
            $raw = $decoded;
        }

        if (!is_array($raw)) {
            return [];
        }

        $out = [];
        $seen = [];
        foreach ($raw as $row) {
            if (is_string($row)) {
                $row = ['name' => $row];
            }
            if (!is_array($row)) {
                continue;
            }
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '' || isset($seen[strtolower($name)])) {
                continue;
            }
            $seen[strtolower($name)] = true;

            $hex = trim((string) ($row['hex'] ?? ''));
            $hex = preg_match('/^#?[0-9a-f]{6}$/i', $hex) ? '#' . strtolower(ltrim($hex, '#')) : null;

            $image = trim((string) ($row['image'] ?? ''));
            $price = $row['price'] ?? null;
            $stock = $row['stock'] ?? null;

            $out[] = [
                'name' => Str::limit($name, 60, ''),
                'hex' => $hex,
                'image' => $image !== '' ? $image : null,
                'price' => is_numeric($price) && (float) $price > 0 ? (float) $price : null,
                'stock' => is_numeric($stock) && $stock !== '' ? max(0, (int) $stock) : null,
                'note' => Str::limit(trim((string) ($row['note'] ?? '')), 120, '') ?: null,
            ];
        }

        return $out;
    }

    protected static function booted(): void
    {
        // A product always has a code. Admins can type their own; blank
        // means "generate one from the id" once the id is known.
        static::saving(function (Product $product) {
            if (!static::hasColumn('sku')) {
                unset($product->sku, $product->meta_title, $product->meta_description);
                if (!static::hasColumn('colors')) {
                    unset($product->colors);
                }
                return;
            }
            if (array_key_exists('sku', $product->getAttributes())) {
                $product->sku = static::normaliseSku($product->sku);
            }
        });

        static::created(function (Product $product) {
            if (static::hasColumn('sku') && empty($product->sku)) {
                $product->forceFill(['sku' => static::defaultSku((int) $product->id)])->saveQuietly();
            }
        });
    }
}
