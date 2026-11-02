{{-- OrderSummaryCard: the cart lines (Alpine) + totals. --}}
<aside {{ $attributes->class(['bg-white border border-ink-10 p-4 sm:p-6']) }}>
    <h3 class="font-display text-base lg:text-xl mb-4 lg:mb-6 uppercase tracking-wider">Order Summary</h3>
    <div class="space-y-4 max-h-64 overflow-y-auto pr-1 no-scrollbar">
        <template x-for="l in $store.cart.lines" :key="l.id">
            <div class="flex gap-3">
                <div class="relative h-16 w-14 flex-shrink-0 overflow-hidden rounded-md border border-border bg-cream">
                    <img :src="l.image" :alt="l.name" :data-fallback-label="l.name" data-bsf-img data-fallback-class="absolute inset-0 h-full w-full"
                         onerror="bsfImgError(this)" loading="lazy" decoding="async" sizes="56px" class="absolute inset-0 h-full w-full object-cover">
                    <span class="absolute top-0 right-0 h-5 min-w-5 px-1 rounded-bl-md bg-brand-black text-white text-[10px] flex items-center justify-center" x-text="l.quantity"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-brand-black line-clamp-1" x-text="l.name"></p>
                    <p class="text-xs text-muted" x-text="`Size ${l.size}` + (l.color && l.color.trim().toLowerCase() !== 'default' ? ` · ${l.color}` : '')"></p>
                </div>
                <p class="text-sm font-medium" x-text="$pkr(l.unit_price * l.quantity)"></p>
            </div>
        </template>
    </div>
    <div class="h-px bg-border my-4"></div>
    <x-shop.cart-summary :include-shipping="true" />
</aside>
