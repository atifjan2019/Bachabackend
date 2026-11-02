@extends('shop.layouts.app')
@php /** @var \App\Support\Storefront\StorefrontProduct $product */ @endphp
@section('title', $title)
@section('description', $description)
@section('canonical', $canonical)
@section('og_type', 'product')
@if($image)
    @section('og_image', $image)
@endif
@push('head')
    @foreach($jsonLd as $ld)
        <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endforeach
    <meta property="product:price:amount" content="{{ number_format($product->displayPrice(), 0, '.', '') }}">
    <meta property="product:price:currency" content="PKR">
@endpush

@section('content')
<div class="container-shop py-6 lg:py-12">
    <nav class="text-sm text-muted mb-6" aria-label="Breadcrumb">
        <a href="{{ route('shop.home') }}" class="hover:text-brand-red">Home</a>
        <span class="mx-2">/</span>
        <a href="{{ route('shop.products', ['category' => $product->category['slug']]) }}" class="hover:text-brand-red">{{ $product->category['name'] }}</a>
        <span class="mx-2">/</span>
        <span class="text-brand-black">{{ $product->name }}</span>
    </nav>

    <div x-data="productDetail(@js($config))">
        <div class="grid lg:grid-cols-2 gap-6 md:gap-8 lg:gap-12">
            @include('shop.products.partials.gallery')
            <div>
                @include('shop.products.partials.actions')
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 md:gap-8 lg:gap-12">
        <div class="hidden lg:block"></div>
        <div>
            <div class="mt-8 lg:mt-0">
                <x-shop.accordion title="Full Description" :open="true">
                    <div class="prose prose-sm max-w-none">{!! $product->description !!}</div>
                </x-shop.accordion>
                <x-shop.accordion title="Size Guide">
                    @include('shop.products.partials.size-guide', ['grouped' => $sizeGuide, 'sizes' => $config['size_options']])
                </x-shop.accordion>
                <x-shop.accordion title="Care Instructions">
                    Gentle machine wash in cold water. Turn inside out before washing embroidered pieces.
                    Do not bleach. Dry in shade. Warm iron on reverse.
                </x-shop.accordion>
                <x-shop.accordion title="Delivery &amp; Returns">
                    <div class="space-y-2">
                        <p>
                            Delivery in 2 to 5 working days across Pakistan. Delivery charge {{ pkr($shipping['fee']) }}{{ $shipping['freeEnabled'] ? ', free on orders of '.pkr($shipping['threshold']).' or more.' : '.' }}
                        </p>
                        @foreach($policy as $p)
                            <p>{{ $p }}</p>
                        @endforeach
                        <p>Size exchanges accepted within 7 days of delivery, item must be unused with tags intact.</p>
                    </div>
                </x-shop.accordion>
            </div>
        </div>
    </div>

    @include('shop.products.partials.reviews')

    @if($related->isNotEmpty())
        <section class="mt-16 lg:mt-24">
            <div class="text-center mb-8">
                <h2 class="font-display text-2xl sm:text-3xl">You may also love</h2>
            </div>
            <x-shop.product-grid :products="$related" />
        </section>
    @endif
</div>
@endsection
