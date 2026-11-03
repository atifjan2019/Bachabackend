<?php

namespace Tests\Feature\Shop;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_pages_are_served_from_cache_with_a_fresh_csrf_token(): void
    {
        Category::create(['name' => 'Shawls', 'slug' => 'shawls']);
        Product::create(['name' => 'Ruby Shawl', 'slug' => 'ruby-shawl', 'category' => 'shawls', 'price' => '1000']);

        $miss = $this->get('/products')->assertOk()->assertHeader('X-Storefront-Cache', 'miss');
        $this->assertStringContainsString('Ruby Shawl', $miss->getContent());
        $tokenA = csrf_token();
        $this->assertStringContainsString($tokenA, $miss->getContent());

        // A different visitor gets the cached page, but with their own token.
        $this->flushSession();
        $hit = $this->get('/products')->assertOk()->assertHeader('X-Storefront-Cache', 'hit');
        $tokenB = csrf_token();
        $this->assertNotSame($tokenA, $tokenB);
        $this->assertStringContainsString($tokenB, $hit->getContent());
        $this->assertStringNotContainsString($tokenA, $hit->getContent());
        $this->assertStringNotContainsString('__BSF_CSRF_TOKEN__', $hit->getContent());

        // Admin saves flush the cache, so a hidden product disappears at once.
        Product::first()->update(['is_active' => false]);
        \Illuminate\Support\Facades\Cache::flush();
        $this->get('/products')->assertOk()->assertHeader('X-Storefront-Cache', 'miss')->assertDontSee('Ruby Shawl');
    }

    public function test_pages_with_flash_messages_or_a_signed_in_customer_are_not_cached(): void
    {
        $this->withSession(['status' => 'Saved'])->get('/products')->assertOk()->assertHeaderMissing('X-Storefront-Cache');

        $customer = \App\Models\Customer::create(['name' => 'A', 'email' => 'a@example.com', 'password' => bcrypt('secret123')]);
        $this->actingAs($customer, 'customer')->get('/products')->assertOk()->assertHeaderMissing('X-Storefront-Cache');
    }
}
