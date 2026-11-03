@props(['variant' => 'light', 'size' => 'default', 'logoUrl' => null, 'priority' => false])
@php
    $packaged = asset($variant === 'dark' ? 'images/BachaStylo White Logo for web.png' : 'images/BachaStylo Logo for web.png');
    $src = $logoUrl ?: $packaged;
    $sizes = ['default' => ['max-h-10', 160], 'lg' => ['max-h-[60px]', 240], 'xl' => ['max-h-20', 320]];
    [$maxH, $px] = $sizes[$size] ?? $sizes['default'];
@endphp
<a href="{{ route('shop.home') }}" class="inline-flex items-center" aria-label="Bacha Stylo">
    {{-- Originals only: the logo is tiny already and a resized copy once lost its transparent background. --}}
    <x-shop.img :src="$src" alt="Bacha Stylo" :width="$px" :height="$px" :sizes="$px.'px'" :priority="$priority" :variants="false"
        :fallback-src="$logoUrl ? $packaged : null" fallback="none" referrerpolicy="no-referrer"
        {{ $attributes->class(['w-auto h-auto object-contain', $maxH]) }} />
</a>
