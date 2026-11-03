<?php

namespace Tests\Feature\Shop;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Admin-controlled visibility (is_active) and ordering (sort_order) of
 * categories and products, as seen by the storefront and the public API.
 */
class VisibilityTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $name, string $category, array $extra = []): Product
    {
        return Product::create([
            'name' => $name, 'slug' => str($name)->slug()->toString(), 'category' => $category,
            'price' => '1000', 'image' => 'https://example.com/x.jpg', 'description' => $name,
        ] + $extra);
    }

    public function test_hidden_product_disappears_from_shop_api_and_sitemap(): void
    {
        Category::create(['name' => 'Shawls', 'slug' => 'shawls']);
        $this->product('Visible Shawl', 'shawls');
        $hidden = $this->product('Hidden Shawl', 'shawls', ['is_active' => false]);

        $this->get('/products')->assertOk()->assertSee('Visible Shawl')->assertDontSee('Hidden Shawl');
        $this->get('/products?search=hidden')->assertOk()->assertDontSee('Hidden Shawl');
        $this->get('/products/hidden-shawl')->assertNotFound();
        $this->get('/sitemap.xml')->assertOk()->assertSee('/products/visible-shawl')->assertDontSee('/products/hidden-shawl');

        $this->getJson('/api/v1/products')->assertOk()
            ->assertJsonFragment(['name' => 'Visible Shawl'])
            ->assertJsonMissing(['name' => 'Hidden Shawl']);
        $this->getJson('/api/v1/products/hidden-shawl')->assertNotFound();
        $this->getJson('/api/v1/products/'.$hidden->id)->assertNotFound();
    }

    public function test_hidden_category_hides_its_subtree_and_products(): void
    {
        $parent = Category::create(['name' => 'Waistcoats', 'slug' => 'waistcoats', 'is_active' => false]);
        Category::create(['name' => 'Kids Waistcoats', 'slug' => 'kids-waistcoats', 'parent_id' => $parent->id]);
        Category::create(['name' => 'Shawls', 'slug' => 'shawls']);
        $this->product('Kids Waistcoat', 'kids-waistcoats');
        $this->product('Ruby Shawl', 'shawls');
        $this->product('Loose Item', '');

        // Navigation (mega menu) and the filter list only know the visible category.
        $this->get('/')->assertOk()->assertSee('category=shawls')->assertDontSee('category=waistcoats');
        $this->get('/products')->assertOk()->assertDontSee('kids-waistcoats');

        // Products inside the hidden subtree are gone; uncategorised products stay.
        $this->get('/products')->assertOk()
            ->assertSee('Ruby Shawl')->assertSee('Loose Item')->assertDontSee('Kids Waistcoat');
        $this->get('/products?category=waistcoats')->assertOk()->assertDontSee('Kids Waistcoat');
        $this->get('/products/kids-waistcoat')->assertNotFound();

        $json = $this->getJson('/api/v1/categories')->assertOk()->json('data');
        $this->assertSame(['shawls'], array_column($json, 'slug'));
        $this->assertSame(1, $json[0]['products_count']);
    }

    public function test_sort_order_puts_numbered_items_first_then_default_order(): void
    {
        $a = Category::create(['name' => 'Alpha', 'slug' => 'alpha']);
        $z = Category::create(['name' => 'Zulu', 'slug' => 'zulu', 'sort_order' => 1]);
        $m = Category::create(['name' => 'Mike', 'slug' => 'mike', 'sort_order' => 2]);
        Category::create(['name' => 'Sub Z', 'slug' => 'sub-z', 'parent_id' => $z->id, 'sort_order' => 5]);
        Category::create(['name' => 'Sub A', 'slug' => 'sub-a', 'parent_id' => $z->id, 'sort_order' => 1]);

        $json = $this->getJson('/api/v1/categories')->assertOk()->json('data');
        $this->assertSame(['zulu', 'mike', 'alpha'], array_column($json, 'slug'));
        $this->assertSame(['sub-a', 'sub-z'], array_column($json[0]['children'], 'slug'));

        $this->product('Oldest', 'alpha');
        $this->product('Pinned Second', 'alpha', ['sort_order' => 2]);
        $this->product('Newest', 'alpha');
        $this->product('Pinned First', 'alpha', ['sort_order' => 1]);

        $names = array_column($this->getJson('/api/v1/products')->assertOk()->json('data'), 'name');
        $this->assertSame(['Pinned First', 'Pinned Second', 'Newest', 'Oldest'], $names);

        $html = $this->get('/products')->assertOk()->getContent();
        $positions = array_map(fn ($n) => strpos($html, $n), ['Pinned First', 'Pinned Second', 'Newest', 'Oldest']);
        $sorted = $positions;
        sort($sorted);
        $this->assertSame($sorted, $positions);

        // Price sorting ignores the manual order entirely.
        $this->get('/products?sort=price_asc')->assertOk();
    }

    public function test_checkout_refuses_a_product_hidden_after_it_was_added_to_the_cart(): void
    {
        Category::create(['name' => 'Shawls', 'slug' => 'shawls']);
        $hidden = $this->product('Gone Shawl', 'shawls', ['is_active' => false]);
        $cart = json_encode([['product_id' => $hidden->id, 'name' => 'Gone Shawl', 'price' => 1000, 'quantity' => 1]]);

        $this->from('/checkout')->post('/checkout', [
            'name' => 'Ayesha Khan', 'phone' => '03001234567', 'email' => 'ayesha@example.com',
            'full_address' => 'House 12', 'city' => 'Lahore', 'province' => 'Punjab',
            'payment_method' => 'cod', 'cart_json' => $cart,
        ])->assertRedirect('/checkout')->assertSessionHasErrors('cart_json');

        $this->postJson('/api/v1/orders', [
            'customer_name' => 'Ayesha Khan', 'customer_email' => 'ayesha@example.com', 'customer_phone' => '923001234567',
            'shipping_address' => 'House 12', 'city' => 'Lahore', 'country' => 'Pakistan',
            'items' => [['product_id' => $hidden->id, 'name' => 'Gone Shawl', 'price' => 1000, 'quantity' => 1]],
            'subtotal' => 1000, 'shipping_fee' => 250, 'total_amount' => 1250, 'payment_method' => 'Cash on Delivery',
        ])->assertStatus(422)->assertJsonValidationErrors(['items.0.product_id']);

        $this->assertDatabaseCount('orders', 0);
    }
}
