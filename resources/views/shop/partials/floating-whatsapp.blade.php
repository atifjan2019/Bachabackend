@php $href = $settings->whatsappHref('Hi! I need help finding the perfect product.'); @endphp
<div x-data="{ open: false, dismissed: false }" x-init="setTimeout(() => { if (!dismissed) open = true }, 2500)"
     class="pointer-events-none fixed bottom-20 right-4 z-50 flex flex-col items-end gap-3 lg:bottom-6 lg:right-6">
    <div class="relative max-w-[250px] rounded-2xl rounded-br-sm border border-ink-10 bg-white px-4 py-3.5 pr-9 shadow-[0_12px_40px_-12px_rgba(20,20,20,0.3)] transition-all duration-300"
         :class="open ? 'pointer-events-auto translate-y-0 scale-100 opacity-100' : 'pointer-events-none translate-y-2 scale-95 opacity-0'">
        <button type="button" @click="open = false; dismissed = true" aria-label="Dismiss message" class="absolute right-2.5 top-2.5 text-ink-30 transition-colors hover:text-brand-black">
            <x-lucide-x class="h-4 w-4" />
        </button>
        <p class="text-[13px] leading-relaxed text-ink-70">Need help finding the perfect product? Chat with our team on WhatsApp.</p>
        <a href="{{ $href }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-block text-[11px] font-bold uppercase tracking-[0.14em] text-[#25D366]">Chat now →</a>
    </div>

    <a href="{{ $href }}" target="_blank" rel="noopener noreferrer" aria-label="Chat with us on WhatsApp" @mouseenter="if (!dismissed) open = true"
       class="pointer-events-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-[0_8px_30px_-6px_rgba(0,0,0,0.35)] transition-colors duration-300 hover:bg-[#1faa55]">
        <x-shop.social-icon name="whatsapp" class="h-7 w-7" />
    </a>
</div>
