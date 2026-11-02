@extends('shop.layouts.app')
@section('title', $order ? 'Order '.$order->ref : 'Order not found')
@section('robots', 'noindex, nofollow')

@section('content')
@if(! $order)
    <div class="container-shop py-20 text-center">
        <h1 class="font-display text-3xl">Order not found</h1>
        <x-shop.button href="{{ route('shop.account.orders') }}" class="mt-4">Back to orders</x-shop.button>
    </div>
@else
    @php
        $steps = [
            ['key' => 'pending', 'label' => 'Pending', 'icon' => 'check'],
            ['key' => 'paid', 'label' => 'Paid', 'icon' => 'check'],
            ['key' => 'processing', 'label' => 'Processing', 'icon' => 'check'],
            ['key' => 'shipped', 'label' => 'Shipped', 'icon' => 'truck'],
            ['key' => 'delivered', 'label' => 'Delivered', 'icon' => 'house'],
        ];
        $status = strtolower(trim((string) $order->status));
        $cancelled = in_array($status, ['cancelled', 'canceled'], true);
        $active = max(0, array_search($status, array_column($steps, 'key'), true) ?: 0);
        $items = is_array($order->items) ? $order->items : [];
        $address = trim((string) $order->shipping_address);
        $notesPos = strpos($address, "\nNotes:");
        $addressLine = $notesPos !== false ? substr($address, 0, $notesPos) : $address;
        $notes = $notesPos !== false ? trim(substr($address, $notesPos + strlen("\nNotes:"))) : null;
    @endphp
    <div class="container-shop py-10 lg:py-14" x-data="reorder(@js($items), @js(route('shop.checkout')))">
        <div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-3xl sm:text-4xl">Order {{ $order->ref }}</h1>
                <p class="text-sm text-muted mt-1">Placed on {{ $order->created_at?->format('d M Y') }}</p>
            </div>
            <x-shop.button type="button" x-on:click="run()">Reorder items</x-shop.button>
        </div>

        @if(! $cancelled)
            <div class="bg-ivory border border-border rounded-lg p-5 lg:p-6 mb-6">
                <div class="flex items-center justify-between gap-2 overflow-x-auto no-scrollbar">
                    @foreach($steps as $i => $step)
                        @php $done = $i <= $active; $current = $i === $active; @endphp
                        <div class="flex-1 flex items-center">
                            <div class="flex flex-col items-center text-center flex-shrink-0 min-w-[70px]">
                                <div class="h-9 w-9 rounded-full flex items-center justify-center border {{ $done ? 'bg-brand-red border-brand-red text-white' : 'border-border text-muted' }} {{ $current ? 'ring-2 ring-brand-red/30' : '' }}">
                                    <x-dynamic-component :component="'lucide-'.$step['icon']" class="h-4 w-4" />
                                </div>
                                <span class="text-xs mt-2 {{ $done ? 'text-brand-black' : 'text-muted' }}">{{ $step['label'] }}</span>
                            </div>
                            @if($i < count($steps) - 1)
                                <div class="h-px flex-1 mx-1 {{ $i < $active ? 'bg-brand-red' : 'bg-border' }}"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-[#fdecec] border border-[#f4c7ca] text-brand-red rounded-lg p-4 mb-6 text-sm">This order was cancelled.</div>
        @endif

        <div class="grid lg:grid-cols-[1fr_340px] gap-6">
            <section class="bg-ivory border border-border rounded-lg p-5 lg:p-6">
                <h3 class="font-display text-lg mb-4">Items</h3>
                <div class="divide-y divide-border">
                    @foreach($items as $i)
                        @php $code = $i['variant_sku'] ?? $i['sku'] ?? null; $color = $i['color'] ?? null; @endphp
                        <div class="flex items-center justify-between py-3 gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                @if(! empty($i['image']))
                                    <div class="relative h-14 w-12 flex-shrink-0 overflow-hidden rounded-md border border-border bg-cream">
                                        <x-shop.img :src="$i['image']" :alt="$i['name']" :label="$i['name']" fill sizes="48px" class="object-cover" />
                                    </div>
                                @else
                                    <div class="h-12 w-12 rounded-full bg-cream border border-border flex items-center justify-center text-gold">
                                        <x-lucide-package class="h-5 w-5" />
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-medium text-sm text-brand-black truncate">{{ $i['name'] }}</p>
                                    @if($code)
                                        <p class="text-[10px] uppercase tracking-[0.14em] text-ink-50">Code {{ $code }}</p>
                                    @endif
                                    <p class="text-xs text-muted">
                                        @if(! empty($i['size']))<span>Size {{ $i['size'] }}</span><span class="mx-1">&middot;</span>@endif
                                        @if($color && strtolower(trim($color)) !== 'default')<span>{{ $color }}</span><span class="mx-1">&middot;</span>@endif
                                        <span>Qty {{ $i['quantity'] }}</span>
                                    </p>
                                </div>
                            </div>
                            <p class="text-sm font-medium">{{ pkr(($i['price'] ?? 0) * ($i['quantity'] ?? 1)) }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <aside class="space-y-6">
                <div class="bg-ivory border border-border rounded-lg p-5">
                    <h3 class="font-display text-lg mb-3">Summary</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between"><span class="text-muted">Subtotal</span><span class="font-medium">{{ pkr($order->subtotal) }}</span></div>
                        <div class="flex items-center justify-between"><span class="text-muted">Shipping</span><span class="font-medium">{{ (float) $order->shipping_fee === 0.0 ? 'Free' : pkr($order->shipping_fee) }}</span></div>
                        <div class="h-px bg-border my-2"></div>
                        <div class="flex items-center justify-between"><span class="text-muted">Total</span><span class="font-display text-lg">{{ pkr($order->total_amount) }}</span></div>
                    </div>
                </div>
                <div class="bg-ivory border border-border rounded-lg p-5">
                    <h3 class="font-display text-lg mb-3">Shipping to</h3>
                    <p class="text-sm text-brand-black">{{ $order->customer_name }}</p>
                    <p class="text-sm text-muted">{{ $order->customer_phone }}</p>
                    <p class="text-sm text-muted mt-2">{{ $addressLine }}</p>
                    <p class="text-sm text-muted">{{ $order->city }}@if($order->country), {{ $order->country }}@endif</p>
                    @if($notes)
                        <p class="text-xs text-muted mt-2 italic">Notes: {{ $notes }}</p>
                    @endif
                </div>
                <div class="bg-ivory border border-border rounded-lg p-5">
                    <h3 class="font-display text-lg mb-2">Payment</h3>
                    <x-shop.badge tone="gold">{{ $order->payment_method ?: 'Cash on Delivery' }}</x-shop.badge>
                </div>
            </aside>
        </div>
    </div>
@endif
@endsection
