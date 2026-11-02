<div x-cloak x-show="$store.ui.searchOpen" x-transition.opacity.duration.200ms
     class="fixed inset-0 z-[70] flex items-start justify-center bg-brand-black/70 backdrop-blur-sm"
     role="dialog" aria-modal="true" aria-label="Search products" @click.self="$store.ui.closeSearch()">
    <div class="mt-24 w-full max-w-2xl px-4 sm:mt-32">
        <form action="{{ route('shop.products') }}" method="get" class="relative" role="search">
            <x-lucide-search class="pointer-events-none absolute left-5 top-1/2 h-5 w-5 -translate-y-1/2 text-ink-30" stroke-width="2" />
            <input id="site-search-input" type="search" name="search" placeholder="Search for products…" aria-label="Search for products" autocomplete="off"
                   class="w-full border border-white/10 bg-white py-5 pl-14 pr-14 text-base text-brand-black shadow-deep outline-none placeholder:text-ink-40">
            <button type="button" @click="$store.ui.closeSearch()" aria-label="Close search"
                    class="absolute right-4 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center text-ink-40 transition-colors hover:text-brand-black">
                <x-lucide-x class="h-5 w-5" stroke-width="2" />
            </button>
        </form>
        <p class="mt-4 text-center text-[11px] font-semibold uppercase tracking-[0.22em] text-white/60">Press Enter to search</p>
    </div>
</div>
