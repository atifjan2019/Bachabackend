<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminFormSubmissionsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): AdminUser
    {
        return AdminUser::create([
            'username' => 'admin_user',
            'email' => 'admin@example.com',
            'password' => Hash::make('secret123'),
            'login_attempts' => 0,
        ]);
    }

    public function test_admin_can_create_and_update_product(): void
    {
        $admin = $this->admin();
        Category::create(['name' => 'Shawls', 'slug' => 'shawls']);

        $create = $this->actingAs($admin, 'admin')->post(route('admin.products.store'), [
            'name' => 'Premium Shawl',
            'slug' => 'premium-shawl',
            'description' => 'Soft and warm',
            'category' => 'Shawls',
            'price' => '4500',
            'original_price' => '5000',
            'is_new' => 1,
        ]);

        $create->assertRedirect(route('admin.products.index'));
        $product = Product::firstOrFail();

        $update = $this->actingAs($admin, 'admin')->put(route('admin.products.update', $product->id), [
            'name' => 'Premium Shawl Updated',
            'slug' => 'premium-shawl-updated',
            'description' => 'Updated details',
            'category' => 'Shawls',
            'price' => '4700',
            'original_price' => '5200',
            'is_new' => 0,
        ]);

        $update->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Premium Shawl Updated',
            'slug' => 'premium-shawl-updated',
            'price' => '4700',
        ]);
    }

    public function test_admin_can_create_and_update_category(): void
    {
        $admin = $this->admin();

        $create = $this->actingAs($admin, 'admin')->post(route('admin.categories.store'), [
            'name' => 'Winter',
            'slug' => 'winter',
            'description' => 'Winter collection',
            'meta_title' => 'Winter Category',
            'meta_description' => 'Desc',
        ]);

        $create->assertRedirect(route('admin.categories.index'));
        $category = Category::firstOrFail();

        $update = $this->actingAs($admin, 'admin')->put(route('admin.categories.update', $category->id), [
            'name' => 'Winter Updated',
            'slug' => 'winter-updated',
            'description' => 'Updated category',
            'meta_title' => 'Winter Category Updated',
            'meta_description' => 'Updated desc',
        ]);

        $update->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Winter Updated',
            'slug' => 'winter-updated',
        ]);
    }

    public function test_admin_can_create_and_update_blog_post(): void
    {
        $admin = $this->admin();

        $create = $this->actingAs($admin, 'admin')->post(route('admin.blog.store'), [
            'title' => 'My First Post',
            'slug' => 'my-first-post',
            'content' => 'Hello world',
            'image' => 'https://example.com/image.jpg',
            'status' => 1,
        ]);

        $create->assertRedirect(route('admin.blog.index'));
        $post = BlogPost::firstOrFail();

        $update = $this->actingAs($admin, 'admin')->put(route('admin.blog.update', $post->id), [
            'title' => 'My Updated Post',
            'slug' => 'my-updated-post',
            'content' => 'Updated content',
            'image' => 'https://example.com/image-2.jpg',
            'status' => 0,
        ]);

        $update->assertRedirect(route('admin.blog.index'));
        $this->assertDatabaseHas('blog_posts', [
            'id' => $post->id,
            'title' => 'My Updated Post',
            'slug' => 'my-updated-post',
            'status' => 0,
        ]);
    }

    public function test_admin_can_update_settings_and_seo(): void
    {
        $admin = $this->admin();

        $settingsUpdate = $this->actingAs($admin, 'admin')->put(route('admin.settings.update'), [
            'business_name' => 'Bacha Stylo',
            'business_email' => 'store@example.com',
            'business_phone' => '923001234567',
            'shipping_fee' => 250,
            'free_shipping_threshold' => 5000,
            'facebook_url' => 'https://facebook.com/bacha',
            'instagram_url' => 'https://instagram.com/bacha',
            'whatsapp_number' => '923001234567',
        ]);

        $settingsUpdate->assertRedirect(route('admin.settings.index'));
        $this->assertDatabaseHas('settings', ['setting_key' => 'business_name', 'setting_value' => 'Bacha Stylo']);
        $this->assertDatabaseHas('settings', ['setting_key' => 'shipping_fee', 'setting_value' => '250']);

        $seoUpdate = $this->actingAs($admin, 'admin')->put(route('admin.seo.update'), [
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'meta_keywords' => 'a,b,c',
            'canonical_base_url' => 'https://bachastylo.com',
            'robots_meta' => 'index, follow',
        ]);

        $seoUpdate->assertRedirect(route('admin.seo.index'));
        $this->assertDatabaseHas('settings', ['setting_key' => 'meta_title', 'setting_value' => 'Meta Title']);
        $this->assertDatabaseHas('settings', ['setting_key' => 'canonical_base_url', 'setting_value' => 'https://bachastylo.com']);
    }

    public function test_admin_can_update_order_status(): void
    {
        $admin = $this->admin();

        $order = Order::create([
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '923001234567',
            'shipping_address' => 'Street 1',
            'city' => 'Lahore',
            'country' => 'Pakistan',
            'items' => [['name' => 'Product', 'price' => 1000, 'quantity' => 1]],
            'subtotal' => 1000,
            'shipping_fee' => 250,
            'total_amount' => 1250,
            'payment_method' => 'COD',
            'status' => 'Pending',
        ]);

        $response = $this->actingAs($admin, 'admin')->put(route('admin.orders.update', $order->id), [
            'status' => 'Processing',
        ]);

        $response->assertRedirect(route('admin.orders.show', $order->id));
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'Processing',
        ]);
    }

    public function test_admin_can_upload_media_file(): void
    {
        $admin = $this->admin();
        Storage::fake('public');

        $response = $this->actingAs($admin, 'admin')->post(route('admin.media.store'), [
            'file' => UploadedFile::fake()->create('banner.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertRedirect(route('admin.media.index'));
        $this->assertDatabaseCount('media', 1);
        $media = \App\Models\Media::firstOrFail();
        $this->assertNotEmpty($media->file_path);
    }

    public function test_settings_allowlist_blocks_unexpected_setting_keys(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin, 'admin')->put(route('admin.settings.update'), [
            'business_name' => 'Allowed',
            'unexpected_key' => 'Should not persist',
        ]);

        $response->assertRedirect(route('admin.settings.index'));

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'business_name',
            'setting_value' => 'Allowed',
        ]);

        $this->assertDatabaseMissing('settings', [
            'setting_key' => 'unexpected_key',
        ]);
    }
}
