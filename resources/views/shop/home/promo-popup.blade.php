@if($promo['enabled'])
@php
    $showVideo = $promo['mediaType'] === 'video' && $promo['videoUrl'];
    $isDirectVideo = $showVideo && ! $promo['embedUrl'];
    $showImage = ! $showVideo && $promo['image'];
    $title = $promo['title'] ?: "Don't miss out";
@endphp
<div x-data="promoPopup()" x-show="open" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
     role="dialog" aria-modal="true" aria-label="{{ $promo['title'] ?: 'Promotion' }}">
    <button type="button" aria-label="Skip promotion" @click="close()" class="absolute inset-0 h-full w-full cursor-default bg-brand-black/80 backdrop-blur-sm animate-fade-up"></button>

    <div class="relative z-10 w-full max-w-lg overflow-hidden bg-brand-black text-white shadow-[0_40px_80px_-20px_rgba(0,0,0,0.7)] animate-fade-up">
        <div class="absolute top-0 left-0 right-0 z-20 h-[3px] bg-gradient-to-r from-brand-red via-brand-red/50 to-transparent"></div>

        <button type="button" @click="close()" aria-label="Skip promotion"
                class="absolute right-3 top-3 z-30 flex h-9 w-9 items-center justify-center rounded-full border border-white/25 bg-brand-black/50 text-white backdrop-blur-md transition-all duration-300 hover:border-brand-red hover:bg-brand-red">
            <x-lucide-x class="h-5 w-5" stroke-width="2.5" />
        </button>

        <div class="relative w-full overflow-hidden bg-brand-black-soft">
            @if($showVideo && $promo['embedUrl'])
                <div class="relative aspect-video w-full">
                    <template x-if="open">
                        <iframe src="{{ $promo['embedUrl'] }}" title="{{ $promo['title'] ?: 'Promotional video' }}"
                                allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen
                                class="absolute inset-0 h-full w-full border-0"></iframe>
                    </template>
                </div>
            @elseif($isDirectVideo)
                <video src="{{ $promo['videoUrl'] }}" @if($promo['image']) poster="{{ $promo['image'] }}" @endif autoplay controls playsinline class="max-h-[60vh] w-full object-contain"></video>
            @elseif($showImage)
                @if($promo['link'])
                    <a href="{{ $promo['link'] }}" target="_blank" rel="noopener noreferrer" class="block">
                        <img src="{{ $promo['image'] }}" alt="{{ $promo['title'] ?: 'Promotion' }}" class="max-h-[60vh] w-full object-contain">
                    </a>
                @else
                    <img src="{{ $promo['image'] }}" alt="{{ $promo['title'] ?: 'Promotion' }}" class="max-h-[60vh] w-full object-contain">
                @endif
            @else
                <div class="relative flex aspect-video w-full items-center justify-center bg-gradient-to-br from-brand-black via-brand-black-soft to-[#2a1116]">
                    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_30%_20%,rgba(232,29,37,0.25)_0%,transparent_60%)]"></div>
                    <x-lucide-play class="relative h-14 w-14 text-brand-red" stroke-width="1.5" />
                </div>
            @endif
        </div>

        <div class="p-6 sm:p-7">
            <div class="mb-3 flex items-center gap-3">
                <span class="h-[2px] w-8 bg-brand-red"></span>
                <span class="text-[10px] font-bold uppercase tracking-[0.28em] text-brand-red">Special Offer</span>
            </div>
            <h2 class="font-display text-2xl font-bold leading-tight sm:text-3xl">{{ $title }}</h2>
            @if($promo['subtitle'])
                <p class="mt-3 text-sm leading-relaxed text-white/70 sm:text-base">{{ $promo['subtitle'] }}</p>
            @endif
            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                @if($promo['link'])
                    <a href="{{ $promo['link'] }}" target="_blank" rel="noopener noreferrer" @click="close()"
                       class="group inline-flex items-center justify-center gap-3 bg-brand-red px-6 py-4 text-[12px] font-bold uppercase tracking-[0.18em] text-white transition-all duration-500 hover:bg-white hover:text-brand-black sm:text-[13px]">
                        <x-lucide-play class="h-4 w-4 fill-current" stroke-width="0" />
                        <span>{{ $promo['buttonText'] }}</span>
                        <x-lucide-arrow-up-right class="h-4 w-4 transition-transform group-hover:rotate-45" stroke-width="2.5" />
                    </a>
                @endif
                <button type="button" @click="close()" class="text-[11px] font-semibold uppercase tracking-[0.24em] text-white/60 underline-offset-4 transition-colors hover:text-white hover:underline sm:px-2">Skip</button>
            </div>
        </div>
    </div>
</div>
@endif
