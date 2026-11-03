<?php

namespace App\Support\Storefront;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Read-side view of the admin Settings for the storefront.
 *
 * One cached map (the same whitelist the public /api/v1/settings endpoint
 * exposes) plus the small rules the Next.js storefront used to compute on the
 * client: delivery charge, WhatsApp link, social accounts, canonical origin.
 * The admin panel flushes the cache on every save, so changes show up at once.
 */
class SiteSettings
{
    public const CACHE_KEY = 'storefront.settings';

    public const CACHE_TTL = 300;

    public const PRIMARY_HOST = 'www.bachastylo.com';

    public const SUPPORT_EMAIL = 'support@bachastylo.com';

    public const SUPPORT_HOURS = 'Mon–Sat, 10 am – 7 pm PKT';

    /** Raw WhatsApp number as the brand publishes it. */
    public const WHATSAPP_NUMBER = '+92 340 9002068';

    public const DEFAULT_SHIPPING_FEE = 250.0;

    public const DEFAULT_FREE_SHIPPING_THRESHOLD = 5000.0;

    public const DEFAULT_META_TITLE = 'Bacha Stylo | Premium Pakistani Fashion & Lifestyle';

    public const DEFAULT_META_DESCRIPTION = 'Premium Pakistani fashion and lifestyle — traditional wear, waistcoats, Chitrali pakol caps, shawls, fragrances, footwear, and refined accessories. Nationwide delivery, Cash on Delivery, JazzCash and Easypaisa.';

    public const DEFAULT_FOOTER_ABOUT = 'Bacha Stylo is a premium Pakistani fashion and lifestyle brand offering traditional wear, fragrances, footwear, accessories, and everyday essentials designed with elegance, simplicity, and authenticity.';

    /** Settings the storefront may read; identical to the public API whitelist. */
    public const WHITELIST = [
        'business_name',
        'business_email',
        'business_phone',
        'business_address',
        'logo_url',
        'footer_logo_url',
        'favicon_url',
        'facebook_url',
        'instagram_url',
        'tiktok_url',
        'whatsapp_number',
        'shipping_fee',
        'free_shipping_enabled',
        'free_shipping_threshold',
        'delivery_policy',
        'meta_title',
        'meta_description',
        'products_meta_title',
        'products_meta_description',
        'meta_keywords',
        'og_image',
        'canonical_base_url',
        'google_analytics_id',
        'facebook_pixel_id',
        'google_site_verification',
        'robots_meta',
        'home_highlight_title',
        'home_highlight_description',
        'home_highlight_image',
        'home_highlight_button',
        'home_highlight_link',
        'intro_enabled',
        'intro_bg_type',
        'intro_title',
        'intro_subtitle',
        'intro_image',
        'intro_video_url',
        'intro_social_url',
        'intro_button_text',
        'promo_enabled',
        'promo_media_type',
        'promo_title',
        'promo_subtitle',
        'promo_image',
        'promo_video_url',
        'promo_link',
        'promo_button_text',
        'cod_enabled',
        'bank_transfer_enabled',
        'easypaisa_enabled',
        'jazzcash_enabled',
        'bank_name',
        'bank_account_title',
        'bank_account_number',
        'bank_iban',
        'easypaisa_account_name',
        'easypaisa_number',
        'jazzcash_account_name',
        'jazzcash_number',
        'footer_about',
    ];

    /** Brand social accounts; admin URLs override the href when set. */
    public const SOCIAL_DEFAULTS = [
        'facebook' => ['label' => 'Facebook', 'handle' => '@BachaStyloFashion', 'href' => 'https://www.facebook.com/BachaStyloFashion', 'setting' => 'facebook_url'],
        'instagram' => ['label' => 'Instagram', 'handle' => 'BachaStyloFashion', 'href' => 'https://www.instagram.com/BachaStyloFashion', 'setting' => 'instagram_url'],
        'tiktok' => ['label' => 'TikTok', 'handle' => '@BachaStylo2', 'href' => 'https://www.tiktok.com/@BachaStylo2', 'setting' => 'tiktok_url'],
        'whatsapp' => ['label' => 'WhatsApp', 'handle' => self::WHATSAPP_NUMBER, 'href' => null, 'setting' => null],
    ];

    /** @var array<string,string|null>|null */
    private ?array $map = null;

    /** @return array<string,string|null> */
    public function all(): array
    {
        if ($this->map !== null) {
            return $this->map;
        }

        $this->map = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            try {
                $rows = Setting::query()
                    ->whereIn('setting_key', self::WHITELIST)
                    ->pluck('setting_value', 'setting_key')
                    ->all();
            } catch (\Throwable) {
                $rows = [];
            }

            if (isset($rows['google_site_verification'])) {
                $rows['google_site_verification'] = Setting::verificationToken($rows['google_site_verification']);
            }

            return $rows;
        });

        return $this->map;
    }

    /** A setting's trimmed value, or the default when missing or blank. */
    public function get(string $key, ?string $default = null): ?string
    {
        $value = $this->all()[$key] ?? null;
        $value = is_string($value) ? trim($value) : $value;

        return ($value === null || $value === '') ? $default : (string) $value;
    }

    /** "0" switches a feature off; anything else (or missing) means on. */
    public function enabled(string $key): bool
    {
        return $this->get($key, '1') !== '0';
    }

    public function businessName(): string
    {
        return $this->get('business_name', 'Bacha Stylo');
    }

    public function logoUrl(): ?string
    {
        return $this->get('logo_url');
    }

    public function footerLogoUrl(): ?string
    {
        return $this->get('footer_logo_url') ?? $this->get('logo_url');
    }

    public function faviconUrl(): string
    {
        return $this->get('favicon_url', asset('images/BachaStylo favicon.png'));
    }

    public function metaTitle(): string
    {
        return $this->get('meta_title', self::DEFAULT_META_TITLE);
    }

    public function metaDescription(): string
    {
        return $this->get('meta_description', self::DEFAULT_META_DESCRIPTION);
    }

    /** Admin meta for the all-products listing (/products), blank when unset. */
    public function productsMetaTitle(): ?string
    {
        return $this->pageTitle($this->get('products_meta_title'));
    }

    public function productsMetaDescription(): ?string
    {
        $v = trim((string) $this->get('products_meta_description', ''));

        return $v !== '' ? $v : null;
    }

    /**
     * An admin-entered page title without a trailing "| Bacha Stylo": the
     * layout appends the business name itself, so a title saved with the
     * brand (as the SEO sheets had it) would otherwise read "… | Bacha
     * Stylo | Bacha Stylo". Null when blank.
     */
    public function pageTitle(?string $title): ?string
    {
        $title = trim((string) $title);
        if ($title === '') {
            return null;
        }
        $brand = preg_quote($this->businessName(), '/');
        $title = trim(preg_replace('/\s*[|\-–—]\s*'.$brand.'(\s+Fashion(\s+Home)?)?\s*$/iu', '', $title) ?? $title);

        return $title !== '' ? $title : null;
    }

    public function footerAbout(): string
    {
        return $this->get('footer_about', self::DEFAULT_FOOTER_ABOUT);
    }

    public function supportEmail(): string
    {
        return self::SUPPORT_EMAIL;
    }

    /** Support phone = the brand WhatsApp number (admin override wins). */
    public function supportPhone(): string
    {
        return $this->whatsapp()['number'];
    }

    public function supportHours(): string
    {
        return self::SUPPORT_HOURS;
    }

    /* ------------------------------------------------------------------ */
    /* Shipping                                                            */
    /* ------------------------------------------------------------------ */

    /** @return array{fee: float, threshold: float, freeEnabled: bool} */
    public function shippingRule(): array
    {
        $fee = $this->get('shipping_fee');
        $threshold = $this->get('free_shipping_threshold');

        return [
            'fee' => is_numeric($fee) ? (float) $fee : self::DEFAULT_SHIPPING_FEE,
            'threshold' => is_numeric($threshold) ? (float) $threshold : self::DEFAULT_FREE_SHIPPING_THRESHOLD,
            'freeEnabled' => $this->enabled('free_shipping_enabled'),
        ];
    }

    /** Delivery charge for a subtotal, mirroring OrderService::shippingFor(). */
    public function shippingFor(float $subtotal): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }
        $rule = $this->shippingRule();

        return ($rule['freeEnabled'] && $subtotal >= $rule['threshold']) ? 0.0 : $rule['fee'];
    }

    /** Rupees still needed to unlock free shipping, or null when it doesn't apply. */
    public function remainingForFreeShipping(float $subtotal): ?float
    {
        $rule = $this->shippingRule();
        if (! $rule['freeEnabled'] || $subtotal <= 0 || $subtotal >= $rule['threshold']) {
            return null;
        }

        return $rule['threshold'] - $subtotal;
    }

    /** @return list<string> The admin's delivery policy, one paragraph per line. */
    public function deliveryPolicyParagraphs(): array
    {
        $lines = preg_split('/\r?\n/', (string) $this->get('delivery_policy', '')) ?: [];

        return array_values(array_filter(array_map('trim', $lines), fn ($l) => $l !== ''));
    }

    /* ------------------------------------------------------------------ */
    /* Contact & social                                                    */
    /* ------------------------------------------------------------------ */

    /** @return array{number: string, digits: string, href: string} */
    public function whatsapp(): array
    {
        $number = $this->get('whatsapp_number') ?: self::WHATSAPP_NUMBER;
        $digits = preg_replace('/[^0-9]/', '', $number) ?? '';

        return ['number' => $number, 'digits' => $digits, 'href' => 'https://wa.me/'.$digits];
    }

    /** A wa.me link with a pre-filled message. */
    public function whatsappHref(string $message): string
    {
        return $this->whatsapp()['href'].'?text='.rawurlencode($message);
    }

    /**
     * @return array<string, array{key: string, label: string, handle: string, href: string}>
     *         Facebook, Instagram, TikTok and WhatsApp in display order.
     */
    public function socials(bool $includeWhatsApp = true): array
    {
        $out = [];
        foreach (self::SOCIAL_DEFAULTS as $key => $account) {
            if ($key === 'whatsapp') {
                if (! $includeWhatsApp) {
                    continue;
                }
                $wa = $this->whatsapp();
                $out[$key] = ['key' => $key, 'label' => $account['label'], 'handle' => $wa['number'], 'href' => $wa['href']];

                continue;
            }
            $override = $account['setting'] ? $this->get($account['setting']) : null;
            $out[$key] = [
                'key' => $key,
                'label' => $account['label'],
                'handle' => $account['handle'],
                'href' => $override ?: $account['href'],
            ];
        }

        return $out;
    }

    /* ------------------------------------------------------------------ */
    /* SEO                                                                 */
    /* ------------------------------------------------------------------ */

    /**
     * Site origin for absolute URLs: the admin canonical base, with the host
     * normalised to the primary www host so the site never splits in two.
     */
    public function siteOrigin(): string
    {
        $fallback = 'https://'.self::PRIMARY_HOST;
        $raw = $this->get('canonical_base_url');
        if (! $raw) {
            return $fallback;
        }
        $parts = parse_url($raw);
        if (! is_array($parts) || empty($parts['host'])) {
            return $fallback;
        }
        $host = strtolower($parts['host']);
        $bare = preg_replace('/^www\./', '', $host);
        if ($bare === preg_replace('/^www\./', '', self::PRIMARY_HOST)) {
            $host = self::PRIMARY_HOST;
        }
        $port = isset($parts['port']) && ! in_array((int) $parts['port'], [80, 443], true) ? ':'.$parts['port'] : '';

        return 'https://'.$host.$port;
    }

    /** Absolute canonical URL for a storefront path (query string kept). */
    public function canonical(string $path = '/'): string
    {
        $path = '/'.ltrim($path, '/');

        return rtrim($this->siteOrigin(), '/').($path === '/' ? '' : $path);
    }

    public function robots(): ?string
    {
        $value = strtolower((string) $this->get('robots_meta', ''));
        if ($value === '') {
            return null;
        }

        return (str_contains($value, 'noindex') ? 'noindex' : 'index').', '.(str_contains($value, 'nofollow') ? 'nofollow' : 'follow');
    }

    public function isNoindex(): bool
    {
        return str_contains(strtolower((string) $this->get('robots_meta', '')), 'noindex');
    }

    public function gaId(): ?string
    {
        $v = (string) $this->get('google_analytics_id', '');

        return preg_match('/^G-[A-Z0-9]+$/', $v) ? $v : null;
    }

    public function pixelId(): ?string
    {
        $v = (string) $this->get('facebook_pixel_id', '');

        return preg_match('/^\d{5,20}$/', $v) ? $v : null;
    }

    public function googleSiteVerification(): ?string
    {
        return $this->get('google_site_verification');
    }

    /* ------------------------------------------------------------------ */
    /* Payments                                                            */
    /* ------------------------------------------------------------------ */

    public const PAYMENT_LABELS = [
        'cod' => 'Cash on Delivery',
        'bank_transfer' => 'Bank Transfer',
        'easypaisa' => 'EasyPaisa',
        'jazzcash' => 'JazzCash',
    ];

    /** @return list<string> Enabled payment method keys (COD as the fallback). */
    public function enabledPaymentMethods(): array
    {
        $enabled = [];
        foreach (['cod' => 'cod_enabled', 'bank_transfer' => 'bank_transfer_enabled', 'easypaisa' => 'easypaisa_enabled', 'jazzcash' => 'jazzcash_enabled'] as $key => $setting) {
            if ($this->enabled($setting)) {
                $enabled[] = $key;
            }
        }

        return $enabled ?: ['cod'];
    }

    /** Forget the cached map (the admin panel already calls Cache::flush()). */
    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
