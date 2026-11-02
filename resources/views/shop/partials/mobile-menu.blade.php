@php
    $items = [
        ['href' => route('shop.home'), 'label' => 'Home'],
        ['href' => route('shop.products'), 'label' => 'Shop'],
        ['href' => route('shop.about'), 'label' => 'About'],
        ['href' => route('shop.blogs'), 'label' => 'Journal'],
        ['href' => route('shop.faq'), 'label' => 'FAQs'],
        ['href' => route('shop.contact'), 'label' => 'Contact'],
    ];
@endphp
<div x-cloak x-show="$store.ui.menuOpen" class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="Menu">
    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" x-show="$store.ui.menuOpen" x-transition.opacity.duration.200ms @click="$store.ui.closeMenu()"></div>
    <div class="fixed right-0 top-0 h-full w-[min(440px,100%)] bg-white border-l border-border shadow-card-hover p-6 overflow-y-auto"
         x-show="$store.ui.menuOpen"
         x-transition:enter="transition-transform duration-300 ease-out" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition-transform duration-200 ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
        <button type="button" aria-label="Close" @click="$store.ui.closeMenu()" class="absolute right-4 top-4 rounded-sm text-muted hover:text-brand-black">
            <x-lucide-x class="h-5 w-5" />
        </button>
        <div class="flex flex-col gap-6 h-full">
            <div class="flex items-center pb-4 border-b border-ink-10">
                <x-shop.brand-mark :logo-url="$settings->logoUrl()" />
            </div>

            <nav class="flex flex-col">
                @foreach($items as $i => $n)
                    <a href="{{ $n['href'] }}" @click="$store.ui.closeMenu()"
                       class="group flex items-center justify-between py-4 border-b border-ink-10 text-brand-black font-semibold uppercase tracking-[0.14em] text-[15px] hover:text-brand-red transition-colors">
                        <span class="flex items-center gap-3">
                            <span class="text-[10px] text-brand-red font-bold">0{{ $i + 1 }}</span>
                            {{ $n['label'] }}
                        </span>
                        <x-lucide-arrow-up-right class="h-4 w-4 text-ink-30 group-hover:text-brand-red group-hover:rotate-45 transition-all" stroke-width="2.5" />
                    </a>
                @endforeach
            </nav>

            <div class="mt-auto flex flex-col gap-3">
                @if($customer)
                    <a href="{{ route('shop.account') }}" @click="$store.ui.closeMenu()" class="btn-primary w-full text-center">My Account</a>
                @else
                    <a href="{{ route('shop.login') }}" @click="$store.ui.closeMenu()" class="btn-primary w-full text-center">Sign in</a>
                    <a href="{{ route('shop.register') }}" @click="$store.ui.closeMenu()" class="btn-outline w-full text-center">Create account</a>
                @endif
                <div class="mt-4 border-t border-ink-10 pt-5">
                    <p class="mb-3 text-center text-[10px] font-bold uppercase tracking-[0.22em] text-brand-red">Follow us</p>
                    <x-shop.social-links tone="onLight" size="md" class="justify-center" />
                </div>
                <p class="text-[10px] uppercase tracking-[0.22em] text-ink-30 mt-3 text-center">&copy; {{ date('Y') }} · Bacha Stylo</p>
            </div>
        </div>
    </div>
</div>
