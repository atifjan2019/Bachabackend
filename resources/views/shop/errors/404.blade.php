@extends('shop.layouts.app')

@section('title', 'Page not found')
@section('description', 'This page is out of stock. The page you are looking for has been moved, deleted, or never existed.')
@section('robots', 'noindex, follow')

@section('content')
<div x-data="notFoundPage()" @mousemove.window.passive="move($event)" class="relative min-h-[80vh] flex items-center justify-center overflow-hidden px-4">
    <div class="absolute inset-0 transition-all duration-700 ease-out pointer-events-none" :style="{ background }"
         style="background: radial-gradient(600px circle at 50% 50%, rgba(232,29,37,0.06), transparent 50%), radial-gradient(800px circle at 20% 80%, rgba(232,29,37,0.03), transparent 50%)"></div>

    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[15%] left-[10%] w-20 h-20 rounded-full border border-ink-10 animate-float-slow opacity-40"></div>
        <div class="absolute top-[60%] right-[15%] w-14 h-14 rounded-lg border border-ink-10 rotate-45 animate-float-medium opacity-30"></div>
        <div class="absolute bottom-[20%] left-[20%] w-10 h-10 rounded-full border border-brand-red/20 animate-float-fast opacity-40"></div>
        <div class="absolute top-[30%] right-[25%] w-6 h-6 rounded-full bg-brand-red/5 animate-float-medium"></div>
        <div class="absolute bottom-[40%] left-[60%] w-16 h-16 rounded-lg border border-ink-10 -rotate-12 animate-float-slow opacity-20"></div>
    </div>

    <div class="relative z-10 text-center max-w-lg">
        <div class="relative mb-6">
            <p class="font-display text-[10rem] sm:text-[12rem] font-bold leading-none tracking-tightest text-ink-10 select-none">404</p>
            <p class="absolute inset-0 font-display text-[10rem] sm:text-[12rem] font-bold leading-none tracking-tightest text-brand-red/10 select-none animate-glitch-1" aria-hidden="true">404</p>
        </div>

        <div class="flex items-center justify-center gap-3 mb-6">
            <span class="block w-12 h-px bg-gradient-to-r from-transparent to-ink-10"></span>
            <span class="block w-2 h-2 rounded-full bg-brand-red animate-pulse"></span>
            <span class="block w-12 h-px bg-gradient-to-l from-transparent to-ink-10"></span>
        </div>

        <h1 class="font-display text-2xl sm:text-3xl text-ink tracking-tighter font-semibold mb-3">This page is out of stock</h1>
        <p class="text-ink-50 text-sm sm:text-base leading-relaxed max-w-md mx-auto mb-8">
            The page you're looking for has been moved, deleted, or perhaps never existed. Let's get you back on track.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
            <a href="{{ route('shop.home') }}" class="group inline-flex items-center gap-2.5 bg-brand-black text-white px-7 py-3.5 rounded-full text-sm font-medium transition-all duration-300 hover:bg-ink-70 hover:shadow-lg hover:-translate-y-0.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-300 group-hover:-translate-x-1"><path d="m15 18-6-6 6-6"/></svg>
                Back to Home
            </a>
            <a href="{{ route('shop.products') }}" class="group inline-flex items-center gap-2.5 border border-ink-10 bg-white text-brand-black px-7 py-3.5 rounded-full text-sm font-medium transition-all duration-300 hover:border-brand-black hover:shadow-md hover:-translate-y-0.5">
                Browse Shop
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-300 group-hover:translate-x-1"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        </div>

        <p class="mt-10 text-xs text-ink-30">Error 404 — Page Not Found</p>
    </div>
</div>
@endsection
