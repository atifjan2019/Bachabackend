@props(['tone' => 'neutral'])
@php
    $tones = [
        'neutral' => 'bg-cream text-brand-black border-border',
        'green' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'amber' => 'bg-amber-50 text-amber-700 border-amber-200',
        'gray' => 'bg-border/40 text-muted border-border',
        'red' => 'bg-[#fdecec] text-brand-red border-[#f4c7ca]',
        'gold' => 'bg-[#f7efd9] text-[#7a5a0c] border-[#ead9a4]',
    ];
@endphp
<span {{ $attributes->class(['inline-flex items-center gap-1 rounded-md border px-2 py-0.5 text-xs font-medium', $tones[$tone] ?? $tones['neutral']]) }}>{{ $slot }}</span>
