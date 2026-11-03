{{--
    One GET form holds every filter so the page works without JavaScript (Apply
    button). With Alpine, each change submits the form at once; a category change
    also clears the size selection, as the sizes on offer belong to the category.
    $formId prefixes input ids; $layout is 'sidebar' (desktop) or 'sheet' (mobile).
--}}
@php
    /** @var \App\Support\Storefront\ProductQuery $q */
    $sheet = ($layout ?? 'sidebar') === 'sheet';
    $headingClass = $sheet ? 'font-display text-base mb-3' : 'text-xs uppercase tracking-[0.18em] text-ink-50 font-medium mb-4';
    $sectionClass = $sheet ? '' : 'border-b border-ink-10 pb-6 mb-6';
    $activeSizes = $q->activeSizes();
@endphp
<form method="get" action="{{ route('shop.products') }}" id="{{ $formId }}" class="{{ $sheet ? 'space-y-6' : 'space-y-0' }}"
      x-data="filterForm()" x-on:change="autoSubmit($event)">
    @foreach($q->hiddenParams(['category', 'size', 'min', 'max']) as [$k, $v])
        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
    @endforeach

    {{-- Category --}}
    <div class="{{ $sectionClass }}">
        <{{ $sheet ? 'h3' : 'h2' }} class="{{ $headingClass }}">Category</{{ $sheet ? 'h3' : 'h2' }}>
        <div class="space-y-2">
            @foreach($categories as $c)
                <div class="space-y-2">
                    <label class="flex cursor-pointer items-center gap-3 text-sm text-brand-black">
                        <input type="checkbox" name="category[]" value="{{ $c->slug }}" data-filter="category" class="h-4 w-4 accent-brand-red" @checked(in_array($c->slug, $q->categories, true))>
                        <span class="flex-1">{{ $c->name }}</span>
                        @if(($c->products_count ?? 0) > 0)<span class="text-xs text-muted">{{ $c->products_count }}</span>@endif
                    </label>
                    @if($c->children && $c->children->isNotEmpty())
                        <div class="ml-[26px] space-y-2 border-l border-ink-10 pl-3">
                            @foreach($c->children as $child)
                                <label class="flex cursor-pointer items-center gap-3 text-[13px] text-ink-70">
                                    <input type="checkbox" name="category[]" value="{{ $child->slug }}" data-filter="category" class="h-4 w-4 accent-brand-red" @checked(in_array($child->slug, $q->categories, true))>
                                    <span class="flex-1">{{ $child->name }}</span>
                                    @if(($child->products_count ?? 0) > 0)<span class="text-xs text-muted">{{ $child->products_count }}</span>@endif
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Size (only the sizes carried by the products being browsed) --}}
    @if($sizeGroups)
        <div class="{{ $sectionClass }}">
            <{{ $sheet ? 'h3' : 'h2' }} class="{{ $headingClass }}">Size</{{ $sheet ? 'h3' : 'h2' }}>
            <div class="space-y-4">
                @foreach($sizeGroups as $group)
                    <div>
                        @if($group['label'])<p class="mb-2 text-[11px] uppercase tracking-wider text-ink-50">{{ $group['label'] }}</p>@endif
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($group['sizes'] as $size)
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="size[]" value="{{ $size }}" data-filter="size" class="peer sr-only" @checked(in_array($size, $activeSizes, true))>
                                    <span class="inline-flex h-8 min-w-9 items-center justify-center rounded-md border px-2 text-[13px] transition border-ink-10 bg-white text-brand-black hover:border-brand-black peer-checked:border-brand-black peer-checked:bg-brand-black peer-checked:text-white peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-brand-red">{{ $size }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Price range --}}
    <div>
        <{{ $sheet ? 'h3' : 'h2' }} class="{{ $headingClass }}">{{ $sheet ? 'Price Range' : 'Price range' }}</{{ $sheet ? 'h3' : 'h2' }}>
        <div class="space-y-3" x-data="priceRange({ min: {{ $q->min }}, max: {{ $q->max }}, floor: {{ \App\Support\Storefront\ProductQuery::PRICE_MIN }}, ceil: {{ \App\Support\Storefront\ProductQuery::PRICE_MAX }}, step: {{ \App\Support\Storefront\ProductQuery::PRICE_STEP }} })">
            <div class="price-range relative h-4 w-full select-none touch-none">
                <div class="absolute left-0 right-0 top-1/2 h-1.5 -translate-y-1/2 overflow-hidden rounded-full bg-border">
                    <div class="absolute h-full bg-brand-red" :style="`left:${pct(lo)}%; right:${100 - pct(hi)}%`"></div>
                </div>
                <input type="range" name="min" id="{{ $formId }}-min" aria-label="Minimum price" :min="floor" :max="ceil" :step="step" x-model.number="lo" x-on:input="clamp('lo')" x-on:change.stop="commit()"
                       class="price-thumb absolute inset-0 w-full appearance-none bg-transparent" :class="{ 'z-20': lo > ceil - step }">
                <input type="range" name="max" id="{{ $formId }}-max" aria-label="Maximum price" :min="floor" :max="ceil" :step="step" x-model.number="hi" x-on:input="clamp('hi')" x-on:change.stop="commit()"
                       class="price-thumb absolute inset-0 w-full appearance-none bg-transparent z-10">
            </div>
            <div class="flex items-center justify-between text-xs text-muted">
                <span x-text="$pkr(lo)">{{ pkr($q->min) }}</span>
                <span x-text="$pkr(hi)">{{ pkr($q->max) }}</span>
            </div>
        </div>
    </div>

    @if($sheet)
        <div class="mt-6 flex gap-2">
            <x-shop.button variant="outline" class="flex-1" :href="$q->clearAllUrl()">Clear</x-shop.button>
            <x-shop.button type="submit" class="flex-1">Show Results</x-shop.button>
        </div>
    @else
        {{-- Visible only without JavaScript: with Alpine every change submits itself. --}}
        <noscript>
            <div class="mt-6">
                <x-shop.button type="submit" class="w-full">Apply filters</x-shop.button>
            </div>
        </noscript>
    @endif
</form>
