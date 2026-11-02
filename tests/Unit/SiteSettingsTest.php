<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Support\Storefront\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function set(array $pairs): SiteSettings
    {
        Setting::query()->delete();
        foreach ($pairs as $k => $v) {
            Setting::updateOrCreate(['setting_key' => $k], ['setting_value' => $v]);
        }
        SiteSettings::forget();

        return new SiteSettings;
    }

    public function test_shipping_rule_defaults_and_overrides(): void
    {
        $s = $this->set([]);
        $this->assertSame(['fee' => 250.0, 'threshold' => 5000.0, 'freeEnabled' => true], $s->shippingRule());
        $this->assertSame(250.0, $s->shippingFor(1000));
        $this->assertSame(0.0, $s->shippingFor(5000));
        $this->assertSame(0.0, $s->shippingFor(0));
        $this->assertSame(4000.0, $s->remainingForFreeShipping(1000));
        $this->assertNull($s->remainingForFreeShipping(6000));

        $s = $this->set(['shipping_fee' => '300', 'free_shipping_threshold' => '8000', 'free_shipping_enabled' => '0']);
        $this->assertSame(300.0, $s->shippingFor(9000), 'free shipping switched off keeps the fee');
        $this->assertNull($s->remainingForFreeShipping(1000));
    }

    public function test_site_origin_normalises_to_the_primary_host(): void
    {
        $this->assertSame('https://www.bachastylo.com', $this->set([])->siteOrigin());
        $this->assertSame('https://www.bachastylo.com', $this->set(['canonical_base_url' => 'http://bachastylo.com/'])->siteOrigin());
        $this->assertSame('https://shop.example.com', $this->set(['canonical_base_url' => 'https://shop.example.com'])->siteOrigin());
        $this->assertSame('https://www.bachastylo.com/products?category=shawls', $this->set([])->canonical('/products?category=shawls'));
    }

    public function test_tracking_ids_are_validated(): void
    {
        $s = $this->set(['google_analytics_id' => 'G-ABC123', 'facebook_pixel_id' => '123456789']);
        $this->assertSame('G-ABC123', $s->gaId());
        $this->assertSame('123456789', $s->pixelId());

        $s = $this->set(['google_analytics_id' => '<script>', 'facebook_pixel_id' => 'abc']);
        $this->assertNull($s->gaId());
        $this->assertNull($s->pixelId());
    }

    public function test_whatsapp_socials_and_payments(): void
    {
        $s = $this->set(['whatsapp_number' => '+92 300 1234567', 'instagram_url' => 'https://instagram.com/custom', 'jazzcash_enabled' => '0']);
        $this->assertSame('923001234567', $s->whatsapp()['digits']);
        $this->assertStringStartsWith('https://wa.me/923001234567?text=Hi', $s->whatsappHref('Hi there'));
        $this->assertSame('https://instagram.com/custom', $s->socials()['instagram']['href']);
        $this->assertSame('https://www.facebook.com/BachaStyloFashion', $s->socials()['facebook']['href']);
        $this->assertSame(['cod', 'bank_transfer', 'easypaisa'], $s->enabledPaymentMethods());
        $this->assertSame('index, nofollow', $this->set(['robots_meta' => 'index, nofollow'])->robots());
        $this->assertTrue($this->set(['robots_meta' => 'noindex'])->isNoindex());
    }
}
