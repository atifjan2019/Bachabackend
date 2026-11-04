<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_picker_lists_catalogue_images_without_listing_the_bucket(): void
    {
        config(['filesystems.disks.s3.url' => 'https://media.bachastylo.com']);
        $admin = AdminUser::create(['username' => 'a', 'email' => 'a@example.com', 'password' => Hash::make('secret123'), 'login_attempts' => 0]);
        Media::create(['file_name' => 'banner.jpg', 'file_path' => 'https://media.bachastylo.com/media/banner.jpg', 'file_type' => 'image/jpeg', 'file_size' => 10]);
        Product::create(['name' => 'P', 'slug' => 'p', 'price' => '1', 'image' => 'https://media.bachastylo.com/products/a.png',
            'gallery' => ['https://media.bachastylo.com/products/b.webp'], 'colors' => [['name' => 'Red', 'image' => 'https://media.bachastylo.com/products/c.jpg']]]);
        Category::create(['name' => 'C', 'slug' => 'c', 'image' => 'https://media.bachastylo.com/categories/d.png']);
        Setting::updateOrCreate(['setting_key' => 'logo_url'], ['setting_value' => 'https://media.bachastylo.com/branding/logo.avif']);
        Product::create(['name' => 'Doc', 'slug' => 'doc', 'price' => '1', 'image' => 'https://media.bachastylo.com/products/manual.pdf']);

        $files = $this->actingAs($admin, 'admin')->getJson(route('admin.media.api.list'))->assertOk()->json();
        $urls = array_column($files, 'url');
        foreach (['media/banner.jpg', 'products/a.png', 'products/b.webp', 'products/c.jpg', 'categories/d.png', 'branding/logo.avif'] as $p) {
            $this->assertContains('https://media.bachastylo.com/'.$p, $urls, $p);
        }
        $this->assertNotContains('https://media.bachastylo.com/products/manual.pdf', $urls);
        $first = collect($files)->firstWhere('url', 'https://media.bachastylo.com/products/a.png');
        $this->assertSame(['path' => 'products/a.png', 'url' => 'https://media.bachastylo.com/products/a.png', 'thumb' => '/img/240/m/products/a.png.webp?v=2', 'name' => 'a.png', 'folder' => 'products', 'used_by' => ['Product BS-00001 – P']], $first);
        $this->assertSame('media/banner.jpg', $files[0]['path']);

        $this->actingAs($admin, 'admin')->get(route('admin.media.index'))->assertOk()->assertSee('a.png');
    }

    public function test_an_image_in_use_cannot_be_deleted_and_an_unused_one_can(): void
    {
        config(['filesystems.disks.s3.url' => 'https://media.bachastylo.com', 'filesystems.default' => 's3']);
        $admin = AdminUser::create(['username' => 'a', 'email' => 'a@example.com', 'password' => Hash::make('secret123'), 'login_attempts' => 0]);
        Category::create(['name' => 'Shawls', 'slug' => 'shawls', 'image' => 'https://media.bachastylo.com/categories/d.png']);
        Media::create(['file_name' => 'old.jpg', 'file_path' => 'https://media.bachastylo.com/media/old.jpg', 'file_type' => 'image/jpeg', 'file_size' => 10]);
        Storage::shouldReceive('disk')->with('s3')->andReturn($fake = \Mockery::mock());
        $fake->shouldReceive('url')->andReturnUsing(fn ($p) => 'https://media.bachastylo.com/'.$p);
        $fake->shouldReceive('delete')->with('media/old.jpg')->once()->andReturn(true);
        $fake->shouldReceive('allFiles')->andThrow(new \RuntimeException('no list'));

        $this->actingAs($admin, 'admin')->postJson(route('admin.media.api.deleteR2'), ['path' => 'categories/d.png'])
            ->assertStatus(409)->assertJsonFragment(['used_by' => ['Category: Shawls']]);
        $this->assertDatabaseHas('categories', ['slug' => 'shawls', 'image' => 'https://media.bachastylo.com/categories/d.png']);

        $this->actingAs($admin, 'admin')->postJson(route('admin.media.api.deleteR2'), ['path' => 'media/old.jpg'])
            ->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseMissing('media', ['file_path' => 'https://media.bachastylo.com/media/old.jpg']);
    }
}
