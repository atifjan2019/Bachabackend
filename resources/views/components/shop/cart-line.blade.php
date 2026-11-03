@props(['compact' => false])
{{-- Must sit inside <template x-for="line in $store.cart.lines"> --}}
<div class="flex gap-3 py-3 border-b border-border last:border-0">
    <a :href="`{{ url('/products') }}/${line.slug}`" class="relative h-20 w-16 sm:h-24 sm:w-20 flex-shrink-0 overflow-hidden rounded-md border border-border bg-cream">
        <img :src="line.image" :srcset="bsf.srcset(line.image, [96, 160, 240])" sizes="96px" :alt="line.name" :data-fallback-label="line.name" data-bsf-img data-fallback-class="absolute inset-0 h-full w-full"
             onerror="bsfImgError(this)" loading="lazy" decoding="async" sizes="80px" class="absolute inset-0 h-full w-full object-cover">
    </a>
    <div class="flex-1 min-w-0">
        <a :href="`{{ url('/products') }}/${line.slug}`" class="font-display text-sm text-brand-black line-clamp-1" x-text="line.name"></a>
        <p class="text-xs text-muted mt-0.5">
            <span x-text="`Size ${line.size}`"></span>
            <template x-if="line.color && line.color.trim().toLowerCase() !== 'default'">
                <span>
                    <span class="mx-1">&middot;</span>
                    <span class="inline-flex items-center gap-1">
                        <span aria-hidden="true" class="inline-block h-2.5 w-2.5 rounded-full border border-border" :style="`background-color: ${line.color_hex}`"></span>
                        <span x-text="line.color"></span>
                    </span>
                </span>
            </template>
        </p>
        <div class="mt-2 flex items-center justify-between gap-3">
            <x-shop.quantity-stepper value="line.quantity" min="1" max="line.max_stock"
                decrement="$store.cart.decrement(line.id)" increment="$store.cart.increment(line.id)" />
            <div class="text-right">
                <p class="text-sm font-medium text-brand-black" x-text="$pkr(line.unit_price * line.quantity)"></p>
                @unless($compact)
                    <button type="button" @click="$store.cart.remove(line.id)" class="inline-flex items-center gap-1 text-xs text-muted hover:text-brand-red mt-1">
                        <x-lucide-trash-2 class="h-3.5 w-3.5" /> Remove
                    </button>
                @endunless
            </div>
        </div>
    </div>
</div>
