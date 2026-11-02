@props(['product', 'label' => null])
@php
    /** @var \App\Support\Storefront\StorefrontProduct $product */
    $onSale = $product->onSale();
    $image = $product->images[0] ?? null;
@endphp
<a href="{{ $product->url() }}" class="group relative block">
    <div class="relative aspect-[4/5] bg-surface-sunken overflow-hidden">
        <x-shop.img :src="$image['url'] ?? null" :alt="$image['alt'] ?? $product->name" :label="$product->name" fill
            sizes="(max-width: 640px) 50vw, (max-width: 1024px) 33vw, 25vw"
            class="object-cover transition-transform duration-700 ease-out group-hover:scale-[1.06]" />

        <div class="absolute inset-0 bg-gradient-to-t from-brand-black/80 via-brand-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

        <div class="absolute left-3 top-3 flex flex-col items-start gap-1.5">
            @if($label)
                <span class="inline-flex items-center gap-1.5 bg-brand-black/80 px-2.5 py-1.5 text-[9px] font-bold uppercase tracking-[0.18em] text-white shadow-sm backdrop-blur-sm">
                    <span class="h-1 w-1 rounded-full bg-brand-red"></span>
                    {{ $label }}
                </span>
            @endif
            @if($onSale)
                <span class="tag-red shadow-red-glow">−{{ $product->discountPercent() }}%</span>
            @endif
            @if(! $product->in_stock)
                <span class="tag-dark">Sold out</span>
            @endif
        </div>

        <div class="absolute right-3 top-3 h-10 w-10 bg-brand-red text-white flex items-center justify-center opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-500 shadow-red-glow">
            <x-lucide-arrow-up-right class="h-4 w-4" stroke-width="2.5" />
        </div>

        <div class="absolute left-0 right-0 bottom-0 translate-y-full group-hover:translate-y-0 transition-transform duration-500 ease-out">
            <div class="bg-brand-red text-white text-[11px] font-bold uppercase tracking-[0.22em] py-3 text-center">View Details →</div>
        </div>
    </div>

    <div class="pt-4 pb-2 flex items-start justify-between gap-3">
        <div class="flex-1 min-w-0">
            <p class="text-[10px] uppercase tracking-[0.22em] text-brand-red font-bold mb-1.5">{{ $product->category['name'] }}</p>
            <h3 class="text-[15px] font-semibold text-brand-black line-clamp-1 group-hover:text-brand-red transition-colors">{{ $product->name }}</h3>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-[15px] font-bold {{ $onSale ? 'text-brand-red' : 'text-brand-black' }}">{{ pkr($product->displayPrice()) }}</span>
                @if($onSale)
                    <span class="text-xs text-ink-30 line-through">{{ pkr($product->price) }}</span>
                @endif
            </div>
        </div>
    </div>
</a>
