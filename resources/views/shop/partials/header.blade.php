@php
    $nav = [
        ['href' => route('shop.home'), 'label' => 'Home'],
        ['href' => route('shop.products'), 'label' => 'Shop', 'mega' => true],
        ['href' => route('shop.about'), 'label' => 'About'],
        ['href' => route('shop.blogs'), 'label' => 'Journal'],
        ['href' => route('shop.faq'), 'label' => 'FAQs'],
        ['href' => route('shop.contact'), 'label' => 'Contact'],
    ];
    $accountHref = $customer ? route('shop.account') : route('shop.login');
@endphp
<header
    x-data="{
        scrolled: false,
        shop: false,
        openT: null,
        closeT: null,
        enter() { clearTimeout(this.closeT); clearTimeout(this.openT); this.openT = setTimeout(() => this.shop = true, 140); },
        leave() { clearTimeout(this.openT); this.closeT = setTimeout(() => this.shop = false, 150); },
    }"
    x-init="scrolled = window.scrollY > 10"
    @scroll.window.passive="scrolled = window.scrollY > 10"
    @click.outside="shop = false"
    class="sticky top-0 z-40 transition-all duration-300 bg-white"
    :class="scrolled ? 'bg-white/95 backdrop-blur-md shadow-[0_1px_0_0_rgba(20,20,20,0.08)]' : 'bg-white'"
>
    <div class="container-shop flex items-center justify-between gap-4 h-16 xl:h-20 relative">
        <div class="flex items-center gap-3 flex-1 xl:flex-initial">
            <button type="button" aria-label="Open menu" class="xl:hidden text-brand-black p-2 -ml-2 hover:text-brand-red transition" @click="$store.ui.openMenu()">
                <x-lucide-menu class="h-5 w-5" stroke-width="2.5" />
            </button>

            <nav class="hidden xl:flex items-center gap-6 text-[13px] font-semibold uppercase tracking-[0.14em]">
                @foreach($nav as $n)
                    <div class="h-full flex items-center relative mega-menu-trigger"
                        @if(!empty($n['mega'])) @mouseenter="enter()" @mouseleave="leave()" @endif>
                        <a href="{{ $n['href'] }}"
                           class="relative transition-colors py-2 uppercase tracking-[0.14em] text-brand-black hover:text-brand-red"
                           @if(!empty($n['mega'])) :class="shop ? 'text-brand-red' : ''" @endif>
                            <span class="relative inline-block">
                                {{ $n['label'] }}
                                <span class="absolute left-0 -bottom-0.5 h-[2px] bg-brand-red transition-all duration-300 w-0"
                                      @if(!empty($n['mega'])) :class="shop ? 'w-full' : 'w-0'" @endif></span>
                            </span>
                        </a>
                        @if(!empty($n['mega']))
                            @include('shop.partials.mega-menu')
                        @endif
                    </div>
                @endforeach
            </nav>
        </div>

        {{-- Centered logo --}}
        <div class="absolute left-1/2 -translate-x-1/2 flex items-center">
            <x-shop.brand-mark size="default" :logo-url="$settings->logoUrl()" priority />
        </div>

        <div class="flex items-center gap-1 flex-1 xl:flex-initial justify-end">
            <div class="hidden lg:flex items-center mr-1.5 border-r border-ink-10 pr-2.5">
                <x-shop.social-links tone="onLight" :bordered="false" size="sm" />
            </div>
            <button type="button" @click="$store.ui.openSearch()" aria-label="Search"
                    class="inline-flex h-10 w-10 items-center justify-center text-brand-black hover:text-brand-red transition-colors">
                <x-lucide-search class="h-5 w-5" stroke-width="2.2" />
            </button>
            <a href="{{ $accountHref }}" aria-label="{{ $customer ? 'Account' : 'Sign in' }}"
               class="hidden sm:inline-flex h-10 w-10 items-center justify-center text-brand-black hover:text-brand-red transition-colors">
                <x-lucide-user class="h-5 w-5" stroke-width="2.2" />
            </a>
            <button type="button" aria-label="Open cart" @click="$store.cart.open()"
                    class="relative inline-flex h-10 w-10 items-center justify-center text-brand-black hover:text-brand-red transition-colors group">
                <x-lucide-shopping-bag class="h-5 w-5 group-hover:scale-110 transition-transform" stroke-width="2.2" />
                <span x-cloak x-show="$store.cart.count > 0" x-text="$store.cart.count"
                      class="absolute top-0.5 right-0.5 h-5 min-w-5 px-1 bg-brand-red text-[10px] font-bold text-white flex items-center justify-center rounded-full shadow-red-glow animate-pulse-red"></span>
            </button>
        </div>
    </div>
</header>
