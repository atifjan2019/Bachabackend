<?php

namespace Tests\Feature\Shop;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    private function seedCatalog(): void
    {
        $parent = Category::create(['name' => 'Waistcoats', 'slug' => 'waistcoats']);
        Category::create(['name' => 'Kids Waistcoats', 'slug' => 'kids-waistcoats', 'parent_id' => $parent->id]);
        Category::create(['name' => 'Shawls', 'slug' => 'shawls']);

        Product::create([
            'name' => 'Royal Maroon Waistcoat', 'slug' => 'royal-maroon-waistcoat', 'sku' => 'BS-WC-001',
            'category' => 'kids-waistcoats', 'price' => '6500', 'original_price' => '7500',
            'image' => 'https://example.com/a.jpg', 'description' => '<p>Signature <b>Karandi</b> waistcoat.</p>',
            'sizes' => ['14', '16', '18', '34', '36', '38'],
            'colors' => [['name' => 'Maroon', 'hex' => '#7a1f2b', 'stock' => 5], ['name' => 'Navy', 'hex' => '#1f2a44', 'stock' => 0]],
            'is_new' => true, 'is_featured' => true,
        ]);
        Product::create([
            'name' => 'Ruby Shawl', 'slug' => 'ruby-shawl', 'sku' => 'BS-SH-001', 'category' => 'shawls',
            'price' => '4500', 'image' => 'https://example.com/b.jpg', 'description' => 'Soft shawl', 'sizes' => ['S', 'M'],
        ]);
        Product::create([
            'name' => 'Pakol Cap', 'slug' => 'pakol-cap', 'sku' => 'BS-PC-001', 'category' => 'shawls',
            'price' => '1800', 'image' => 'https://example.com/c.jpg', 'description' => 'Wool cap',
        ]);
    }

    public function test_listing_renders_seeded_products(): void
    {
        $this->seedCatalog();

        $this->get('/products')
            ->assertOk()
            ->assertSee('Royal Maroon Waistcoat')
            ->assertSee('Ruby Shawl')
            ->assertSee('3 pieces stitched with care')
            ->assertSee('<link rel="canonical" href="https://www.bachastylo.com/products">', false);
    }

    public function test_category_filter_expands_to_children_and_sets_hero(): void
    {
        $this->seedCatalog();

        $this->get('/products?category=waistcoats')
            ->assertOk()
            ->assertSee('Royal Maroon Waistcoat')
            ->assertDontSee('Ruby Shawl')
            ->assertSee('1 piece curated for waistcoats')
            ->assertSee('<title>Waistcoats | Bacha Stylo</title>', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('<link rel="canonical" href="https://www.bachastylo.com/products?category=waistcoats">', false);
    }

    public function test_price_size_and_sort_filters_narrow_results(): void
    {
        $this->seedCatalog();

        $this->get('/products?min=5000')->assertOk()->assertSee('Royal Maroon Waistcoat')->assertDontSee('Ruby Shawl')->assertDontSee('Pakol Cap');
        $this->get('/products?max=2000')->assertOk()->assertSee('Pakol Cap')->assertDontSee('Ruby Shawl');
        $this->get('/products?size=38')->assertOk()->assertSee('Royal Maroon Waistcoat')->assertDontSee('Ruby Shawl');
        $this->get('/products?size=S')->assertOk()->assertSee('Ruby Shawl')->assertDontSee('Royal Maroon Waistcoat');

        $asc = $this->get('/products?sort=price_asc')->assertOk()->getContent();
        $this->assertLessThan(strpos($asc, 'Royal Maroon Waistcoat'), strpos($asc, 'Pakol Cap'));
        $desc = $this->get('/products?sort=price_desc')->assertOk()->getContent();
        $this->assertLessThan(strpos($desc, 'Pakol Cap'), strpos($desc, 'Royal Maroon Waistcoat'));

        $this->get('/products?tab=featured')->assertOk()->assertSee('Royal Maroon Waistcoat')->assertDontSee('Ruby Shawl');
        $this->get('/products?search=shawl')->assertOk()->assertSee('Ruby Shawl')->assertDontSee('Pakol Cap');
        $this->get('/products?search=nothing-matches')->assertOk()->assertSee('Nothing matches yet');
    }

    public function test_product_page_renders_with_json_ld(): void
    {
        $this->seedCatalog();

        $this->get('/products/royal-maroon-waistcoat')
            ->assertOk()
            ->assertSee('<title>Royal Maroon Waistcoat | Bacha Stylo</title>', false)
            ->assertSee('"@type":"Product"', false)
            ->assertSee('"sku":"BS-WC-001"', false)
            ->assertSee('"priceCurrency":"PKR"', false)
            ->assertSee('"price":"6500"', false)
            ->assertSee('Kids Waistcoats')
            ->assertSee('Rs. 6,500')
            ->assertSee('Save Rs. 1,000', false)
            ->assertSee('Size Guide')
            ->assertSee('og:type" content="product"', false);
    }

    public function test_unknown_product_is_404(): void
    {
        $this->seedCatalog();

        $this->get('/products/does-not-exist')->assertNotFound()->assertSee('out of stock');
    }
}
