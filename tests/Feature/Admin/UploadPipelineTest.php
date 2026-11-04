<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadPipelineTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): AdminUser
    {
        return AdminUser::create(['username' => 'a', 'email' => 'a@example.com', 'password' => Hash::make('secret123'), 'login_attempts' => 0]);
    }

    public function test_uploaded_images_are_stored_as_capped_webp(): void
    {
        Storage::fake('s3');
        config(['filesystems.default' => 's3', 'filesystems.disks.s3.url' => 'https://media.test']);

        $this->actingAs($this->admin(), 'admin')->post(route('admin.categories.store'), [
            'name' => 'Caps', 'image_file' => UploadedFile::fake()->image('huge photo.PNG', 3000, 1500),
        ])->assertSessionHasNoErrors();

        $category = Category::firstOrFail();
        // The fake disk builds its own URL; what matters is the stored object.
        $this->assertMatchesRegularExpression('#/categories/[0-9a-f-]{36}\.webp$#', $category->image);
        $path = 'categories/'.basename($category->image);
        Storage::disk('s3')->assertExists($path);
        [$w, $h, $type] = getimagesizefromstring(Storage::disk('s3')->get($path));
        $this->assertSame(IMAGETYPE_WEBP, $type);
        $this->assertSame(2000, $w);
        $this->assertSame(1000, $h);
    }

    public function test_a_file_that_is_not_really_an_image_is_rejected(): void
    {
        Storage::fake('s3');
        config(['filesystems.default' => 's3']);
        $bogus = UploadedFile::fake()->createWithContent('photo.jpg', 'this is not image data');

        $this->actingAs($this->admin(), 'admin')->post(route('admin.categories.store'), [
            'name' => 'Caps', 'image_file' => $bogus,
        ])->assertSessionHasErrors();
        $this->assertDatabaseMissing('categories', ['name' => 'Caps']);
    }

    public function test_every_admin_write_clears_the_storefront_cache(): void
    {
        Cache::put('storefront.page.anything', 'stale', 600);
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')->get(route('admin.categories.index'))->assertOk();
        $this->assertSame('stale', Cache::get('storefront.page.anything'), 'a GET must not flush');

        $this->actingAs($admin, 'admin')->post(route('admin.categories.store'), ['name' => 'Caps'])->assertRedirect();
        $this->assertNull(Cache::get('storefront.page.anything'));
    }

    public function test_storefront_html_is_not_cached_by_proxies_and_og_image_is_served_from_www(): void
    {
        config(['filesystems.disks.s3.url' => 'https://media.bachastylo.com']);
        \App\Models\Setting::updateOrCreate(['setting_key' => 'og_image'], ['setting_value' => 'https://media.bachastylo.com/branding/share.png']);

        $html = $this->get('/')->assertOk()
            ->assertHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, private')
            ->getContent();
        $this->assertMatchesRegularExpression('#<meta property="og:image" content="https?://[^/]+/img/full/m/branding/share\.png\.jpg\?v=2">#', $html);
    }
}
