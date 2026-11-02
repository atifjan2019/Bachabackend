@props(['icon', 'title', 'info', 'sub', 'href' => null, 'index'])
@php
    $wrapper = 'h-full border-ink-10 border-b sm:[&:nth-child(2n)]:border-l sm:[&:nth-child(n+3)]:border-t lg:[&:nth-child(2n)]:border-l-0 lg:[&:not(:first-child)]:border-l lg:border-b-0 sm:last:border-b-0';
    $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if($href) href="{{ $href }}" @if(str_starts_with($href, 'http')) target="_blank" @endif rel="noopener noreferrer" class="block {{ $wrapper }}" @else class="{{ $wrapper }}" @endif>
    <div class="group relative h-full p-6 sm:p-8 bg-white hover:bg-brand-black transition-all duration-500 overflow-hidden">
        <div class="absolute top-0 left-0 h-1 w-0 bg-brand-red group-hover:w-full transition-all duration-500"></div>
        <div class="relative z-10">
            <div class="flex items-start justify-between mb-5 sm:mb-6">
                <div class="h-12 w-12 sm:h-14 sm:w-14 border-2 border-brand-red text-brand-red group-hover:bg-brand-red group-hover:text-white flex items-center justify-center transition-colors">
                    <x-dynamic-component :component="'lucide-'.$icon" class="h-5 w-5" stroke-width="2" />
                </div>
                <span class="text-[10px] uppercase tracking-[0.22em] text-ink-30 font-bold group-hover:text-brand-red transition-colors">0{{ $index }}</span>
            </div>
            <p class="text-[10px] uppercase tracking-[0.28em] text-brand-red font-bold mb-2">{{ $title }}</p>
            <p class="text-sm sm:text-base font-bold text-brand-black group-hover:text-white transition-colors break-words">{{ $info }}</p>
            <p class="mt-1.5 text-xs text-ink-50 group-hover:text-white/60 transition-colors">{{ $sub }}</p>
            @if($href)
                <div class="mt-4 sm:mt-6 inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-[0.22em] text-brand-black group-hover:text-brand-red transition-colors">
                    Connect
                    <x-lucide-arrow-up-right class="h-3.5 w-3.5 transition-transform group-hover:rotate-45" stroke-width="2.5" />
                </div>
            @endif
        </div>
    </div>
</{{ $tag }}>
