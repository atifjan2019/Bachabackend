<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\Storefront\Catalog;
use App\Support\Storefront\SiteSettings;
use App\Support\Storefront\VideoEmbed;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /** Short editorial taglines for spotlighted collections, keyed by slug. */
    public const COLLECTION_TAGLINES = [
        'traditional-wear' => 'Timeless silhouettes, tailored for every occasion.',
        'premium-waistcoats' => 'Tailored waistcoats and refined occasion wear.',
        'chitrali-pakol' => 'Handcrafted caps rooted in northern heritage.',
        'shawls' => 'Elegant drapes woven with warmth and grace.',
        'clothes' => 'Refined everyday essentials for the modern wardrobe.',
        'peshawari-chappal' => 'Handmade leather footwear, built to last.',
        'luxury-fragrances' => 'Signature scents for a lasting impression.',
        'accessories' => 'Finishing touches that define the look.',
        'beauty-care' => 'Premium grooming and care, thoughtfully curated.',
    ];

    public const DEFAULT_TAGLINE = 'A curated edit of premium essentials.';

    public const BESTSELLER_LABELS = ['Trending Now', 'Limited Edition', 'Customer Favourite', 'New Arrival'];

    public const HERO_IMAGE = 'https://media.bachastylo.com/media/c20808da-4ba4-4ea0-967f-16e75b9422bf.jpg';

    public function index(SiteSettings $settings): View
    {
        $categories = Catalog::categoryTree();
        $featured = Catalog::featured(3);
        $bestSellers = Catalog::bestSellers(8);

        $heritageCategory = $categories->first(fn (Category $c) => preg_match('/tradition|heritage|waistcoat|shawl/i', (string) $c->slug))
            ?? $categories->first();

        $spotlights = $featured->isNotEmpty()
            ? $featured->take(3)->map(fn ($p) => [
                'href' => $p->url(),
                'image' => $p->primaryImage(),
                'eyebrow' => $p->category['name'],
                'title' => $p->name,
                'subtitle' => pkr($p->displayPrice()),
            ])->values()->all()
            : $categories->take(3)->map(fn (Category $c) => [
                'href' => route('shop.products', ['category' => $c->slug]),
                'image' => $c->image ?: null,
                'eyebrow' => 'Collection',
                'title' => $c->name,
                'subtitle' => self::COLLECTION_TAGLINES[$c->slug] ?? self::DEFAULT_TAGLINE,
            ])->values()->all();

        $socials = $settings->socials();
        $socialCards = [
            ['name' => 'Instagram', 'key' => 'instagram', 'href' => $socials['instagram']['href']],
            ['name' => 'Facebook', 'key' => 'facebook', 'href' => $socials['facebook']['href']],
            ['name' => 'TikTok', 'key' => 'tiktok', 'href' => $socials['tiktok']['href']],
            ['name' => 'WhatsApp', 'key' => 'whatsapp', 'href' => $socials['whatsapp']['href']],
        ];

        // Hero / launch banner
        $introEnabled = $settings->get('intro_enabled') === '1';
        $introVideo = $settings->get('intro_video_url');
        $introImage = $settings->get('intro_image');
        $bgType = $settings->get('intro_bg_type') ?: ($introVideo ? 'video' : 'image');
        $hero = [
            'enabled' => $introEnabled,
            'bgType' => $bgType,
            'videoUrl' => $introVideo,
            'embedUrl' => $introVideo ? VideoEmbed::background($introVideo) : null,
            'image' => $introImage,
            'socialUrl' => $settings->get('intro_social_url'),
            'button' => $settings->get('intro_button_text'),
            'hasVideo' => $introEnabled && $bgType === 'video' && (bool) $introVideo,
            'hasImage' => $introEnabled && ! ($bgType === 'video' && $introVideo) && (bool) $introImage,
        ];

        // Promo popup
        $promoVideo = $settings->get('promo_video_url');
        $promoType = $settings->get('promo_media_type') ?: 'image';
        $promo = [
            'enabled' => $settings->get('promo_enabled') === '1',
            'mediaType' => $promoType,
            'image' => $settings->get('promo_image'),
            'videoUrl' => $promoVideo,
            'embedUrl' => $promoType === 'video' && $promoVideo ? VideoEmbed::playable($promoVideo) : null,
            'link' => $settings->get('promo_link'),
            'title' => $settings->get('promo_title'),
            'subtitle' => $settings->get('promo_subtitle'),
            'buttonText' => $settings->get('promo_button_text') ?: 'Watch Now',
        ];

        $origin = $settings->siteOrigin();
        $jsonLd = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => $settings->businessName(),
                'url' => $origin,
                'logo' => $settings->logoUrl() ?: asset('images/BachaStylo Logo for web.png'),
                'email' => $settings->supportEmail(),
                'telephone' => $settings->supportPhone(),
                'sameAs' => array_values(array_map(fn ($s) => $s['href'], $socials)),
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => $settings->businessName(),
                'url' => $origin,
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => $origin.'/products?search={search_term_string}',
                    'query-input' => 'required name=search_term_string',
                ],
            ],
        ];

        return view('shop.home', compact(
            'categories', 'bestSellers', 'spotlights', 'heritageCategory', 'socialCards', 'hero', 'promo', 'jsonLd'
        ) + ['bestsellerLabels' => self::BESTSELLER_LABELS, 'heroImage' => self::HERO_IMAGE]);
    }
}
