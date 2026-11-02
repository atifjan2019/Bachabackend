@extends('shop.layouts.app')
@php
    /** @var \App\Support\Storefront\ProductQuery $q */
    $pieces = $total === 1 ? 'piece' : 'pieces';
    $chipClass = 'inline-flex items-center gap-1 rounded-full border border-ink-10 bg-surface-soft px-3 py-1 text-xs text-brand-black hover:border-ink-30 transition';
    $activeSizes = $q->activeSizes();
    $filterCount = $q->activeFilterCount();
@endphp
@section('title', $title)
@section('description', $description)
@section('canonical', $canonical)
@if($noindex)
    @section('robots', 'noindex, follow')
@endif
@if($category && $category->image)
    @section('og_image', $category->image)
@endif
@if($breadcrumbs)
    @push('head')
        <script type="application/ld+json">{!! json_encode($breadcrumbs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endpush
@endif

@section('content')
<div class="flex flex-col" x-data="productFilters()">
    {{-- Hero --}}
    <x-shop.page-hero
        :eyebrow="$category ? 'Shop by category' : 'Shop'"
        :title="$category?->name ?? 'All Products'"
        :subtitle="$category ? $total.' '.$pieces.' curated for '.mb_strtolower($category->name) : $total.' '.$pieces.' stitched with care'"
        :image="$category?->image ?: null"
        variant="dark"
        align="center" />

    {{-- Toolbar --}}
    <div class="sticky top-14 z-30 bg-white border-b border-ink-10">
        <div class="container-shop py-3 space-y-3">
            <div class="flex items-center gap-3">
                <button type="button" class="lg:hidden inline-flex items-center gap-2 text-sm text-brand-black hover:text-ink-70 transition shrink-0" x-on:click="mobileOpen = true">
                    <x-lucide-sliders-horizontal class="h-4 w-4" />
                    <span class="sr-only sm:not-sr-only">Filters</span>
                    @if($filterCount > 0)
                        <span class="h-5 min-w-5 px-1 rounded-full bg-brand-black text-white text-[10px] flex items-center justify-center">{{ $filterCount }}</span>
                    @endif
                </button>

                <form method="get" action="{{ route('shop.products') }}" class="relative flex-1 max-w-xl" role="search">
                    @foreach($q->hiddenParams(['search']) as [$k, $v])
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <x-lucide-search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-ink-30" />
                    <input type="search" name="search" value="{{ $q->search }}" placeholder="Search products…" aria-label="Search products"
                           class="w-full border border-ink-10 bg-white py-2.5 pl-9 pr-3 text-sm outline-none transition-colors focus:border-brand-black">
                </form>

                <form method="get" action="{{ route('shop.products') }}" class="shrink-0">
                    @foreach($q->hiddenParams(['sort']) as [$k, $v])
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <label class="sr-only" for="sort">Sort</label>
                    <div class="relative">
                        <select id="sort" name="sort" x-on:change="$el.form.submit()"
                                class="h-11 w-[150px] sm:w-[180px] appearance-none rounded-md border border-border bg-ivory pl-3 pr-9 text-sm text-brand-black focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-red/40">
                            @foreach(\App\Support\Storefront\ProductQuery::SORTS as $key => $label)
                                <option value="{{ $key }}" @selected($q->sort === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-lucide-chevron-down class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 opacity-60" />
                    </div>
                    <noscript><button type="submit" class="mt-1 text-xs underline">Sort</button></noscript>
                </form>
            </div>

            {{-- Collection tabs --}}
            <div class="flex gap-2 overflow-x-auto pb-0.5 no-scrollbar">
                @foreach(\App\Support\Storefront\ProductQuery::COLLECTIONS as $key => $label)
                    <a href="{{ $q->url(['tab' => $key === 'all' ? null : $key]) }}"
                       class="whitespace-nowrap border px-4 py-2 text-[11px] font-bold uppercase tracking-[0.16em] transition-colors {{ $q->collection === $key ? 'border-brand-black bg-brand-black text-white' : 'border-ink-10 bg-white text-ink-70 hover:border-brand-black' }}"
                       @if($q->collection === $key) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </div>

            {{-- Active filter chips (desktop) --}}
            @if($filterCount > 0)
                <div class="hidden lg:flex items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach($q->categories as $slug)
                            <a href="{{ $q->toggleCategoryUrl($slug) }}" class="{{ $chipClass }}">{{ $q->categoryName($slug) }} <x-lucide-x class="h-3 w-3" /></a>
                        @endforeach
                        @foreach($activeSizes as $sz)
                            <a href="{{ $q->toggleSizeUrl($sz) }}" class="{{ $chipClass }}">Size {{ $sz }} <x-lucide-x class="h-3 w-3" /></a>
                        @endforeach
                        @if($q->priceActive())
                            <a href="{{ $q->url(['min' => null, 'max' => null]) }}" class="{{ $chipClass }}">Rs. {{ number_format($q->min) }} – Rs. {{ number_format($q->max) }} <x-lucide-x class="h-3 w-3" /></a>
                        @endif
                        <a href="{{ $q->clearAllUrl() }}" class="text-xs text-ink-50 hover:text-brand-black underline transition">Clear all</a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Main content --}}
    <div id="product-grid" class="container-shop py-8 lg:py-12 scroll-mt-32">
        <div class="flex gap-6 lg:gap-10">
            <aside class="hidden lg:block w-56 xl:w-60 flex-shrink-0">
                <div class="sticky top-32 space-y-0">
                    @include('shop.products.partials.filter-form', ['formId' => 'filters-desktop', 'layout' => 'sidebar'])
                </div>
            </aside>

            <div class="flex-1 min-w-0">
                @if(count($products) === 0)
                    <x-shop.empty-state title="Nothing matches yet" description="Try clearing a filter or two. New pieces are added regularly." cta-label="Clear filters" :cta-href="route('shop.products')" />
                @else
                    <x-shop.product-grid :products="$products" />
                    @if($results->lastPage() > 1)
                        <nav class="mt-12 flex items-center justify-center gap-3" aria-label="Pagination">
                            @php $prevDisabled = $results->currentPage() <= 1; $nextDisabled = $results->currentPage() >= $results->lastPage(); @endphp
                            <a @if(!$prevDisabled) href="{{ $q->pageUrl($results->currentPage() - 1) }}#product-grid" @else aria-disabled="true" @endif aria-label="Previous page"
                               class="h-10 w-10 rounded-full border border-ink-10 flex items-center justify-center text-brand-black hover:bg-surface-soft transition {{ $prevDisabled ? 'opacity-30 cursor-not-allowed pointer-events-none' : '' }}">
                                <x-lucide-chevron-left class="h-4 w-4" />
                            </a>
                            <span class="text-sm text-ink-50">Page {{ $results->currentPage() }} of {{ $results->lastPage() }}</span>
                            <a @if(!$nextDisabled) href="{{ $q->pageUrl($results->currentPage() + 1) }}#product-grid" @else aria-disabled="true" @endif aria-label="Next page"
                               class="h-10 w-10 rounded-full border border-ink-10 flex items-center justify-center text-brand-black hover:bg-surface-soft transition {{ $nextDisabled ? 'opacity-30 cursor-not-allowed pointer-events-none' : '' }}">
                                <x-lucide-chevron-right class="h-4 w-4" />
                            </a>
                        </nav>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Mobile filter sheet --}}
    <div x-cloak x-show="mobileOpen" class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="Filters">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" x-show="mobileOpen" x-transition.opacity.duration.200ms x-on:click="mobileOpen = false"></div>
        <div class="fixed left-0 bottom-0 right-0 max-h-[85vh] rounded-t-lg bg-cream border border-border shadow-card-hover p-6 overflow-y-auto"
             x-show="mobileOpen"
             x-transition:enter="transition-transform duration-300 ease-out" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
             x-transition:leave="transition-transform duration-200 ease-in" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full">
            <button type="button" aria-label="Close" x-on:click="mobileOpen = false" class="absolute right-4 top-4 rounded-sm text-muted hover:text-brand-black"><x-lucide-x class="h-5 w-5" /></button>
            <h2 class="font-display text-xl text-brand-black">Filters</h2>
            <div class="mt-4">
                @include('shop.products.partials.filter-form', ['formId' => 'filters-mobile', 'layout' => 'sheet'])
            </div>
        </div>
    </div>
</div>
@endsection
