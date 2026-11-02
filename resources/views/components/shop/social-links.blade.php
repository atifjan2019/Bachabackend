@props(['tone' => 'onLight', 'bordered' => true, 'size' => 'md', 'includeWhatsapp' => true])
@php
    $sizes = [
        'sm' => ['box' => 'h-9 w-9', 'icon' => 'h-[15px] w-[15px]', 'gap' => 'gap-2'],
        'md' => ['box' => 'h-10 w-10', 'icon' => 'h-[18px] w-[18px]', 'gap' => 'gap-2.5'],
        'lg' => ['box' => 'h-11 w-11', 'icon' => 'h-5 w-5', 'gap' => 'gap-3'],
    ];
    $s = $sizes[$size] ?? $sizes['md'];
    $tones = [
        'onDark' => $bordered
            ? 'border border-white/20 text-white/70 hover:border-brand-red hover:bg-brand-red hover:text-white'
            : 'text-white/70 hover:text-brand-red',
        'onLight' => $bordered
            ? 'border border-ink-10 text-ink-70 hover:border-brand-red hover:bg-brand-red hover:text-white'
            : 'text-ink-50 hover:text-brand-red',
    ];
    $accounts = $settings->socials($includeWhatsapp);
@endphp
<div {{ $attributes->class(['flex items-center', $s['gap']]) }}>
    @foreach($accounts as $key => $account)
        <a aria-label="{{ $account['label'] }}" title="{{ $account['label'] }}" href="{{ $account['href'] }}" target="_blank" rel="noopener noreferrer"
           class="group inline-flex shrink-0 items-center justify-center transition-all duration-300 {{ $s['box'] }} {{ $tones[$tone] ?? $tones['onLight'] }}">
            <x-shop.social-icon :name="$key" :class="$s['icon']" />
        </a>
    @endforeach
</div>
