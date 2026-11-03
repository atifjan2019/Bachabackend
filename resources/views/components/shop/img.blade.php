@props([
    'src' => null,
    'alt' => '',
    'label' => null,          // word whose first letter becomes the fallback monogram (defaults to alt)
    'fill' => false,          // absolutely fill the positioned parent (next/image fill)
    'fallbackSrc' => null,    // tried once before the panel (e.g. packaged logo behind a remote URL)
    'fallback' => 'panel',    // 'panel' | 'none' (hide on failure: decorative backdrops)
    'priority' => false,      // above the fold: eager + fetchpriority=high
    'width' => null,
    'height' => null,
    'sizes' => null,
    'widths' => null,         // srcset candidates (variant widths); defaults from sizes/width
    'variants' => true,       // false: always serve the original file (logos)
])
@php
    $src = is_string($src) ? trim($src) : '';
    $label = trim((string) ($label ?? $alt));
    $classes = $attributes->get('class', '');
    $layout = $fill ? 'absolute inset-0 h-full w-full' : '';
    // Resized variants (ImageVariant) when the slot size is known: fluid
    // candidates for a `sizes` image, 1x/2x for a fixed-width one.
    $srcset = null;
    if ($variants && $src !== '' && ($widths || $sizes || $width)) {
        $candidates = $widths ?: ($sizes ? \App\Support\Storefront\ImageVariant::FLUID : [(int) $width, (int) $width * 2]);
        $candidates = array_values(array_filter($candidates, fn ($w) => in_array($w, \App\Support\Storefront\ImageVariant::WIDTHS, true)));
        $srcset = $candidates ? \App\Support\Storefront\ImageVariant::srcset($src, $candidates) : null;
        if ($srcset && ! $sizes) {
            $sizes = $width.'px';
        }
    }
@endphp
@if($src === '')
    @if($fallback !== 'none')
        <div {{ $attributes->class(["img-fallback", $fill ? 'absolute inset-0' : 'relative h-full w-full']) }} role="img" aria-label="{{ $alt ?: 'Image unavailable' }}">
            @if($label !== '')<span>{{ mb_strtoupper(mb_substr($label, 0, 1)) }}</span>@endif
        </div>
    @endif
@else
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        data-bsf-img
        @if($label !== '') data-fallback-label="{{ $label }}" @endif
        @if($fallbackSrc) data-fallback-src="{{ $fallbackSrc }}" @endif
        @if($fallback === 'none') data-fallback="none" @endif
        @if($fill) data-fallback-class="{{ trim('absolute inset-0 '.$classes) }}" @endif
        onerror="bsfImgError(this)"
        @if($priority) loading="eager" fetchpriority="high" @else loading="lazy" @endif
        decoding="async"
        @if($width) width="{{ $width }}" @endif
        @if($height) height="{{ $height }}" @endif
        @if($srcset) srcset="{{ $srcset }}" @endif
        @if($sizes && $srcset) sizes="{{ $sizes }}" @endif
        {{ $attributes->except('class')->merge(['class' => trim($layout.' '.$classes)]) }}
    >
@endif
