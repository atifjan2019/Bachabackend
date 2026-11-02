@extends('shop.layouts.app')
@section('title', 'Your Cart')
@section('description', 'Review the pieces in your Bacha Stylo cart before checkout.')
@section('robots', 'noindex, follow')

@section('content')
<div class="container-shop py-8 lg:py-12">
    <div class="text-center mb-8">
        <span aria-hidden="true" class="mb-4 block"></span>
        <h1 class="font-display text-3xl sm:text-4xl">Your Cart</h1>
    </div>

    <template x-if="$store.cart.lines.length === 0">
        <x-shop.empty-state icon="shopping-bag" title="Your cart is waiting"
            description="Kuch pyara sa choose karein. Your little one's next favourite outfit is only a click away."
            :cta-href="route('shop.products')" cta-label="Continue Shopping" />
    </template>

    <div x-cloak x-show="$store.cart.lines.length > 0" class="grid lg:grid-cols-[1fr_minmax(300px,360px)] gap-6 lg:gap-8">
        <div class="bg-ivory border border-border rounded-lg p-5">
            <template x-for="line in $store.cart.lines" :key="line.id">
                <x-shop.cart-line />
            </template>
        </div>
        <aside class="bg-ivory border border-border rounded-lg p-5 h-fit lg:sticky lg:top-24">
            <h3 class="font-display text-lg mb-4">Summary</h3>
            <x-shop.cart-summary :include-shipping="true" />
            <div class="mt-5 space-y-2">
                <x-shop.button href="{{ route('shop.checkout') }}" class="w-full">Proceed to Checkout</x-shop.button>
                <x-shop.whatsapp-order-button />
                <x-shop.button href="{{ route('shop.products') }}" variant="outline" class="w-full">Continue Shopping</x-shop.button>
            </div>
            <x-shop.delivery-policy class="mt-5 rounded-lg" />
        </aside>
    </div>

    <noscript>
        <p class="mt-6 text-center text-sm text-muted">Your cart is saved in this browser and needs JavaScript to display. You can also <a href="{{ $settings->whatsapp()['href'] }}" class="text-brand-red underline">order on WhatsApp</a>.</p>
    </noscript>
</div>
@endsection
