@extends('shop.layouts.app')
@section('title', 'Order placed')
@section('description', 'Your Bacha Stylo order has been received.')
@section('robots', 'noindex, nofollow')

@php
    /** @var \App\Models\Order $order */
    $items = is_array($order->items) ? $order->items : [];
    $receipt = $order->payment_receipt;
    $isPdf = $receipt && preg_match('/\.pdf($|\?)/i', $receipt);
    $address = trim((string) $order->shipping_address);
    $notesPos = strpos($address, "\nNotes:");
    $addressLine = $notesPos !== false ? substr($address, 0, $notesPos) : $address;
@endphp

@section('content')
<div class="container-shop py-14 lg:py-20" x-data="orderSuccess(@js($order->reference))">
    <div class="max-w-xl mx-auto text-center">
        <div class="mx-auto h-14 w-14 rounded-full bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600">
            <x-lucide-circle-check class="h-8 w-8" />
        </div>
        <h1 class="font-display text-3xl sm:text-4xl text-brand-black mt-4">Order placed, shukria!</h1>
        <p class="mt-3 text-muted">
            Your order <span class="font-medium text-brand-black">{{ $order->reference }}</span> has been received. Our team will call to confirm within the next few hours.
        </p>

        <div class="mt-10 space-y-6 text-left max-w-2xl mx-auto">
            <div class="bg-white border border-ink-10 p-6 lg:p-8 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-display uppercase tracking-widest text-[11px] font-bold text-ink-30">Order Details</h3>
                    <x-shop.badge tone="gold" class="uppercase tracking-widest text-[9px] font-bold px-3 py-1">{{ strtolower($order->status) }}</x-shop.badge>
                </div>

                <div class="space-y-5 mb-8">
                    @foreach($items as $item)
                        @php $code = $item['variant_sku'] ?? $item['sku'] ?? null; $color = $item['color'] ?? null; @endphp
                        <div class="flex justify-between gap-4">
                            @if(! empty($item['image']))
                                <div class="relative h-16 w-14 flex-shrink-0 overflow-hidden rounded-md border border-ink-10 bg-cream">
                                    <x-shop.img :src="$item['image']" :alt="$item['name']" :label="$item['name']" fill sizes="56px" class="object-cover" />
                                </div>
                            @endif
                            <div class="flex-1">
                                <p class="text-sm lg:text-base font-bold text-brand-black">{{ $item['name'] }}</p>
                                <p class="text-[10px] lg:text-[11px] text-ink-30 uppercase tracking-widest mt-0.5">
                                    Qty {{ $item['quantity'] }}
                                    @if(! empty($item['size'])) &middot; Size {{ $item['size'] }} @endif
                                    @if($color && strtolower($color) !== 'default') &middot; {{ $color }} @endif
                                    @if($code) &middot; Code {{ $code }} @endif
                                </p>
                            </div>
                            <p class="text-sm lg:text-base font-bold text-brand-black">{{ pkr(($item['price'] ?? 0) * ($item['quantity'] ?? 1)) }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-ink-5 pt-6 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-ink-50 uppercase tracking-widest text-[10px] font-bold">Subtotal</span>
                        <span class="font-medium text-brand-black">{{ pkr($order->subtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-ink-50 uppercase tracking-widest text-[10px] font-bold">Shipping</span>
                        <span class="font-medium text-brand-black">{{ (float) $order->shipping_fee === 0.0 ? 'Free' : pkr($order->shipping_fee) }}</span>
                    </div>
                    <div class="flex justify-between items-end pt-4 border-t border-ink-10">
                        <span class="font-black text-brand-black uppercase tracking-widest text-[12px]">Total Amount</span>
                        <span class="font-display text-2xl lg:text-3xl font-black leading-none">{{ pkr($order->total_amount) }}</span>
                    </div>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div class="bg-white border border-ink-10 p-6">
                    <h4 class="text-[10px] uppercase tracking-widest font-bold text-ink-30 mb-3">Delivery Address</h4>
                    <p class="text-sm font-bold text-brand-black mb-1">{{ $order->customer_name }}</p>
                    <p class="text-xs text-ink-50 leading-relaxed">
                        {{ $addressLine }}<br>
                        {{ $order->city }}@if($order->country), {{ $order->country }}@endif
                    </p>
                </div>
                <div class="bg-white border border-ink-10 p-6">
                    <h4 class="text-[10px] uppercase tracking-widest font-bold text-ink-30 mb-3">Payment Info</h4>
                    <p class="text-sm font-bold text-brand-black uppercase tracking-widest mb-1">{{ $order->payment_method ?: 'Cash on Delivery' }}</p>
                    <p class="text-[11px] text-ink-50 italic leading-relaxed">Confirmation call expected within 2-3 hours.</p>
                </div>
            </div>

            @if($receipt)
                <div class="bg-white border border-ink-10 p-6">
                    <h4 class="text-[10px] uppercase tracking-widest font-bold text-ink-30 mb-3">Payment Receipt</h4>
                    @if($isPdf)
                        <a href="{{ $receipt }}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-brand-red underline underline-offset-4">View uploaded receipt (PDF)</a>
                    @else
                        <a href="{{ $receipt }}" target="_blank" rel="noopener noreferrer" class="block">
                            <img src="{{ $receipt }}" alt="Payment receipt" loading="lazy" decoding="async" class="max-h-80 w-auto rounded-md border border-ink-10">
                        </a>
                    @endif
                    <p class="mt-3 text-[11px] text-ink-50 italic leading-relaxed">The payment receipt you submitted with this order. Our team will verify it shortly.</p>
                </div>
            @endif
        </div>

        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
            <x-shop.button href="{{ route('shop.products') }}">Continue Shopping</x-shop.button>
            <x-shop.button href="{{ route('shop.home') }}" variant="outline">Back to Home</x-shop.button>
        </div>

        <div class="mt-12 border-t border-ink-10 pt-8">
            <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.22em] text-brand-red">Stay connected</p>
            <x-shop.social-links tone="onLight" size="md" class="justify-center" />
        </div>
    </div>
</div>
@endsection
