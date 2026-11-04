<?php

namespace Tests\Unit;

use App\Support\Storefront\ImageVariant;
use Tests\TestCase;

class ImageVariantTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['filesystems.disks.s3.url' => 'https://media.example.com', 'app.url' => 'https://www.example.com']);
    }

    public function test_media_bucket_and_packaged_images_get_variant_urls(): void
    {
        $this->assertSame('/img/480/m/categories/a-b.png.webp?v=2', ImageVariant::url('https://media.example.com/categories/a-b.png', 480));
        $this->assertSame('/img/96/l/images/logo.png.png?v=2', ImageVariant::url('https://www.example.com/images/logo.png', 96, 'png'));
        $this->assertSame('/img/96/l/images/logo.png.png?v=2', ImageVariant::url('/images/logo.png', 96, 'png'));
        $this->assertSame(
            '/img/320/m/p/x.jpg.webp?v=2 320w, /img/640/m/p/x.jpg.webp?v=2 640w',
            ImageVariant::srcset('https://media.example.com/p/x.jpg', [320, 640])
        );
    }

    public function test_other_sources_sizes_and_formats_are_refused(): void
    {
        $this->assertNull(ImageVariant::url('https://images.unsplash.com/photo.jpg', 480));
        $this->assertNull(ImageVariant::url('https://media.example.com/p/x.jpg?w=1', 480));
        $this->assertNull(ImageVariant::url('https://media.example.com/../x.jpg', 480));
        $this->assertNull(ImageVariant::url('https://media.example.com/p/x.svg', 480));
        $this->assertNull(ImageVariant::url('https://media.example.com/p/x.jpg', 500));
        $this->assertNull(ImageVariant::url('https://media.example.com/p/x.jpg', 480, 'gif'));
        $this->assertNull(ImageVariant::srcset('data:image/png;base64,AAAA'));
        $this->assertNull(ImageVariant::srcset(null));
    }
}
