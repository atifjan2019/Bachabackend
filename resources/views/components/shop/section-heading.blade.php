@props(['eyebrow' => null, 'title', 'subtitle' => null, 'align' => 'left', 'variant' => 'light'])
@php $isDark = $variant === 'dark'; @endphp
<div {{ $attributes->class([$align === 'center' ? 'text-center max-w-2xl mx-auto' : 'max-w-3xl']) }}>
    @if($eyebrow)
        <div class="flex items-center gap-3 mb-5 {{ $align === 'center' ? 'justify-center' : '' }}">
            <span class="h-[2px] w-8 bg-brand-red"></span>
            <p class="text-[11px] uppercase tracking-[0.28em] font-bold text-brand-red">{{ $eyebrow }}</p>
            <span class="h-[2px] w-8 bg-brand-red"></span>
        </div>
    @endif
    <h2 class="font-display font-bold tracking-tightest leading-[1.02] text-4xl sm:text-5xl lg:text-6xl {{ $isDark ? 'text-white' : 'text-brand-black' }}">{{ $title }}</h2>
    @if($subtitle)
        <p class="mt-5 text-base lg:text-lg leading-relaxed max-w-xl {{ $align === 'center' ? 'mx-auto' : '' }} {{ $isDark ? 'text-white/70' : 'text-ink-70' }}">{{ $subtitle }}</p>
    @endif
</div>
