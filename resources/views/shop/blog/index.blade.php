@extends('shop.layouts.app')

@section('title', 'The Journal')
@section('description', 'Stories of tradition & style — fashion insights, heritage stories, seasonal collections, styling inspiration, and brand storytelling from Bacha Stylo.')
@section('canonical', $settings->canonical('/blogs'))

@section('content')
<div class="flex flex-col bg-white">
    <x-shop.page-hero eyebrow="The Journal" title="Stories of Tradition & Style"
        subtitle="Fashion insights, heritage stories, seasonal collections, styling inspiration, and brand storytelling." variant="dark" align="center" />

    @if(! $featured)
        <div class="container-shop py-24 text-center">
            <h2 class="font-display text-2xl text-brand-black">No stories published yet.</h2>
            <p class="mt-2 text-ink-50">New journal entries are coming soon — check back shortly.</p>
        </div>
    @else
        <section class="container-shop py-16 lg:py-24">
            <a href="{{ route('shop.blog', $featured['slug']) }}" class="group grid items-center gap-8 lg:grid-cols-2 lg:gap-14">
                <div class="relative aspect-[4/3] overflow-hidden bg-brand-black">
                    <x-shop.home-collection-image :src="$featured['image']" :alt="$featured['title']" :label="$featured['title']" sizes="(max-width: 1024px) 100vw, 50vw" />
                    <span class="absolute left-5 top-5 inline-flex items-center bg-brand-red px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.18em] text-white">Featured</span>
                </div>
                <div>
                    <div class="mb-4 flex flex-wrap items-center gap-3 text-[11px] font-semibold uppercase tracking-[0.2em]">
                        <span class="text-brand-red">{{ $featured['category'] }}</span>
                        <span class="h-1 w-1 rounded-full bg-ink-30"></span>
                        <span class="text-ink-50">{{ $featured['date'] }}</span>
                        <span class="h-1 w-1 rounded-full bg-ink-30"></span>
                        <span class="text-ink-50">{{ $featured['readTime'] }}</span>
                    </div>
                    <h2 class="font-display text-3xl font-bold leading-[1.05] tracking-tightest text-brand-black transition-colors group-hover:text-brand-red sm:text-4xl lg:text-5xl">{{ $featured['title'] }}</h2>
                    <p class="mt-5 max-w-lg text-base leading-relaxed text-ink-70 sm:text-lg">{{ $featured['excerpt'] }}</p>
                    <span class="mt-7 inline-flex items-center gap-2 text-[12px] font-bold uppercase tracking-[0.18em] text-brand-black transition-all group-hover:gap-3">
                        Read story
                        <x-lucide-arrow-up-right class="h-4 w-4 transition-transform group-hover:rotate-45" stroke-width="2.5" />
                    </span>
                </div>
            </a>
        </section>

        @if(count($rest) > 0)
            <section class="bg-ink-5">
                <div class="container-shop py-16 lg:py-24">
                    <div class="mb-10 sm:mb-14">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="h-[2px] w-8 bg-brand-red"></span>
                            <span class="text-[11px] font-bold uppercase tracking-[0.28em] text-brand-red">Latest Stories</span>
                        </div>
                        <h2 class="font-display text-3xl font-bold tracking-tightest text-brand-black sm:text-4xl">From the Journal</h2>
                    </div>
                    <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 lg:gap-10">
                        @foreach($rest as $post)
                            <a href="{{ route('shop.blog', $post['slug']) }}" class="group block">
                                <div class="relative aspect-[4/3] overflow-hidden bg-brand-black">
                                    <x-shop.home-collection-image :src="$post['image']" :alt="$post['title']" :label="$post['title']" sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw" />
                                    <div class="absolute inset-0 bg-gradient-to-t from-brand-black/30 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
                                </div>
                                <div class="mt-5">
                                    <div class="mb-2.5 flex items-center gap-3 text-[10px] font-semibold uppercase tracking-[0.18em]">
                                        <span class="text-brand-red">{{ $post['category'] }}</span>
                                        <span class="h-1 w-1 rounded-full bg-ink-30"></span>
                                        <span class="text-ink-50">{{ $post['readTime'] }}</span>
                                    </div>
                                    <h3 class="font-display text-xl font-bold leading-snug tracking-tight text-brand-black transition-colors group-hover:text-brand-red">{{ $post['title'] }}</h3>
                                    <p class="mt-2.5 line-clamp-2 text-sm leading-relaxed text-ink-70">{{ $post['excerpt'] }}</p>
                                    <span class="mt-4 inline-block text-xs text-ink-50">{{ $post['date'] }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    @endif

    <section class="border-t border-ink-10 bg-white py-14 sm:py-16">
        <div class="container-shop flex flex-col items-center text-center">
            <span class="text-[11px] font-bold uppercase tracking-[0.28em] text-brand-red">Follow the journey</span>
            <h2 class="mt-3 font-display text-2xl font-bold tracking-tight text-brand-black sm:text-3xl">Get the latest from Bacha Stylo</h2>
            <p class="mt-3 max-w-md text-sm leading-relaxed text-ink-50">New stories, styling inspiration, and behind-the-scenes moments — straight from our socials.</p>
            <x-shop.social-links tone="onLight" size="lg" class="mt-7 justify-center" />
        </div>
    </section>
</div>
@endsection
