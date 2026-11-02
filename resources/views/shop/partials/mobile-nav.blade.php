<nav aria-label="Primary" class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-ink-10">
    <ul class="grid grid-cols-4">
        <li>
            <a href="{{ route('shop.home') }}" class="flex flex-col items-center gap-1 py-2.5 text-[10px] text-brand-black">
                <x-lucide-house class="h-5 w-5" />
                <span>Home</span>
            </a>
        </li>
        <li>
            <a href="{{ route('shop.products') }}" class="flex flex-col items-center gap-1 py-2.5 text-[10px] text-brand-black">
                <x-lucide-search class="h-5 w-5" />
                <span>Shop</span>
            </a>
        </li>
        <li>
            <button type="button" @click="$store.cart.open()" class="relative w-full flex flex-col items-center gap-1 py-2.5 text-[10px] text-brand-black">
                <x-lucide-shopping-bag class="h-5 w-5" />
                <span>Cart</span>
                <span x-cloak x-show="$store.cart.count > 0" x-text="$store.cart.count"
                      class="absolute top-1 right-6 h-4 min-w-4 px-1 rounded-full bg-brand-black text-[10px] text-white flex items-center justify-center"></span>
            </button>
        </li>
        <li>
            <a href="{{ $customer ? route('shop.account') : route('shop.login') }}" class="flex flex-col items-center gap-1 py-2.5 text-[10px] text-brand-black">
                <x-lucide-user class="h-5 w-5" />
                <span>{{ $customer ? 'Account' : 'Sign in' }}</span>
            </a>
        </li>
    </ul>
    <div class="h-[env(safe-area-inset-bottom)]"></div>
</nav>
