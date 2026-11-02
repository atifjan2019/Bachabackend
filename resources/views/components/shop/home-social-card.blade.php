@props(['name', 'icon', 'href' => null])
@php $tag = $href ? 'a' : 'div'; @endphp
<{{ $tag }} @if($href) href="{{ $href }}" target="_blank" rel="noopener noreferrer" @endif>
    <div class="group relative flex aspect-[4/3] flex-col items-center justify-center gap-4 overflow-hidden bg-gradient-to-br from-brand-black via-brand-black-soft to-[#241015] p-8 text-center text-white transition-transform duration-500 hover:-translate-y-1">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(232,29,37,0.18)_0%,transparent_60%)] opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
        <div class="relative flex h-16 w-16 items-center justify-center rounded-full border border-white/20 text-brand-red transition-colors duration-500 group-hover:bg-brand-red group-hover:text-white">
            <x-shop.social-icon :name="$icon" class="h-7 w-7" />
        </div>
        <div class="relative">
            <h3 class="font-display text-xl font-bold">{{ $name }}</h3>
        </div>
        <span class="relative mt-1 inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-[0.18em] text-white/80 transition-colors group-hover:text-brand-red">
            Follow
            <x-lucide-arrow-up-right class="h-3.5 w-3.5" stroke-width="2.5" />
        </span>
    </div>
</{{ $tag }}>
