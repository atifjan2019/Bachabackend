@php $first = $product->images[0] ?? null; @endphp
{{-- Gallery: the first image is server-rendered for LCP; Alpine takes over for arrows, thumbs, swipe and colour photos. --}}
<div class="space-y-3">
    <div class="group relative aspect-[4/5] w-full overflow-hidden rounded-lg border border-border bg-cream"
         x-on:touchstart.passive="touchStart($event)" x-on:touchend.passive="touchEnd($event)">
        @if($first)
            @php($firstSet = \App\Support\Storefront\ImageVariant::srcset($first['url'], [480, 640, 768, 960, 1280]))
            <img src="{{ $first['url'] }}" :src="active.url" alt="{{ $first['alt'] }}" :alt="active.alt" data-bsf-img data-fallback-label="{{ $product->name }}"
                 @if($firstSet) srcset="{{ $firstSet }}" @endif :srcset="bsf.srcset(active.url, [480, 640, 768, 960, 1280])"
                 data-fallback-class="absolute inset-0 h-full w-full" onerror="bsfImgError(this)" loading="eager" fetchpriority="high" decoding="async"
                 sizes="(max-width: 1024px) 100vw, 50vw" class="absolute inset-0 h-full w-full object-cover">
        @else
            <div class="img-fallback absolute inset-0" role="img" aria-label="{{ $product->name }}"><span>{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</span></div>
        @endif

        <template x-if="images.length > 1">
            <div>
                <button type="button" x-on:click="prev()" aria-label="Previous image"
                        class="absolute left-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/85 text-brand-black shadow-sm backdrop-blur-sm transition hover:bg-white sm:opacity-0 sm:group-hover:opacity-100">
                    <x-lucide-chevron-left class="h-5 w-5" />
                </button>
                <button type="button" x-on:click="next()" aria-label="Next image"
                        class="absolute right-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/85 text-brand-black shadow-sm backdrop-blur-sm transition hover:bg-white sm:opacity-0 sm:group-hover:opacity-100">
                    <x-lucide-chevron-right class="h-5 w-5" />
                </button>
                <div class="absolute bottom-3 right-3 rounded-full bg-brand-black/70 px-2.5 py-1 text-[11px] font-medium text-white" x-text="`${index + 1} / ${images.length}`"></div>
            </div>
        </template>
    </div>

    <div x-cloak x-show="images.length > 1" x-ref="thumbs" class="flex gap-2 overflow-x-auto no-scrollbar scroll-smooth">
        <template x-for="(img, i) in images" :key="img.url">
            <button type="button" x-on:click="go(i)" :aria-label="`View image ${i + 1}`"
                    class="relative h-16 w-16 sm:h-20 sm:w-20 flex-shrink-0 overflow-hidden rounded-md border transition"
                    :class="i === index ? 'border-brand-black' : 'border-border hover:border-ink-30'">
                <img :src="img.url" :srcset="bsf.srcset(img.url, [96, 160])" :alt="img.alt" :data-fallback-label="name" data-bsf-img data-fallback-class="absolute inset-0 h-full w-full"
                     onerror="bsfImgError(this)" loading="lazy" decoding="async" sizes="80px" class="absolute inset-0 h-full w-full object-cover">
            </button>
        </template>
    </div>
</div>
