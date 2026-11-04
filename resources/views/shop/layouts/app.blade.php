@php
    /** @var \App\Support\Storefront\SiteSettings $settings */
    // Inline @section('x', $value) escapes its value, and the tags below
    // escape again — so decode here or "&" becomes "&amp;amp;" in the title.
    $yield = fn (string $name) => html_entity_decode(trim($__env->yieldContent($name)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $pageTitle = $yield('title');
    $fullTitle = $pageTitle !== '' ? $pageTitle.' | '.$settings->businessName() : $settings->metaTitle();
    $metaDescription = $yield('description') ?: $settings->metaDescription();
    $canonical = $yield('canonical') ?: $settings->canonical(request()->getRequestUri());
    $ogImage = $yield('og_image') ?: $settings->get('og_image');
    $ogType = $yield('og_type') ?: 'website';
    $robots = $yield('robots') ?: $settings->robots();
    $whatsapp = $settings->whatsapp();
    $bsf = [
        'shipping' => $settings->shippingRule(),
        'whatsapp' => ['digits' => $whatsapp['digits'], 'number' => $whatsapp['number']],
        'origin' => request()->getSchemeAndHttpHost(),
        'apiBase' => url('/api/v1'),
        'media' => \App\Support\Storefront\ImageVariant::mediaOrigin(),
        'imgWidths' => \App\Support\Storefront\ImageVariant::WIDTHS,
        'imgVersion' => \App\Support\Storefront\ImageVariant::VERSION,
        'csrf' => csrf_token(),
        'customer' => $customer ? ['name' => $customer->name, 'email' => $customer->email, 'phone' => $customer->phone] : null,
    ];
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    @if($settings->get('meta_keywords'))
        <meta name="keywords" content="{{ $settings->get('meta_keywords') }}">
    @endif
    <link rel="canonical" href="{{ $canonical }}">
    @if($robots)
        <meta name="robots" content="{{ $robots }}">
    @endif
    @if($settings->googleSiteVerification())
        <meta name="google-site-verification" content="{{ $settings->googleSiteVerification() }}">
    @endif
    <meta name="simpledcver" content="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJkb21haW4iOiJiYWNoYXN0eWxvLmNvbSIsImV4cCI6MTc4Mjk1MDQwMH0.ygBW6WTYwg99HAxQFiM5JF5iYuDk5pjyPuxKg_gv80s">

    {{-- Open Graph / Twitter --}}
    <meta property="og:site_name" content="{{ $settings->businessName() }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif
    <meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $fullTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">

    @php
        $favicon = $settings->faviconUrl();
        $faviconSmall = \App\Support\Storefront\ImageVariant::url($favicon, 96, 'png') ?? $favicon;
        $faviconTouch = \App\Support\Storefront\ImageVariant::url($favicon, 240, 'png') ?? $favicon;
    @endphp
    <link rel="icon" href="{{ $faviconSmall }}">
    <link rel="shortcut icon" href="{{ $faviconSmall }}">
    <link rel="apple-touch-icon" href="{{ $faviconTouch }}">

    <link rel="preconnect" href="https://media.bachastylo.com">
    {{-- Self-hosted Montserrat (see app.css): fetched before the stylesheet asks for it. --}}
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/montserrat-latin.woff2') }}" crossorigin>

    @include('shop.partials.img-fallback')
    <script>window.BSF = @json($bsf);</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('shop.partials.tracking')
    @stack('head')
</head>
<body class="min-h-screen flex flex-col bg-surface text-brand-black antialiased" x-data>
    @include('shop.partials.announcement')
    @include('shop.partials.header')

    <main class="flex-1">
        @yield('content')
    </main>

    @include('shop.partials.footer')
    @include('shop.partials.mobile-nav')
    @include('shop.partials.floating-whatsapp')
    @include('shop.partials.cart-drawer')
    @include('shop.partials.search-overlay')
    @include('shop.partials.mobile-menu')
    @include('shop.partials.toasts')

    @stack('scripts')
</body>
</html>
