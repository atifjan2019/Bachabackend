<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Support\Storefront\Sizes;
use App\Support\Storefront\StorefrontProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontProductTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Royal Waistcoat',
            'slug' => 'royal-waistcoat',
            'sku' => 'BS-WC-001',
            'category' => 'men-waistcoats',
            'price' => '6500',
            'original_price' => '7500',
            'image' => 'https://cdn.test/main.jpg',
            'gallery' => ['https://cdn.test/g1.jpg', 'https://cdn.test/g2.jpg'],
            'sizes' => ['34', '36'],
            'colors' => json_encode([
                ['name' => 'Maroon', 'hex' => '#7a1f2b', 'stock' => 5, 'image' => 'https://cdn.test/maroon.jpg'],
                ['name' => 'Emerald', 'hex' => '#1f6f50', 'stock' => 0, 'price' => 6900],
            ]),
        ], $overrides));
    }

    public function test_it_builds_variants_for_every_colour_and_size(): void
    {
        $p = StorefrontProduct::make($this->product());

        $this->assertCount(4, $p->variants);
        $this->assertSame('BS-WC-001-MAROON-34', $p->variants[0]['sku']);
        $this->assertSame($p->id * 10000, $p->variants[0]['id']);
        $this->assertSame($p->id * 10000 + 100 + 1, $p->variants[3]['id']);
        $this->assertSame(5, $p->variants[0]['stock']);
        $this->assertSame('https://cdn.test/maroon.jpg', $p->variants[0]['image']);
        $this->assertSame(6900.0, $p->variants[2]['price']);
        $this->assertTrue($p->in_stock);
        $this->assertTrue($p->showColors());
    }

    public function test_sale_pricing_and_images(): void
    {
        $p = StorefrontProduct::make($this->product());

        $this->assertSame(7500.0, $p->price);
        $this->assertSame(6500.0, $p->sale_price);
        $this->assertTrue($p->onSale());
        $this->assertSame(13, $p->discountPercent());
        $this->assertSame(6500.0, $p->displayPrice());
        $this->assertCount(3, $p->images);
        $this->assertSame('https://cdn.test/main.jpg', $p->images[0]['url']);
        $this->assertSame('Royal Waistcoat image 2', $p->images[1]['alt']);
    }

    public function test_placeholder_colour_and_one_size_when_nothing_is_set(): void
    {
        $p = StorefrontProduct::make($this->product([
            'slug' => 'plain', 'sku' => null, 'colors' => null, 'sizes' => null, 'original_price' => null,
            'gallery' => 'https://cdn.test/a.jpg, https://cdn.test/b.jpg',
        ]));

        $this->assertCount(1, $p->variants);
        $this->assertSame('Default', $p->variants[0]['color']);
        $this->assertSame('One Size', $p->variants[0]['size']);
        $this->assertSame(10, $p->variants[0]['stock']);
        $this->assertFalse($p->showColors());
        $this->assertFalse($p->hasSizes());
        $this->assertNull($p->sale_price);
        $this->assertSame(Product::defaultSku($p->id), $p->sku);
        $this->assertSame($p->sku, $p->variants[0]['sku'], 'placeholder colour and no sizes add no code parts');
        $this->assertCount(3, $p->images, 'comma-separated gallery strings are accepted');
    }

    public function test_category_name_falls_back_to_title_case_of_the_slug(): void
    {
        $p = StorefrontProduct::make($this->product(['category' => 'kurta-sets']));

        $this->assertSame(['slug' => 'kurta-sets', 'name' => 'Kurta Sets'], $p->category);
    }

    public function test_size_bands(): void
    {
        $this->assertTrue(Sizes::usesBands(['14', '16', '34', '36']));
        $this->assertFalse(Sizes::usesBands(['34', '36']), 'one band only → flat row');
        $this->assertFalse(Sizes::usesBands(['S', 'M', '34']), 'letter sizes → flat row');

        $grouped = Sizes::group(['36', '14', 'S', '34']);
        $this->assertSame(['S'], $grouped['other']);
        $this->assertSame('kids', $grouped['banded'][0]['band']['key']);
        $this->assertSame(['34', '36'], $grouped['banded'][1]['sizes']);
    }

    public function test_money_and_excerpt_helpers(): void
    {
        $this->assertSame('Rs. 12,500', pkr(12500));
        $this->assertSame('Rs. 0', pkr(null));
        $this->assertSame('Rs. 251', pkr(250.6));
        $this->assertSame('Hello world', strip_html_excerpt('<p>Hello&nbsp;<b>world</b></p>'));
        $this->assertStringEndsWith('…', strip_html_excerpt(str_repeat('word ', 60), 100));
    }
}
