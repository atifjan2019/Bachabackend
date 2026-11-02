<div x-cloak x-show="$store.cart.isOpen" class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="Your cart">
    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" x-show="$store.cart.isOpen" x-transition.opacity.duration.200ms @click="$store.cart.close()"></div>
    <div class="fixed right-0 top-0 h-full w-[min(440px,100%)] bg-cream border-l border-border shadow-card-hover p-0 flex flex-col"
         x-show="$store.cart.isOpen"
         x-transition:enter="transition-transform duration-300 ease-out" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition-transform duration-200 ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
        <button type="button" aria-label="Close" @click="$store.cart.close()" class="absolute right-4 top-4 rounded-sm text-muted hover:text-brand-black z-10">
            <x-lucide-x class="h-5 w-5" />
        </button>

        <div class="p-5 border-b border-border">
            <h2 class="font-display text-xl text-brand-black">Your Cart</h2>
            <p class="text-xs text-muted" x-text="`${$store.cart.lines.length} ${$store.cart.lines.length === 1 ? 'item' : 'items'}`"></p>
        </div>

        <div class="flex-1 overflow-y-auto px-5">
            <template x-if="$store.cart.lines.length === 0">
                <div @click="$store.cart.close()">
                    <x-shop.empty-state icon="shopping-bag" title="Your cart is waiting"
                        description="Kuch pyara sa choose karein. Your little one's next favourite outfit is only a click away."
                        :cta-href="route('shop.products')" cta-label="Continue Shopping" />
                </div>
            </template>
            <div class="pt-2" x-show="$store.cart.lines.length > 0">
                <template x-for="line in $store.cart.lines" :key="line.id">
                    <x-shop.cart-line />
                </template>
            </div>
        </div>

        <div class="border-t border-border p-5 bg-cream" x-show="$store.cart.lines.length > 0">
            <x-shop.cart-summary />
            <div class="mt-4 space-y-2">
                <x-shop.button href="{{ route('shop.checkout') }}" class="w-full" x-on:click="$store.cart.close()">Proceed to Checkout</x-shop.button>
                <x-shop.whatsapp-order-button x-on:click="$store.cart.close()" />
                <x-shop.button href="{{ route('shop.cart') }}" variant="outline" class="w-full" x-on:click="$store.cart.close()">View Full Cart</x-shop.button>
            </div>
        </div>
    </div>
</div>
