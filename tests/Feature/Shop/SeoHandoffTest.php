<?php

namespace Tests\Feature\Shop;

use App\Models\AdminUser;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** The developer items from the SEO handoff (E1–E6) and the Shipping page. */
class SeoHandoffTest extends TestCase
{
    use RefreshDatabase;

    public function test_image_alt_text_from_admin_renders_on_the_storefront(): void
    {
        Category::create(['name' => 'Shawls', 'slug' => 'shawls', 'image' => 'https://example.com/c.jpg', 'image_alt' => 'Hand-woven Swati shawls on a rack']);
        Product::create([
            'name' => 'Ruby Shawl', 'slug' => 'ruby-shawl', 'category' => 'shawls', 'price' => '1000',
            'image' => 'https://example.com/a.jpg', 'image_alt' => 'Ruby red wool shawl, folded',
            'gallery' => ['https://example.com/b.jpg', 'https://example.com/c.jpg'], 'gallery_alts' => ['Close-up of the weave'],
        ]);

        $this->get('/')->assertOk()->assertSee('alt="Hand-woven Swati shawls on a rack"', false);
        $this->get('/products')->assertOk()->assertSee('alt="Ruby red wool shawl, folded"', false);
        // Main image is server-rendered; the gallery alts travel in the Alpine config.
        $this->get('/products/ruby-shawl')->assertOk()
            ->assertSee('alt="Ruby red wool shawl, folded"', false)
            ->assertSee('Close-up of the weave')
            ->assertSee('Ruby Shawl image 3');

        $this->getJson('/api/v1/products')->assertOk()->assertJsonFragment(['image_alt' => 'Ruby red wool shawl, folded']);
    }

    public function test_admin_product_form_saves_alt_text_and_rejects_a_duplicate_slug(): void
    {
        $admin = AdminUser::create(['username' => 'a', 'email' => 'a@example.com', 'password' => Hash::make('secret123'), 'login_attempts' => 0]);
        Product::create(['name' => 'Taken', 'slug' => 'taken', 'price' => '1']);

        $this->actingAs($admin, 'admin')->post(route('admin.products.store'), [
            'name' => 'New One', 'slug' => 'taken', 'price' => '1',
        ])->assertSessionHasErrors('slug');

        $this->actingAs($admin, 'admin')->post(route('admin.products.store'), [
            'name' => 'Taken', 'price' => '1', 'image_alt' => ' Front view ', 'gallery_alts' => "Side view\n\nBack view\n\n",
        ])->assertSessionHasNoErrors();
        $made = Product::where('name', 'Taken')->orderByDesc('id')->first();
        $this->assertSame('taken-2', $made->slug);
        $this->assertSame('Front view', $made->image_alt);
        $this->assertSame(['Side view', '', 'Back view'], $made->gallery_alts);
    }

    public function test_products_page_and_category_pages_use_their_own_meta(): void
    {
        Setting::create(['setting_key' => 'meta_title', 'setting_value' => 'Home Title']);
        Setting::create(['setting_key' => 'products_meta_title', 'setting_value' => 'Shop All Pakistani Wear | Bacha Stylo']);
        Setting::create(['setting_key' => 'products_meta_description', 'setting_value' => 'Every piece in one place.']);
        Category::create([
            'name' => 'Shawls', 'slug' => 'shawls',
            'meta_title' => 'Swati Shawls for Men & Women | Bacha Stylo', 'meta_description' => 'Pure wool shawls, handmade in Swat.',
        ]);

        $this->get('/')->assertOk()->assertSee('<title>Home Title</title>', false);
        $this->get('/products')->assertOk()
            ->assertSee('<title>Shop All Pakistani Wear | Bacha Stylo</title>', false)
            ->assertSee('<meta name="description" content="Every piece in one place.">', false);
        $this->get('/products?category=shawls')->assertOk()
            ->assertSee('<title>Swati Shawls for Men &amp; Women | Bacha Stylo</title>', false)
            ->assertSee('content="Pure wool shawls, handmade in Swat."', false);
    }

    public function test_ampersands_in_product_titles_are_encoded_once(): void
    {
        Product::create(['name' => 'Shawl & Cap Deal', 'slug' => 'deal', 'price' => '1', 'meta_title' => 'Shawl & Cap Deal | Bacha Stylo', 'meta_description' => 'Warm & ready.']);

        $this->get('/products/deal')->assertOk()
            ->assertSee('<title>Shawl &amp; Cap Deal | Bacha Stylo</title>', false)
            ->assertDontSee('&amp;amp;', false)
            ->assertSee('<meta name="description" content="Warm &amp; ready.">', false);
    }

    public function test_shipping_policy_page_is_linked_from_the_footer_and_in_the_sitemap(): void
    {
        Setting::updateOrCreate(['setting_key' => 'shipping_fee'], ['setting_value' => '250']);
        Setting::updateOrCreate(['setting_key' => 'free_shipping_threshold'], ['setting_value' => '5000']);
        Setting::updateOrCreate(['setting_key' => 'delivery_policy'], ['setting_value' => "We ship daily.\n\nParcels are insured."]);

        $this->get('/shipping')->assertOk()
            ->assertSee('Shipping &amp; Delivery', false)
            ->assertSee('Rs. 250')->assertSee('Rs. 5,000')
            ->assertSee('We ship daily.')->assertSee('Parcels are insured.')
            ->assertSee('<link rel="canonical" href="https://www.bachastylo.com/shipping">', false);
        $this->assertMatchesRegularExpression('#href="[^"]*/shipping"[^>]*>[^<]*Shipping#', $this->get('/')->assertOk()->getContent());
        $this->get('/sitemap.xml')->assertOk()->assertSee('https://www.bachastylo.com/shipping');
    }

    public function test_a_renamed_slug_redirects_the_old_url_to_the_new_one(): void
    {
        $product = Product::create(['name' => 'Kids Waistcoat', 'slug' => 'kids-waistcoat-black-red', 'price' => '1']);
        $product->update(['slug' => 'kids-waistcoat-black-white']);
        $this->assertSame('kids-waistcoat-black-red', $product->fresh()->previous_slug);

        $this->get('/products/kids-waistcoat-black-red')
            ->assertStatus(301)
            ->assertRedirect('/products/kids-waistcoat-black-white');
        $this->get('/products/kids-waistcoat-black-white')->assertOk();
        $this->get('/products/never-existed')->assertNotFound();

        // A hidden product's old link does not leak it.
        $product->update(['is_active' => false]);
        \Illuminate\Support\Facades\Cache::flush();
        $this->get('/products/kids-waistcoat-black-red')->assertNotFound();
    }
}
