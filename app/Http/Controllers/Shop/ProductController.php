<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\Storefront\Catalog;
use App\Support\Storefront\ProductQuery;
use App\Support\Storefront\Sizes;
use App\Support\Storefront\StorefrontProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ProductController extends Controller
{
    /** Shop listing: every filter lives in the URL and runs in SQL. */
    public function index(Request $request): View
    {
        $query = ProductQuery::fromRequest($request);
        $results = $query->results();
        $category = $query->activeCategory();
        $total = $results->total();
        $settings = site();

        // Titles: a category page uses the category's own meta fields, the
        // plain listing the "Products page" meta from SEO settings; either
        // falls back to the name. The layout appends " | Bacha Stylo".
        if ($category) {
            $title = $settings->pageTitle($category->meta_title) ?? trim($category->name);
            $description = trim((string) $category->meta_description)
                ?: "Shop {$category->name} at Bacha Stylo. Nationwide delivery, Cash on Delivery, JazzCash and Easypaisa.";
        } else {
            $title = $settings->productsMetaTitle() ?? 'Shop';
            $description = $settings->productsMetaDescription()
                ?? 'Browse every Bacha Stylo piece — waistcoats, shawls, kurta sets, pakol caps and accessories. Nationwide delivery across Pakistan.';
        }

        $breadcrumbs = null;
        if ($category) {
            $breadcrumbs = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $settings->canonical('/')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Shop', 'item' => $settings->canonical('/products')],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $category->name, 'item' => $settings->canonical('/products?category='.rawurlencode($category->slug))],
                ],
            ];
        }

        return view('shop.products.index', [
            'q' => $query,
            'results' => $results,
            'products' => $results->items(),
            'total' => $total,
            'category' => $category,
            'title' => $title,
            'description' => $description,
            'canonical' => $settings->canonical($query->canonicalPath()),
            'noindex' => $query->search !== '' || count($query->categories) > 1 || $query->priceActive() || $query->activeSizes() !== [] || $query->collection !== 'all',
            'breadcrumbs' => $breadcrumbs,
            'sizeGroups' => $query->sizeGroups(),
        ]);
    }

    /** Product detail page. */
    public function show(string $slug): View
    {
        $model = Cache::remember('storefront.product.'.md5($slug), Catalog::TTL, fn () => Product::query()->storefront()->where('slug', $slug)->first());
        abort_unless($model, 404);

        $product = StorefrontProduct::make($model);
        $settings = site();

        $reviews = $model->reviews()
            ->where('is_approved', true)
            ->orderByDesc('id')
            ->get(['id', 'author_name', 'rating', 'comment', 'created_at']);
        $reviewCount = $reviews->count();
        $reviewAverage = $reviewCount ? round((float) $reviews->avg('rating'), 1) : 0.0;

        $related = Catalog::related($product, 4);

        // The layout appends " | {business}"; an admin meta_title that already
        // carries the brand suffix (the Next page used it as an absolute title)
        // would otherwise read "… | Bacha Stylo | Bacha Stylo".
        $title = $settings->pageTitle($product->meta_title) ?? $product->name;
        $description = $product->meta_description
            ?: (strip_html_excerpt($product->description) ?: "{$product->name} from Bacha Stylo. Nationwide delivery, Cash on Delivery, JazzCash and Easypaisa.");
        $image = $product->primaryImage();
        $url = $settings->canonical('/products/'.rawurlencode($product->slug));

        // Variant lists for the purchase panel (unique sizes/colours, as the React app derived them).
        $sizes = array_values(array_unique(array_column($product->variants, 'size')));
        $colors = [];
        foreach ($product->variants as $v) {
            if (! isset($colors[$v['color']])) {
                $colors[$v['color']] = ['color' => $v['color'], 'color_hex' => $v['color_hex'], 'in_stock' => $v['stock'] > 0];
            } elseif ($v['stock'] > 0) {
                $colors[$v['color']]['in_stock'] = true;
            }
        }
        $grouped = Sizes::group($sizes);
        $useBands = $grouped['other'] === [] && count($grouped['banded']) >= 2;

        $config = $product->toArray() + [
            'description_excerpt' => mb_substr(trim(preg_replace('/<[^>]*>/', '', $product->description) ?? ''), 0, 140),
            'size_options' => $sizes,
            'color_options' => array_values($colors),
            'use_bands' => $useBands,
            'bands' => array_map(fn ($g) => ['key' => $g['band']['key'], 'label' => $g['band']['label'], 'shortLabel' => $g['band']['shortLabel'], 'sizes' => $g['sizes']], $grouped['banded']),
            'whatsapp_digits' => $settings->whatsapp()['digits'],
        ];

        $offerAvailability = $product->in_stock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock';
        $jsonLd = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => $product->name,
                'description' => $description,
                'sku' => $product->sku,
                'image' => array_column($product->images, 'url'),
                'url' => $url,
                'brand' => ['@type' => 'Brand', 'name' => 'Bacha Stylo'],
                'category' => $product->category['name'],
                'offers' => [
                    '@type' => 'Offer',
                    'url' => $url,
                    'priceCurrency' => 'PKR',
                    'price' => number_format($product->displayPrice(), 0, '.', ''),
                    'availability' => $offerAvailability,
                    'itemCondition' => 'https://schema.org/NewCondition',
                    'seller' => ['@type' => 'Organization', 'name' => 'Bacha Stylo'],
                ],
            ] + ($reviewCount ? ['aggregateRating' => ['@type' => 'AggregateRating', 'ratingValue' => $reviewAverage, 'reviewCount' => $reviewCount]] : []),
            [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $settings->canonical('/')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => $product->category['name'], 'item' => $settings->canonical('/products?category='.rawurlencode($product->category['slug']))],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $product->name, 'item' => $url],
                ],
            ],
        ];

        return view('shop.products.show', [
            'product' => $product,
            'config' => $config,
            'reviews' => $reviews,
            'reviewCount' => $reviewCount,
            'reviewAverage' => $reviewAverage,
            'related' => $related,
            'title' => $title,
            'description' => $description,
            'image' => $image,
            'canonical' => $url,
            'jsonLd' => $jsonLd,
            'sizeGuide' => $grouped,
            'shipping' => $settings->shippingRule(),
            'policy' => $settings->deliveryPolicyParagraphs(),
        ]);
    }
}
