@props(['eyebrow', 'title', 'subtitle' => null, 'variant' => 'light', 'align' => 'left', 'image' => null, 'size' => 'default'])
@php
    $isDark = $variant === 'dark' || ! empty($image);
    $heightClass = $size === 'lg' ? 'min-h-[420px] lg:min-h-[500px]' : 'min-h-[220px] lg:min-h-[260px]';
@endphp
<section class="relative {{ $heightClass }} overflow-hidden {{ $isDark ? 'bg-brand-black' : 'bg-surface-soft' }}">
    @if($image)
        <x-shop.img :src="$image" alt="" fill priority sizes="100vw" fallback="none" class="object-cover opacity-50" />
        <div class="absolute inset-0 bg-gradient-to-br from-brand-black/90 via-brand-black/70 to-brand-red/30"></div>
    @endif

    @if($isDark && ! $image)
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(232,29,37,0.18)_0%,transparent_50%)]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_80%_80%,rgba(232,29,37,0.12)_0%,transparent_50%)]"></div>
        <div class="absolute top-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-brand-red to-transparent opacity-60"></div>
        <div class="absolute bottom-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-brand-red to-transparent opacity-40"></div>
    @endif

    @if($size === 'lg')
        <div class="absolute -right-8 -bottom-12 pointer-events-none select-none opacity-[0.06]">
            <span class="font-display text-[14rem] lg:text-[22rem] leading-none font-black {{ $isDark ? 'text-white' : 'text-brand-black' }}">✦</span>
        </div>
    @endif

    <div class="relative h-full flex flex-col justify-center container-shop py-12 {{ $align === 'center' ? 'text-center items-center' : '' }}">
        <div class="flex items-center gap-3 mb-4 {{ $align === 'center' ? 'justify-center' : '' }}">
            <span class="h-[2px] w-10 bg-brand-red"></span>
            <span class="text-[11px] uppercase tracking-[0.28em] font-bold text-brand-red">{{ $eyebrow }}</span>
        </div>
        <h1 class="font-display tracking-tightest leading-[0.98] font-bold {{ $size === 'lg' ? 'text-5xl sm:text-6xl lg:text-7xl' : 'text-3xl lg:text-5xl' }} {{ $isDark ? 'text-white' : 'text-brand-black' }}">{{ $title }}</h1>
        @if($subtitle)
            <p class="mt-5 text-base lg:text-lg max-w-xl leading-relaxed {{ $align === 'center' ? 'mx-auto' : '' }} {{ $isDark ? 'text-white/70' : 'text-ink-70' }}">{{ $subtitle }}</p>
        @endif
        {{ $slot }}
    </div>
</section>
