<?php

namespace Tests\Feature\Shop;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceMarkupTest extends TestCase
{
    use RefreshDatabase;

    public function test_fonts_do_not_block_render_and_analytics_wait_for_interaction(): void
    {
        Setting::create(['setting_key' => 'google_analytics_id', 'setting_value' => 'G-TEST12345']);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#<link rel="preload" as="font" type="font/woff2" href="[^"]*/fonts/montserrat-latin\.woff2" crossorigin>#', $html);
        $this->assertStringNotContainsString('fonts.googleapis.com', $html);
        $this->assertStringContainsString("gtag('config', \"G-TEST12345\")", $html);
        $this->assertStringNotContainsString('<script async src="https://www.googletagmanager.com', $html);
        $this->assertStringContainsString("['pointerdown', 'touchstart', 'keydown', 'scroll', 'mousemove']", $html);
    }

    public function test_media_images_render_with_resized_srcset(): void
    {
        config(['filesystems.disks.s3.url' => 'https://media.bachastylo.com']);
        Category::create(['name' => 'Shawls', 'slug' => 'shawls', 'image' => 'https://media.bachastylo.com/categories/c1.png']);
        Product::create(['name' => 'Ruby Shawl', 'slug' => 'ruby-shawl', 'category' => 'shawls', 'price' => '1000', 'image' => 'https://media.bachastylo.com/products/p1.jpg']);

        $this->get('/')->assertOk()
            ->assertSee('srcset="/img/320/m/categories/c1.png.webp 320w, /img/360/m/categories/c1.png.webp 360w, /img/480/m/categories/c1.png.webp 480w', false);
        $this->get('/products')->assertOk()
            ->assertSee('/img/480/m/products/p1.jpg.webp 480w', false)
            ->assertSee('sizes="(max-width: 640px) 50vw, (max-width: 1024px) 33vw, 25vw"', false);
        $this->get('/products/ruby-shawl')->assertOk()
            ->assertSee('/img/768/m/products/p1.jpg.webp 768w', false);
    }
}
