@php
    /** @var \Illuminate\Support\Collection<int,\App\Models\Category> $categories */
    $featured = $categories->first(fn ($c) => $c->image) ?? $categories->first();
    $primary = $categories->slice(0, 6);
    $extra = $categories->slice(6);
@endphp
@if($categories->isNotEmpty())
<div class="pointer-events-none fixed inset-x-0 top-[calc(var(--header-h,5rem)+2px)] z-50 flex justify-center px-4 pt-4 -mt-4 transition-all duration-200 ease-out mega-menu-content"
     :class="shop ? 'opacity-100 visible translate-y-0' : 'opacity-0 invisible translate-y-1'"
     x-cloak>
    <div class="pointer-events-auto w-full max-w-[1120px] overflow-hidden border border-ink-10 bg-white shadow-deep">
        <div class="h-[2px] bg-gradient-to-r from-transparent via-brand-red to-transparent"></div>

        <div class="grid grid-cols-1 md:grid-cols-[1fr_260px] lg:grid-cols-[1fr_300px]">
            {{-- Left: categories --}}
            <div class="p-6 lg:p-8">
                <div class="mb-6 flex items-center justify-between border-b border-ink-10 pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="h-[2px] w-6 bg-brand-red"></span>
                            <p class="text-[10px] font-bold uppercase tracking-[0.28em] text-brand-red">Bacha Stylo Edits</p>
                        </div>
                        <h3 class="mt-2 font-display text-2xl font-bold text-brand-black tracking-tightest">Shop by Category</h3>
                    </div>
                    <a href="{{ route('shop.products') }}" @click="shop = false"
                       class="group/all inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-[0.2em] text-brand-black hover:text-brand-red transition-colors shrink-0">
                        View All
                        <x-lucide-arrow-up-right class="h-3.5 w-3.5 transition-transform group-hover/all:rotate-45" stroke-width="2.5" />
                    </a>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-3 gap-2">
                    @foreach($primary as $parent)
                        <div class="group/cat border border-transparent p-3 transition-colors hover:border-ink-10 hover:bg-ink-5">
                            <a href="{{ route('shop.products', ['category' => $parent->slug]) }}" @click="shop = false" class="flex items-start justify-between gap-2">
                                <span class="min-w-0">
                                    <span class="block text-[15px] font-bold text-brand-black transition-colors group-hover/cat:text-brand-red truncate">{{ $parent->name }}</span>
                                    @if(($parent->products_count ?? 0) > 0)
                                        <span class="mt-0.5 block text-[11px] uppercase tracking-wider text-ink-50">{{ $parent->products_count }} pieces</span>
                                    @endif
                                </span>
                                <x-lucide-arrow-right class="mt-1 h-3.5 w-3.5 shrink-0 text-ink-30 transition-all group-hover/cat:translate-x-0.5 group-hover/cat:text-brand-red" stroke-width="2.5" />
                            </a>
                            @if($parent->children && $parent->children->isNotEmpty())
                                <ul class="mt-2.5 space-y-1.5 border-l border-brand-red/30 pl-3">
                                    @foreach($parent->children->take(4) as $child)
                                        <li>
                                            <a href="{{ route('shop.products', ['category' => $child->slug]) }}" @click="shop = false"
                                               class="block text-[13px] text-ink-70 transition-colors hover:text-brand-red truncate">{{ $child->name }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($extra->isNotEmpty())
                    <div class="mt-5 flex flex-wrap gap-2 border-t border-ink-10 pt-4">
                        @foreach($extra as $category)
                            <a href="{{ route('shop.products', ['category' => $category->slug]) }}" @click="shop = false"
                               class="border border-ink-10 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wider text-ink-70 transition-colors hover:border-brand-red hover:bg-brand-red hover:text-white">{{ $category->name }}</a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Right: featured --}}
            <a href="{{ route('shop.products', ['category' => $featured->slug]) }}" @click="shop = false"
               class="group/feat relative hidden md:flex flex-col justify-between overflow-hidden bg-brand-black p-6 text-white min-h-[340px]">
                @if($featured->image)
                    <x-shop.img :src="$featured->image" :alt="$featured->image_alt ?: $featured->name" fill fallback="none" sizes="300px"
                        class="absolute inset-0 h-full w-full object-cover opacity-40 transition-all duration-700 group-hover/feat:opacity-60 group-hover/feat:scale-105" />
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-brand-black via-brand-black/60 to-brand-black/30"></div>
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(232,29,37,0.35)_0%,transparent_55%)]"></div>

                <div class="relative z-10 flex justify-between items-start">
                    <span class="inline-flex items-center gap-1.5 bg-brand-red px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.22em] text-white">
                        <x-lucide-sparkles class="h-3 w-3" stroke-width="2.5" />
                        Featured
                    </span>
                </div>
                <div class="relative z-10">
                    <p class="text-[10px] font-bold uppercase tracking-[0.28em] text-brand-red mb-2">Curated for the season</p>
                    <h3 class="font-display text-3xl font-bold leading-[1.02] tracking-tightest">{{ $featured->name }}</h3>
                    <span class="mt-5 inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.22em] text-white group-hover/feat:text-brand-red transition-colors">
                        Explore Now
                        <x-lucide-arrow-up-right class="h-4 w-4 transition-transform group-hover/feat:rotate-45" stroke-width="2.5" />
                    </span>
                </div>
            </a>
        </div>
    </div>
</div>
@endif
