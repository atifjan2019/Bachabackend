@props(['src' => null, 'alt' => '', 'sizes' => '100vw', 'label' => null])
{{-- CollectionImage: real catalogue image or a branded gradient panel, never stock photography. --}}
@if($src)
    <x-shop.img :src="$src" :alt="$alt" :label="$label ?? $alt" fill :sizes="$sizes"
        class="object-cover transition-transform duration-[1300ms] ease-out group-hover:scale-[1.06]" />
@else
    <div class="img-fallback absolute inset-0 transition-transform duration-[1300ms] ease-out group-hover:scale-[1.04]" role="img" aria-label="{{ $alt ?: 'Image unavailable' }}">
        @if(($label ?? $alt) !== '')<span>{{ mb_strtoupper(mb_substr($label ?? $alt, 0, 1)) }}</span>@endif
    </div>
@endif
