{{-- "Order via WhatsApp": the whole cart as a pre-filled message. --}}
<a x-cloak x-show="$store.cart.lines.length > 0" :href="$store.cart.whatsappHref()" target="_blank" rel="noopener noreferrer"
   {{ $attributes->class(['inline-flex w-full items-center justify-center gap-2 rounded-md bg-[#0f7a60] px-6 py-3 text-sm font-bold text-white transition-colors hover:bg-[#0b5f4b]']) }}>
    <x-shop.social-icon name="whatsapp" class="h-5 w-5" />
    Order via WhatsApp
</a>
