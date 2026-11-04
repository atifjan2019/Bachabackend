# Storefront (Blade + Alpine) — architecture and conventions

The customer-facing site that used to live in `Bachafrontend/` (Next.js) is
served by this Laravel app. Admin (`/admin`, Bootstrap via CDN) and the JSON
API (`/api/v1`) are unchanged. Everything below applies to the storefront only.

## Layout

| Piece | Where |
|---|---|
| Layout | `resources/views/shop/layouts/app.blade.php` |
| Header, footer, drawers, overlays, toasts | `resources/views/shop/partials/*.blade.php` |
| Shared components | `resources/views/components/shop/*.blade.php` → `<x-shop.name>` |
| Pages | `resources/views/shop/<area>/*.blade.php` |
| Controllers | `app/Http/Controllers/Shop/*` |
| Routes | `routes/web.php` (names `shop.*`) |
| Tailwind 4 theme + component classes | `resources/css/app.css` (+ `resources/css/pages/*.css`) |
| Alpine runtime | `resources/js/app.js`, `resources/js/shop/*.js` |
| Page Alpine components | `resources/js/shop/pages/*.js` (auto-imported by glob) |

A page extends the layout and fills sections:

```blade
@extends('shop.layouts.app')
@section('title', 'Shop')                 {{-- rendered as "Shop | Bacha Stylo"; omit on home --}}
@section('description', $description)
@section('canonical', $settings->canonical('/products'))   {{-- defaults to the request URI on the primary host --}}
@section('og_image', $url)  @section('og_type', 'product')  @section('robots', 'noindex, follow')
@push('head') <script type="application/ld+json">{!! json_encode($ld) !!}</script> @endpush
@section('content') ... @endsection
```

Every `shop.*` view and `components.shop.*` component receives (view composer):

- `$settings` — `App\Support\Storefront\SiteSettings` (also `site()` helper). Cached admin settings:
  `get('key')`, `enabled('cod_enabled')`, `businessName()`, `logoUrl()`, `shippingRule()`,
  `shippingFor($subtotal)`, `remainingForFreeShipping()`, `deliveryPolicyParagraphs()`,
  `whatsapp()` → `{number,digits,href}`, `whatsappHref($message)`, `socials()`, `siteOrigin()`,
  `canonical($path)`, `supportEmail()`, `supportPhone()`, `supportHours()`, `enabledPaymentMethods()`,
  `SiteSettings::PAYMENT_LABELS`.
- `$categories` — top-level `Category` collection with `->children` and `products_count` (subtree).
- `$customer` — signed-in `Customer` or null (session guard `customer`).

## Products

Never hand `Product` models to a view. Wrap them:

```php
use App\Support\Storefront\{StorefrontProduct, Catalog, Sizes};
$p = StorefrontProduct::make($product);      // or StorefrontProduct::collection($models)
$p->name, $p->slug, $p->sku, $p->price, $p->sale_price, $p->onSale(), $p->displayPrice(), $p->discountPercent(),
$p->images[]{id,url,alt}, $p->sizes[], $p->colors[], $p->showColors(), $p->hasSizes(), $p->variants[],
$p->in_stock, $p->category['slug'|'name'], $p->url(), $p->accordions[], $p->toArray() /* JSON for Alpine */
Catalog::cards()            // slim query builder for grids (select only card columns)
Catalog::featured(8), Catalog::bestSellers(8), Catalog::related($p, 4)   // cached presenters
Catalog::categoryTree(), Catalog::flatCategories(), Catalog::findCategory($slug), Catalog::effectivePriceSql()
Sizes::group($sizes) / Sizes::usesBands($sizes) / Sizes::BANDS   // Kids 14–26, Teens 28–32, Gen Z 34–42, Plus 44–50
```

Helpers: `pkr(12500)` → `Rs. 12,500`; `strip_html_excerpt($html, 160)`.

### Visibility and ordering

Categories and products carry `is_active` (default true) and a nullable `sort_order`, set from the admin
forms or inline on the admin lists (`PATCH admin/{categories,products}/{id}/quick`).

- `Product::storefront()` — active products not filed under a hidden category (`Category::hiddenSlugs()`:
  every inactive category plus its subtree). Uncategorised products stay visible. Use it on **every**
  shopper-facing product read; `Catalog::cards()`, the product page, sitemap, `/api/v1/products*` and
  `OrderService::assertPurchasable()` already do.
- `Category::active()` + `Category::ordered()` / `Product::ordered()` — numbered rows first (lower first,
  `NULL` last), then oldest first (categories) or newest first (products). `Catalog::categoryTree()` and the API apply
  both; price sorts on the listing ignore the manual order.
- Counts: `Category::subtreeProductCounts(storefront: true)` for anything a shopper sees.

### Images and caching

- Catalogue images are admin uploads of 1–2k px on the media bucket (`https://media.bachastylo.com`, whose
  CDN caches for only 4 h and whose API token can't change that). The storefront never shows them at full
  size: `ImageVariant::url($src, $width, 'webp')` / `::srcset($src, $widths)` point at
  `/img/{width}/{key}.webp`, which `Shop\ImageController` renders once (Imagick/GD, never upscaled) into
  `public/img/…` (git-ignored) and the web server then serves as a static file with a one-year cache header.
  Failures redirect to the original. Only the media bucket and `public/images` are accepted as sources;
  widths are limited to `ImageVariant::WIDTHS`.
- `<x-shop.img>` emits the srcset itself whenever it knows the slot size (`sizes`, `width` or `widths`);
  Alpine-driven `<img>`s use `bsf.srcset(url, widths)` (same rules, from `window.BSF.media`).
- New uploads go through `media_store($file, $dir)`: images are re-encoded by `UploadNormalizer` (upright, ≤2000px,
  WebP, so the bucket only holds files the resizer can read), stored with `Cache-Control: max-age=31536000`;
  a refused write raises a validation error (`upload`) and `StorageHealth` shows a banner in the admin.
- `php artisan media:warm` pre-generates every width (nightly 03:30); `media:cache-headers` rewrites bucket
  objects' Cache-Control; originals are cached under `storage/app/media-originals`.
- Guest storefront pages are cached whole for 5 min (`StorefrontCacheHeaders`, header `X-Storefront-Cache`);
  the CSRF token is swapped per visitor. HTML is sent `no-cache`, and `FlushStorefrontCacheAfterAdminWrite`
  clears the whole application cache after every admin POST/PUT/PATCH/DELETE, so changes show at once. Keep anything
  per-visitor out of server-rendered HTML (the cart lives in the browser; signed-in customers bypass it).
- Fonts load via `preload as=style`; GA4 and the Meta pixel load on first interaction or 5 s after load.

### SEO fields

- Products: `meta_title`, `meta_description`, `image_alt`, `gallery_alts` (JSON list aligned with `gallery`);
  `StorefrontProduct::$images[*]['alt']` already resolves the fallback (name / "name image N").
- Categories: `meta_title`, `meta_description`, `image_alt`; `Catalog::categoryTree()` carries them.
- Settings: `meta_title`/`meta_description` are the homepage only; `products_meta_title`/`_description`
  are the /products listing. `SiteSettings::pageTitle()` strips a trailing "| Bacha Stylo" because the
  layout appends the business name. Inline `@section('title', $x)` values are HTML-escaped by Blade; the
  layout decodes them before escaping once (never pass pre-escaped strings as component attributes).
- Admin product slugs must be unique (validation); blank slugs auto-suffix `-2`, `-3`. Renaming a slug stores
  the old one in `previous_slug`, and `/products/{old}` 301s to the new URL (not for the shared-slug
  products of the SEO handoff, which were split by SQL and never had their own URL).

## Components (`<x-shop.*>`)

- `img` — SafeImage: `<x-shop.img :src="$url" :alt="$alt" :label="$name" fill sizes="…" class="object-cover" />`
  props `fill`, `priority`, `fallback="none"`, `fallback-src`, `width`, `height`. Failed images become a
  branded monogram panel (`bsfImgError` in `<head>`). For Alpine-bound images write a raw
  `<img :src … data-bsf-img onerror="bsfImgError(this)" loading="lazy" decoding="async">`.
- `button` (`variant` primary|outline|ghost|subtle, `size` sm|md|lg|icon, `href` → `<a>`, `type`),
  `badge` (`tone` neutral|green|amber|gray|red|gold), `brand-mark`, `social-links`, `social-icon`,
  `page-hero`, `section-heading`, `empty-state`, `product-card`, `product-grid`, `field-error name=`,
  `input`, `textarea`, `label`, `delivery-policy`, `cart-summary :include-shipping`, `cart-line`,
  `quantity-stepper`, `whatsapp-order-button`, `accordion title=`.
- CSS classes from globals.css are available: `container-shop`, `eyebrow`, `btn-primary`, `btn-outline`,
  `btn-ghost-red`, `pill`, `tag-red`, `tag-dark`, `link-underline`, `display-xl|lg|md`, `prose`,
  `prose-policy`, `noise-overlay`, `stripes-red`, `no-scrollbar`, `input-shop`, `textarea-shop`, `label-shop`.
- Icons: `<x-lucide-arrow-up-right class="h-4 w-4" stroke-width="2.5" />` for every lucide-react icon.
- **Inside a `<x-shop.*>` tag** write Alpine as `x-on:click="…"` and `::class="…"` (`:` alone is PHP).

## Alpine

Globals from the layout: `window.BSF = {shipping:{fee,threshold,freeEnabled}, whatsapp:{digits,number},
origin, apiBase, csrf, customer|null}` and `window.bsf` helpers: `pkr`, `shippingFor`,
`remainingForFreeShipping`, `whatsappOrderHref`, `api(path,{method,body})` (same-origin `/api/v1`, throws
`{status, errors}`), `captureAbandonedCart`, `debounce`, `copyText`, `stockStatus`, `PK_PHONE`.
Magic: `$pkr(n)`.

Stores:

- `$store.cart` — `lines`, `count`, `subtotal`, `shipping`, `total`, `remainingForFreeShipping`,
  `isOpen`, `open()`, `close()`, `add(line, {open, toast})`, `remove(id)`, `setQty(id, n)`,
  `increment(id)`, `decrement(id)`, `clear()`, `whatsappHref()`, `toOrderItems()`.
  Line shape (localStorage `bsf_cart`, same as the Next app):
  `{id, product_id, variant_id, sku, variant_sku, slug, name, image, size, color, color_hex, unit_price, quantity, max_stock}`
  with `id = \`${product_id}-${variant_id}-${size}-${color}\``.
- `$store.ui` — `toast({title, description, tone: 'success'|'error'|'default'})`, `openMenu()`,
  `openSearch()`, `closeAll()`.

Server flash → toast: `redirect()->with('toast', ['title' => 'Welcome back', 'tone' => 'success'])`.

Page components: `resources/js/shop/pages/<name>.js` with `document.addEventListener('alpine:init', () =>
Alpine.data('productDetail', (cfg) => ({ ... })))`, used as `x-data="productDetail(@js($p->toArray()))"`.

## Rules

- Keep the admin and the API untouched. All user content escaped; `{!! !!}` only for product
  `description` and blog `content`.
- Links and forms work without JavaScript except cart/checkout interactivity.
- Public GET pages are wrapped in the `shop.cache` middleware (see routes). Cache catalogue queries with
  `Cache::remember` (the admin flushes the cache on save).
- Build assets with `npm run build` (output `public/build`, gitignored).

## Deploy (Cloudways)

```
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan storage:link
```

Set `APP_URL` and `FRONTEND_URL` to `https://www.bachastylo.com` (FRONTEND_URL also feeds CORS and
the password-reset links). Demo data: `php artisan db:seed --class=StorefrontDemoSeeder`.
