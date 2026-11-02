@props(['includeShipping' => false])
{{-- Alpine-driven: totals come from $store.cart so drawer, cart page and checkout always agree. --}}
<div {{ $attributes->class(['space-y-2 text-sm']) }}>
    <div class="flex items-center justify-between">
        <span class="text-muted">Subtotal</span>
        <span class="font-medium" x-text="$pkr($store.cart.subtotal)"></span>
    </div>
    <div class="flex items-center justify-between">
        <span class="text-muted">Shipping</span>
        @if($includeShipping)
            <span class="font-medium" x-text="$store.cart.shipping === 0 ? 'Free' : $pkr($store.cart.shipping)"></span>
        @else
            <span class="font-medium">Calculated at checkout</span>
        @endif
    </div>
    <div class="h-px bg-border my-2"></div>
    <div class="flex items-center justify-between">
        <span class="font-display text-base">Total</span>
        <span class="font-display text-lg" x-text="$pkr({{ $includeShipping ? '$store.cart.total' : '$store.cart.subtotal' }})"></span>
    </div>
    <p class="text-xs text-muted pt-1" x-show="$store.cart.remainingForFreeShipping !== null"
       x-text="`Add ${$pkr($store.cart.remainingForFreeShipping)} more for free shipping.`"></p>
</div>
