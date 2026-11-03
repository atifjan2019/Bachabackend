@php
    $toneClasses = [
        'green' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'amber' => 'bg-amber-50 text-amber-700 border-amber-200',
        'gray' => 'bg-border/40 text-muted border-border',
    ];
    $sizeBtn = 'min-w-12 h-10 px-3 rounded-md text-sm border transition';
    $sizeActive = 'border-brand-black bg-brand-black text-white';
    $sizeIdle = 'border-border bg-ivory text-brand-black hover:border-brand-black';
@endphp
<div>
    <p class="text-xs uppercase tracking-[0.18em] text-gold">{{ $product->category['name'] }}</p>
    <h1 class="mt-1 font-display text-3xl lg:text-4xl text-brand-black">{{ $product->name }}</h1>
    <div class="mt-3 flex items-baseline gap-3">
        <span class="font-display text-2xl text-brand-red" x-text="$pkr(displayPrice)">{{ pkr($product->displayPrice()) }}</span>
        <span class="text-sm text-muted line-through" x-show="onSale" @unless($product->onSale()) x-cloak @endunless x-text="$pkr(product.price)">{{ pkr($product->price) }}</span>
        <span class="inline-flex items-center gap-1 rounded-md border px-2 py-0.5 text-xs font-medium bg-[#fdecec] text-[#b3141a] border-[#f4c7ca]" x-show="onSale" @unless($product->onSale()) x-cloak @endunless
              x-text="`Save ${$pkr(product.price - product.sale_price)}`">Save {{ pkr($product->savings()) }}</span>
    </div>
    @if($config['description_excerpt'] !== '')
        <p class="mt-4 text-sm text-muted max-w-md">{{ $config['description_excerpt'] }}&hellip;</p>
    @endif
    <p class="mt-2 text-[11px] uppercase tracking-[0.14em] text-ink-50">Code: <span x-text="variant ? variant.sku : product.sku">{{ $product->variants[0]['sku'] ?? $product->sku }}</span></p>

    <div class="h-px bg-border my-6"></div>

    <div class="space-y-5">
        {{-- Size --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium">Size</span>
                <span class="text-xs text-muted" x-show="size" x-text="`Selected: ${size}`"></span>
            </div>
            @if($config['use_bands'])
                <div class="space-y-3">
                    <div>
                        <p class="mb-2 text-[11px] font-medium uppercase tracking-[0.14em] text-muted">Fit</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($config['bands'] as $band)
                                @php $lo = $band['sizes'][0]; $hi = end($band['sizes']); @endphp
                                <button type="button" x-on:click="pickBand(@js($band['key']))"
                                        class="h-9 px-3.5 rounded-md text-[13px] border transition"
                                        :class="bandKey === @js($band['key']) ? 'border-brand-black bg-brand-black text-white' : 'border-border bg-ivory text-brand-black hover:border-brand-black'">
                                    {{ $band['shortLabel'] }}
                                    <span class="ml-1.5 text-[11px] opacity-60">{{ $lo === $hi ? $lo : $lo.'–'.$hi }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <p class="mb-2 text-[11px] font-medium uppercase tracking-[0.14em] text-muted">Size</p>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="s in currentBandSizes" :key="s">
                                <button type="button" x-on:click="size = s" x-text="s" class="{{ $sizeBtn }}" :class="s === size ? '{{ $sizeActive }}' : '{{ $sizeIdle }}'"></button>
                            </template>
                        </div>
                    </div>
                </div>
            @else
                <div class="flex flex-wrap gap-2">
                    @foreach($config['size_options'] as $s)
                        <button type="button" x-on:click="size = @js($s)" class="{{ $sizeBtn }}" :class="size === @js($s) ? '{{ $sizeActive }}' : '{{ $sizeIdle }}'">{{ $s }}</button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Colour (only real admin colours; the placeholder is never shown) --}}
        @if($product->showColors())
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium">Colour</span>
                    <span class="text-xs text-muted" x-show="variant && variant.note" x-text="variant ? variant.note : ''"></span>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    @foreach($config['color_options'] as $c)
                        <button type="button" x-on:click="color = @js($c['color'])"
                                aria-label="{{ $c['color'] }}{{ $c['in_stock'] ? '' : ' (sold out)' }}" title="{{ $c['color'] }}{{ $c['in_stock'] ? '' : ' (sold out)' }}"
                                :aria-pressed="color === @js($c['color'])"
                                class="relative h-9 w-9 rounded-full border transition {{ $c['in_stock'] ? '' : 'opacity-40' }}"
                                :class="color === @js($c['color']) ? 'border-brand-black ring-2 ring-offset-2 ring-brand-black/70' : 'border-border'"
                                style="background-color: {{ $c['color_hex'] }}">
                            @unless($c['in_stock'])
                                <span aria-hidden="true" class="absolute inset-0 m-auto h-px w-full rotate-45 bg-brand-black/70"></span>
                            @endunless
                        </button>
                    @endforeach
                    <span class="text-xs text-muted" x-show="color" x-text="color"></span>
                </div>
            </div>
        @endif

        {{-- Quantity --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium">Quantity</span>
                <span class="inline-flex items-center gap-1 rounded-md border px-2 py-0.5 text-xs font-medium"
                      :class="{ '{{ $toneClasses['green'] }}': status.tone === 'green', '{{ $toneClasses['amber'] }}': status.tone === 'amber', '{{ $toneClasses['gray'] }}': status.tone === 'gray' }"
                      x-text="status.label"></span>
            </div>
            <x-shop.quantity-stepper value="qty" min="1" max="Math.max(1, stock)" decrement="qty = Math.max(1, qty - 1)" increment="qty = Math.min(Math.max(1, stock), qty + 1)" />
        </div>
    </div>

    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
        <x-shop.button size="lg" class="w-full sm:flex-1" x-on:click="add()" ::disabled="!variant || stock <= 0">
            <x-lucide-shopping-bag class="h-4 w-4" />
            <span x-text="stock <= 0 ? 'Out of Stock' : 'Add to Cart'">Add to Cart</span>
        </x-shop.button>
        <a :href="whatsappHref" href="{{ $settings->whatsappHref("Hi! I'd like to order:\n".$product->name) }}" target="_blank" rel="noopener noreferrer"
           class="inline-flex w-full items-center justify-center gap-2 bg-[#0f7a60] px-6 text-sm font-bold text-white transition-colors hover:bg-[#0b5f4b] sm:w-auto">
            <x-shop.social-icon name="whatsapp" class="h-5 w-5" />
            Order on WhatsApp
        </a>
    </div>

    {{-- Delivery & trust badges --}}
    <div class="mt-7 grid grid-cols-2 gap-4 border-t border-border pt-6 sm:grid-cols-4">
        @foreach([['truck', 'Nationwide Delivery'], ['banknote', 'Cash on Delivery'], ['refresh-cw', 'Easy Returns'], ['shield-check', 'Secure Checkout']] as [$icon, $label])
            <div class="flex flex-col items-center gap-2 text-center">
                <x-dynamic-component :component="'lucide-'.$icon" class="h-5 w-5 text-brand-red" stroke-width="1.5" />
                <span class="text-[11px] font-medium leading-tight text-ink-70">{{ $label }}</span>
            </div>
        @endforeach
    </div>

    {{-- Share / follow --}}
    <div class="mt-6 flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-border pt-6">
        <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-ink-50">Follow us</span>
        <x-shop.social-links tone="onLight" :bordered="false" size="sm" />
    </div>
</div>
