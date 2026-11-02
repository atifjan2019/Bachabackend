@props(['variant' => 'primary', 'size' => 'md', 'href' => null, 'type' => 'button'])
@php
    $variants = [
        'primary' => 'bg-brand-red text-white hover:bg-[#c8161d]',
        'outline' => 'border border-brand-black text-brand-black hover:bg-brand-black hover:text-white',
        'ghost' => 'text-brand-black hover:bg-border/60',
        'subtle' => 'bg-ivory border border-border text-brand-black hover:bg-border/40',
        'gold' => 'bg-gold text-white hover:bg-[#a17708]',
    ];
    $sizes = ['sm' => 'h-9 px-3 text-xs', 'md' => 'h-11 px-5', 'lg' => 'h-12 px-6 text-base', 'icon' => 'h-10 w-10'];
    $classes = 'inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-red disabled:opacity-50 disabled:cursor-not-allowed '
        .($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']);
@endphp
@if($href)
    <a href="{{ $href }}" {{ $attributes->class([$classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class([$classes]) }}>{{ $slot }}</button>
@endif
