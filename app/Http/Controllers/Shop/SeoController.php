<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Product;
use App\Support\Storefront\Catalog;
use App\Support\Storefront\SiteSettings;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    /** Public pages. Account, auth and checkout routes are deliberately absent. */
    public const STATIC_PATHS = ['/', '/products', '/about', '/blogs', '/faq', '/contact', '/privacy', '/terms', '/refund'];

    public const PRIVATE_PATHS = [
        '/account', '/account/', '/cart', '/checkout', '/checkout/',
        '/login', '/register', '/forgot-password', '/reset-password', '/admin',
    ];

    public function sitemap(SiteSettings $settings): Response
    {
        $xml = Cache::remember('storefront.sitemap', 3600, function () use ($settings) {
            $origin = rtrim($settings->siteOrigin(), '/');
            $now = now()->toAtomString();
            $entries = [];

            foreach (self::STATIC_PATHS as $path) {
                $entries[] = [
                    'loc' => $origin.($path === '/' ? '' : $path),
                    'lastmod' => $now,
                    'changefreq' => in_array($path, ['/', '/products'], true) ? 'daily' : 'weekly',
                    'priority' => $path === '/' ? '1.0' : ($path === '/products' ? '0.9' : '0.5'),
                ];
            }

            foreach (Catalog::flatCategories() as $c) {
                if (! $c->slug) {
                    continue;
                }
                $entries[] = [
                    'loc' => $origin.'/products?category='.rawurlencode($c->slug),
                    'lastmod' => $now,
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ];
            }

            Product::query()->whereNotNull('slug')->orderBy('id')
                ->select(['slug', 'updated_at', 'created_at'])
                ->chunk(500, function ($products) use (&$entries, $origin, $now) {
                    foreach ($products as $p) {
                        $entries[] = [
                            'loc' => $origin.'/products/'.rawurlencode($p->slug),
                            'lastmod' => optional($p->updated_at ?? $p->created_at)->toAtomString() ?? $now,
                            'changefreq' => 'weekly',
                            'priority' => '0.8',
                        ];
                    }
                });

            foreach (BlogPost::query()->where('status', true)->orderBy('id')->get(['slug', 'updated_at', 'created_at']) as $b) {
                if (! $b->slug) {
                    continue;
                }
                $entries[] = [
                    'loc' => $origin.'/blogs/'.rawurlencode($b->slug),
                    'lastmod' => optional($b->updated_at ?? $b->created_at)->toAtomString() ?? $now,
                    'changefreq' => 'monthly',
                    'priority' => '0.6',
                ];
            }

            // One URL per location.
            $seen = [];
            $entries = array_values(array_filter($entries, function ($e) use (&$seen) {
                if (isset($seen[$e['loc']])) {
                    return false;
                }
                $seen[$e['loc']] = true;

                return true;
            }));

            // The XML declaration is prepended here: Blade would read `<?xml` as a PHP tag.
            return '<?xml version="1.0" encoding="UTF-8"?>'."\n".view('shop.seo.sitemap', ['entries' => $entries])->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(SiteSettings $settings): Response
    {
        $origin = rtrim($settings->siteOrigin(), '/');
        $lines = ['User-agent: *'];

        if ($settings->isNoindex()) {
            $lines[] = 'Disallow: /';
        } else {
            $lines[] = 'Allow: /';
            foreach (self::PRIVATE_PATHS as $path) {
                $lines[] = 'Disallow: '.$path;
            }
        }

        $lines[] = '';
        $lines[] = 'Host: '.$origin;
        $lines[] = 'Sitemap: '.$origin.'/sitemap.xml';

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
