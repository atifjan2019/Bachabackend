@extends('shop.layouts.app')

@section('title', $metaTitle)
@section('description', $metaDescription)
@section('canonical', $canonical)
@section('og_type', 'article')
@if($ogImage)
    @section('og_image', $ogImage)
@endif

@push('head')
    @if(trim((string) $post->meta_keywords) !== '')
        <meta name="keywords" content="{{ $post->meta_keywords }}">
    @endif
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<article class="min-h-screen bg-white">
    <div class="container-shop py-10 lg:py-16">
        <a href="{{ route('shop.blogs') }}" class="mb-10 inline-flex items-center text-sm font-medium text-ink-50 transition-colors hover:text-brand-black">
            <x-lucide-arrow-left class="mr-2 h-4 w-4" /> Back to the Journal
        </a>

        <div class="mx-auto mb-10 max-w-3xl text-center">
            <span class="mb-5 inline-block bg-brand-red px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.18em] text-white">{{ $category }}</span>
            <h1 class="mb-6 font-display text-4xl font-bold leading-tight tracking-tightest text-brand-black lg:text-5xl">{{ $post->title }}</h1>
            <div class="text-ink-50">{{ $date }}</div>
        </div>

        @if($post->image)
            <div class="relative mb-16 aspect-[21/9] w-full overflow-hidden bg-surface-sunken shadow-sm">
                <x-shop.img :src="$post->image" :alt="$post->title" :label="$post->title" fill priority class="object-cover" sizes="(max-width: 1320px) 100vw, 1320px" />
            </div>
        @endif

        <div class="prose prose-brand prose-lg mx-auto max-w-3xl">
            @if(trim((string) $post->content) !== '')
                {!! $post->content !!}
            @else
                <p>Content is still being written for this story. Check back soon.</p>
            @endif
        </div>

        <div class="mx-auto mt-14 flex max-w-3xl flex-col items-center gap-4 border-t border-ink-10 pt-10 sm:flex-row sm:justify-between">
            <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-brand-red">Enjoyed this? Follow us</p>
            <x-shop.social-links tone="onLight" size="md" />
        </div>
    </div>
</article>
@endsection
