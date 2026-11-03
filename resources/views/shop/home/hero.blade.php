@php
    $stats = [['num' => '50k+', 'label' => 'Happy Customers'], ['num' => '200+', 'label' => 'Signature Pieces'], ['num' => '4.9★', 'label' => 'Customer Rating']];
    $hasVideo = $hero['hasVideo'];
    $hasImage = $hero['hasImage'];
    $primaryCta = $hasVideo && $hero['socialUrl'];
@endphp
@php($heroSet = \App\Support\Storefront\ImageVariant::srcset($heroImage, [480, 768, 960, 1280, 1600]))
@if(! $hasVideo && ! $hasImage)
    @push('head')
        {{-- The hero is the largest paint on the home page: fetch it before the parser reaches it. --}}
        @if($heroSet)
            <link rel="preload" as="image" imagesrcset="{{ $heroSet }}" imagesizes="100vw" fetchpriority="high">
        @else
            <link rel="preload" as="image" href="{{ $heroImage }}" fetchpriority="high">
        @endif
    @endpush
@endif
<section class="relative w-full overflow-hidden bg-brand-black text-white"
         x-data="heroBanner({ video: @js($hasVideo), image: @js($hasImage) })">
    {{-- Background: launch-banner media while active, else the default hero image --}}
    @if($hasVideo && $hero['embedUrl'])
        <div class="pointer-events-none absolute inset-0 overflow-hidden" x-show="showVideo">
            <iframe src="{{ $hero['embedUrl'] }}" title="Intro video" allow="autoplay; encrypted-media; picture-in-picture" tabindex="-1"
                    class="absolute left-1/2 top-1/2 aspect-video w-[max(100%,177.78vh)] min-h-full min-w-full -translate-x-1/2 -translate-y-1/2 border-0"></iframe>
        </div>
    @elseif($hasVideo)
        <video src="{{ $hero['videoUrl'] }}" @if($hero['image']) poster="{{ $hero['image'] }}" @endif autoplay muted loop playsinline x-show="showVideo"
               class="absolute inset-0 h-full w-full object-cover object-center"></video>
    @endif
    @if($hasImage)
        <img src="{{ $hero['image'] }}" alt="Intro banner" x-show="showImage" x-on:error="bannerFailed = true"
             class="absolute inset-0 h-full w-full object-cover object-center">
    @endif
    <div class="absolute inset-0" x-show="showDefault">
        <x-shop.img :src="$heroImage" alt="Bacha Style collection" fill priority sizes="100vw" :widths="[480, 768, 960, 1280, 1600]" fallback="none" class="object-cover object-center" />
    </div>

    <div class="absolute inset-0 bg-gradient-to-r from-brand-black/85 via-brand-black/55 to-brand-black/20"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-brand-black/70 via-transparent to-brand-black/30"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(232,29,37,0.28)_0%,transparent_55%)]"></div>
    <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-brand-red via-brand-red/50 to-transparent"></div>

    @if($hasVideo)
        <button type="button" @click="dismissed = true" x-show="showVideo" aria-label="Skip intro banner"
                class="absolute right-4 top-4 z-20 flex h-10 w-10 items-center justify-center rounded-full border border-white/25 bg-brand-black/40 text-white backdrop-blur-md transition-all duration-300 hover:border-brand-red hover:bg-brand-red sm:right-6 sm:top-6">
            <x-lucide-x class="h-5 w-5" stroke-width="2.5" />
        </button>
    @endif

    <div class="pointer-events-none absolute left-0 top-0 bottom-0 hidden w-20 xl:block">
        <div class="absolute left-1/2 top-1/2 flex -translate-x-1/2 -translate-y-1/2 -rotate-90 items-center gap-4 whitespace-nowrap text-[10px] font-semibold uppercase tracking-[0.42em] text-white/40">
            <span>Est · 2020</span>
            <span class="text-brand-red">◆</span>
            <span>Made in Pakistan</span>
        </div>
    </div>

    <div class="relative container-shop flex min-h-[80vh] flex-col justify-center py-20 sm:min-h-[86vh] lg:min-h-[92vh] lg:py-28">
        <div class="max-w-2xl animate-fade-up">
            <div class="mb-7 inline-flex items-center gap-3">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand-red opacity-75"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-brand-red"></span>
                </span>
                <span class="text-[11px] font-bold uppercase tracking-[0.32em] text-white/80">Heritage Collection · 2026</span>
            </div>

            <h1 class="font-display font-bold tracking-tightest leading-[0.96] text-[clamp(2.25rem,7vw,5rem)] text-white">
                <span class="block">Timeless Pakistani style,</span>
                <span class="block">crafted with <span class="italic text-brand-red">distinction.</span></span>
            </h1>

            <p class="mt-6 max-w-xl text-sm leading-relaxed text-white/75 sm:text-base lg:text-lg">
                Discover premium waistcoats, Chitrali pakol caps, elegant shawls, fragrances, footwear, and refined everyday essentials — designed for a modern traditional lifestyle.
            </p>

            <div class="mt-9 flex flex-col flex-wrap gap-3 sm:flex-row sm:gap-4">
                @if($primaryCta)
                    <a href="{{ $hero['socialUrl'] }}" target="_blank" rel="noopener noreferrer" x-show="showVideo"
                       class="group inline-flex items-center justify-center gap-3 bg-brand-red px-6 py-4 text-[12px] font-bold uppercase tracking-[0.18em] text-white transition-all duration-500 hover:bg-white hover:text-brand-black sm:px-8 sm:py-5 sm:text-[13px]">
                        <x-lucide-play class="h-4 w-4 fill-current" stroke-width="0" />
                        <span>{{ $hero['button'] ?: 'Watch Video' }}</span>
                    </a>
                @endif
                <a href="{{ route('shop.products') }}"
                   class="group {{ $primaryCta ? 'inline-flex items-center justify-center gap-3 border-2 border-white/40 px-6 py-4 text-[12px] font-bold uppercase tracking-[0.18em] text-white transition-all duration-300 hover:border-brand-red hover:text-brand-red sm:px-8 sm:py-5 sm:text-[13px]' : 'relative inline-flex items-center justify-center gap-3 overflow-hidden bg-brand-red px-6 py-4 text-[12px] font-bold uppercase tracking-[0.18em] text-white transition-all duration-500 hover:bg-white hover:text-brand-black sm:px-8 sm:py-5 sm:text-[13px]' }}">
                    <span class="relative z-10">Shop the Collection</span>
                    <x-lucide-arrow-up-right class="relative z-10 h-4 w-4 transition-transform group-hover:rotate-45" stroke-width="2.5" />
                </a>
                <a href="{{ route('shop.products', ['category' => 'traditional-wear']) }}"
                   class="inline-flex items-center justify-center gap-3 border-2 border-white/40 px-6 py-4 text-[12px] font-bold uppercase tracking-[0.18em] text-white transition-all duration-300 hover:border-brand-red hover:text-brand-red sm:px-8 sm:py-5 sm:text-[13px]">
                    Explore Traditional Wear
                </a>
            </div>

            @if($hasVideo)
                <button type="button" @click="dismissed = true" x-show="showVideo"
                        class="mt-5 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/60 underline-offset-4 transition-colors hover:text-white hover:underline">Skip intro</button>
            @endif

            <div class="mt-12 grid max-w-lg grid-cols-3 gap-4 border-t border-white/15 pt-6 sm:gap-6 lg:mt-16 lg:gap-12 lg:pt-8">
                @foreach($stats as $s)
                    <div class="min-w-0">
                        <div class="truncate font-display text-2xl font-bold text-white sm:text-3xl lg:text-4xl">{{ $s['num'] }}</div>
                        <div class="mt-1 text-[9px] uppercase leading-tight tracking-[0.18em] text-white/55 sm:text-[10px] sm:tracking-[0.22em]">{{ $s['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
