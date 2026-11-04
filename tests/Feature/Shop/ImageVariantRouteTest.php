<?php

namespace Tests\Feature\Shop;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ImageVariantRouteTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        File::deleteDirectory(public_path('img/96'));
        parent::tearDown();
    }

    public function test_a_packaged_image_is_resized_written_once_and_served_with_a_long_cache(): void
    {
        $key = 'l/images/BachaStylo favicon.png.webp';
        File::deleteDirectory(public_path('img/96'));

        $first = $this->get('/img/96/'.rawurlencode('l').'/images/'.rawurlencode('BachaStylo favicon.png').'.webp');
        $first->assertOk()
            ->assertHeader('Content-Type', 'image/webp')
            ->assertHeader('Cache-Control', 'immutable, max-age=31536000, public');
        $this->assertStringNotContainsString('set-cookie', strtolower(implode(' ', array_keys($first->headers->all()))));

        $file = public_path('img/96/'.$key);
        $this->assertFileExists($file);
        [$w] = getimagesize($file);
        $this->assertSame(96, $w);

        // Second request is served from the written file.
        $this->get('/img/96/l/images/BachaStylo%20favicon.png.webp')->assertOk()->assertHeader('Content-Type', 'image/webp');
    }

    public function test_full_keeps_the_original_dimensions(): void
    {
        File::deleteDirectory(public_path('img/full'));
        [$ow] = getimagesize(public_path('images/BachaStylo favicon.png'));
        $this->get('/img/full/l/images/BachaStylo%20favicon.png.webp')->assertOk()->assertHeader('Content-Type', 'image/webp');
        [$w] = getimagesize(public_path('img/full/l/images/BachaStylo favicon.png.webp'));
        $this->assertSame($ow, $w);
        File::deleteDirectory(public_path('img/full'));
    }

    public function test_unknown_sizes_sources_and_traversal_are_404(): void
    {
        $this->get('/img/500/l/images/BachaStylo%20favicon.png.webp')->assertNotFound();
        $this->get('/img/96/l/images/missing.png.webp')->assertNotFound();
        $this->get('/img/96/l/..%2F..%2F.env.webp')->assertNotFound();
        $this->get('/img/96/x/images/BachaStylo%20favicon.png.webp')->assertNotFound();
        $this->get('/img/96/l/images/BachaStylo%20favicon.png.gif')->assertNotFound();
    }
}
