@extends('shop.layouts.app')

@section('title', 'Foundation & Vision')
@section('description', 'The people, vision, and values behind Bacha Stylo — a trusted Pakistani fashion and lifestyle brand rooted in quality, honesty, and modern elegance.')
@section('canonical', $settings->canonical('/about'))

@section('content')
@php $wa = $settings->whatsapp(); @endphp
<div class="flex flex-col bg-white">
    {{-- 1. HERO --}}
    <section class="relative overflow-hidden bg-brand-black py-24 text-white sm:py-32 lg:py-40">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(232,29,37,0.28)_0%,transparent_55%)]"></div>
        <div class="absolute left-0 right-0 top-0 h-[3px] bg-gradient-to-r from-brand-red via-brand-red/50 to-transparent"></div>
        <div class="container-shop relative max-w-4xl">
            <div class="mb-6 flex items-center gap-3">
                <span class="h-[2px] w-10 bg-brand-red"></span>
                <span class="text-[11px] font-bold uppercase tracking-[0.28em] text-brand-red">Foundation &amp; Vision</span>
            </div>
            <h1 class="font-display font-bold tracking-tightest leading-[0.96] text-[clamp(2.25rem,6.5vw,5.5rem)] text-white">
                The people behind <span class="italic text-brand-red">Bacha Stylo.</span>
            </h1>
            <p class="mt-7 max-w-2xl text-base leading-relaxed text-white/70 sm:text-lg lg:text-xl">
                A dedicated team working with passion to build a trusted Pakistani fashion brand rooted in quality, honesty, and modern elegance.
            </p>
        </div>
    </section>

    {{-- 2. TEAM STRUCTURE --}}
    <section class="bg-white py-20 sm:py-24 lg:py-32">
        <div class="container-shop">
            <div class="mx-auto mb-12 max-w-2xl text-center sm:mb-16">
                <div class="mb-5 flex items-center justify-center gap-3">
                    <span class="h-[2px] w-8 bg-brand-red"></span>
                    <span class="text-[11px] font-bold uppercase tracking-[0.28em] text-brand-red">Our Team</span>
                    <span class="h-[2px] w-8 bg-brand-red"></span>
                </div>
                <h2 class="font-display text-4xl font-bold tracking-tightest text-brand-black sm:text-5xl">Our Team Structure</h2>
                <p class="mt-5 text-base leading-relaxed text-ink-70 sm:text-lg">Meet the people who manage, design, and deliver the Bacha Stylo experience.</p>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-5 lg:grid-cols-5">
                @foreach($team as $role)
                    <div class="group overflow-hidden border border-ink-10 bg-white transition-all duration-500 hover:-translate-y-1.5 hover:shadow-[0_30px_60px_-24px_rgba(20,20,20,0.4)]">
                        <div class="relative aspect-[4/5] overflow-hidden bg-gradient-to-br from-brand-black via-brand-black-soft to-[#241015]">
                            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_50%_30%,rgba(232,29,37,0.22)_0%,transparent_60%)]"></div>
                            @if(! empty($role['image']))
                                <x-shop.img :src="$role['image']" :alt="$role['title']" fill fallback="none" class="object-cover transition-transform duration-500 group-hover:scale-105" />
                            @else
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <x-dynamic-component :component="'lucide-'.$role['lucide']" class="h-12 w-12 text-white/25 transition-transform duration-500 group-hover:scale-110" stroke-width="1.25" />
                                </div>
                            @endif
                            <div class="absolute left-0 top-0 h-0.5 w-0 bg-brand-red transition-all duration-500 group-hover:w-full"></div>
                        </div>
                        <div class="p-4 text-center sm:p-5">
                            <h3 class="font-display text-base font-bold leading-tight text-brand-black sm:text-lg">{{ $role['title'] }}</h3>
                            @if(! empty($role['subtitle']))
                                <p class="mt-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-brand-red">{{ $role['subtitle'] }}</p>
                            @endif
                            @if(! empty($role['bio']))
                                <p class="mt-2.5 text-xs leading-relaxed text-ink-70">{{ $role['bio'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 3. FOUNDER --}}
    <section class="relative overflow-hidden bg-ink-5 py-20 sm:py-24 lg:py-32">
        <div class="container-shop grid items-center gap-10 lg:grid-cols-12 lg:gap-16">
            <div class="relative order-2 mx-auto w-full max-w-sm lg:order-1 lg:col-span-5">
                <div class="relative aspect-[4/5] overflow-hidden bg-gradient-to-br from-brand-black via-brand-black-soft to-[#2a1116]">
                    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_40%_25%,rgba(232,29,37,0.25)_0%,transparent_60%)]"></div>
                    @if(! empty($founder['image']))
                        <x-shop.img :src="$founder['image']" :alt="$founder['name']" fill fallback="none" class="object-cover" />
                    @else
                        <span class="absolute inset-0 flex items-center justify-center font-display text-[7rem] font-bold leading-none text-white/10">{{ $founder['initials_resolved'] }}</span>
                    @endif
                </div>
                <div class="absolute -left-3 -top-3 h-14 w-14 border-l-2 border-t-2 border-brand-red sm:-left-4 sm:-top-4 sm:h-20 sm:w-20"></div>
                <div class="absolute -bottom-3 -right-3 h-14 w-14 border-b-2 border-r-2 border-brand-red sm:-bottom-4 sm:-right-4 sm:h-20 sm:w-20"></div>
            </div>
            <div class="order-1 lg:order-2 lg:col-span-7">
                <div class="mb-5 flex items-center gap-3">
                    <span class="h-[2px] w-10 bg-brand-red"></span>
                    <span class="text-[11px] font-bold uppercase tracking-[0.28em] text-brand-red">{{ $founder['role'] ?: 'Founder & CEO' }}</span>
                </div>
                <h2 class="font-display text-3xl font-bold tracking-tightest text-brand-black sm:text-4xl lg:text-5xl">{{ $founder['name'] }}</h2>
                <div class="mt-6 space-y-4 text-base leading-relaxed text-ink-70 sm:text-lg">
                    @foreach($founder['paragraphs'] as $p)
                        <p>{{ $p }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- 4 & 5. VISION + MISSION --}}
    <section class="bg-white py-20 sm:py-24 lg:py-32">
        <div class="container-shop grid gap-10 lg:grid-cols-2 lg:gap-16">
            <div class="border border-ink-10 p-8 sm:p-10 lg:p-12">
                <div class="mb-5 flex items-center gap-3">
                    <span class="h-[2px] w-8 bg-brand-red"></span>
                    <span class="text-[11px] font-bold uppercase tracking-[0.28em] text-brand-red">Our Vision</span>
                </div>
                <h2 class="font-display text-3xl font-bold tracking-tightest text-brand-black sm:text-4xl">Our Vision</h2>
                <p class="mt-6 text-base leading-relaxed text-ink-70 sm:text-lg">
                    To become a trusted Pakistani lifestyle brand that makes traditional fashion and lifestyle products accessible, authentic, and affordable for everyone — without compromising on quality or trust.
                </p>
            </div>
            <div class="border border-ink-10 p-8 sm:p-10 lg:p-12">
                <div class="mb-5 flex items-center gap-3">
                    <span class="h-[2px] w-8 bg-brand-red"></span>
                    <span class="text-[11px] font-bold uppercase tracking-[0.28em] text-brand-red">Our Mission</span>
                </div>
                <h2 class="font-display text-3xl font-bold tracking-tightest text-brand-black sm:text-4xl">Our Mission</h2>
                <ul class="mt-6 space-y-3.5">
                    @foreach($mission as $m)
                        <li class="flex items-start gap-3 text-sm leading-relaxed text-ink-70 sm:text-base">
                            <x-lucide-circle-check class="mt-0.5 h-5 w-5 shrink-0 text-brand-red" stroke-width="2" />
                            <span>{{ $m }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- 6. BRAND PROMISE --}}
    <section class="relative overflow-hidden bg-brand-black py-20 text-white sm:py-24 lg:py-28">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(232,29,37,0.2)_0%,transparent_60%)]"></div>
        <div class="container-shop relative mx-auto max-w-3xl text-center">
            <x-lucide-quote class="mx-auto mb-6 h-10 w-10 text-brand-red" stroke-width="1.5" />
            <p class="mb-3 text-[11px] font-bold uppercase tracking-[0.28em] text-brand-red">Our Brand Promise</p>
            <p class="font-display text-2xl font-bold leading-snug tracking-tight sm:text-3xl lg:text-4xl">
                We believe fashion should never be misleading or overpriced. Our promise is simple: <span class="italic text-brand-red">quality, honesty, and value</span> in every product.
            </p>
        </div>
    </section>

    {{-- 7. OUR STORY --}}
    <section class="bg-white py-20 sm:py-24 lg:py-32">
        <div class="container-shop grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
            <div class="relative order-2 aspect-[4/5] overflow-hidden sm:aspect-[16/11] lg:order-1 lg:aspect-[4/5]">
                <div class="absolute inset-0 bg-gradient-to-br from-brand-black via-brand-black-soft to-[#2a1116]">
                    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_30%_20%,rgba(232,29,37,0.22)_0%,transparent_60%)]"></div>
                    @if(! empty($story['image']))
                        <x-shop.img :src="$story['image']" :alt="$story['heading'] ?: 'Our story'" fill fallback="none" class="object-cover" />
                    @else
                        <span class="absolute inset-0 flex items-center justify-center font-display text-[5rem] font-bold leading-none text-white/[0.07] sm:text-[7rem]">BS</span>
                    @endif
                </div>
                @if(! empty($story['location']))
                    <div class="absolute bottom-5 left-5 inline-flex items-center gap-2 bg-brand-red px-4 py-2 text-[10px] font-bold uppercase tracking-[0.18em] text-white shadow-red-glow-lg">
                        <x-lucide-map-pin class="h-3.5 w-3.5" stroke-width="2.5" /> {{ $story['location'] }}
                    </div>
                @endif
            </div>
            <div class="order-1 lg:order-2">
                <div class="mb-5 flex items-center gap-3">
                    <span class="h-[2px] w-10 bg-brand-red"></span>
                    <span class="text-[11px] font-bold uppercase tracking-[0.28em] text-brand-red">Our Story</span>
                </div>
                <h2 class="font-display text-3xl font-bold tracking-tightest text-brand-black sm:text-4xl lg:text-5xl">
                    {{ $story['heading'] }}
                    @if(! empty($story['accent']))
                        <span class="italic text-brand-red">{{ $story['accent'] }}</span>
                    @endif
                </h2>
                <div class="mt-6 space-y-4 text-base leading-relaxed text-ink-70 sm:text-lg">
                    @foreach($story['paragraphs'] as $p)
                        <p>{{ $p }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- 8. VALUES --}}
    <section class="bg-ink-5 py-20 sm:py-24 lg:py-32">
        <div class="container-shop">
            <div class="mx-auto mb-12 max-w-2xl text-center sm:mb-16">
                <div class="mb-5 flex items-center justify-center gap-3">
                    <span class="h-[2px] w-8 bg-brand-red"></span>
                    <span class="text-[11px] font-bold uppercase tracking-[0.28em] text-brand-red">Our Values</span>
                    <span class="h-[2px] w-8 bg-brand-red"></span>
                </div>
                <h2 class="font-display text-4xl font-bold tracking-tightest text-brand-black sm:text-5xl">What we stand for</h2>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 sm:gap-5 lg:grid-cols-5">
                @foreach($values as $i => $v)
                    <div class="group relative border border-ink-10 bg-white p-6 transition-all duration-500 hover:-translate-y-1.5 hover:shadow-[0_30px_60px_-24px_rgba(20,20,20,0.35)]">
                        <div class="mb-5 flex h-12 w-12 items-center justify-center border border-brand-red/60 text-brand-red transition-colors duration-500 group-hover:bg-brand-red group-hover:text-white">
                            <x-dynamic-component :component="'lucide-'.$v['icon']" class="h-5 w-5" stroke-width="1.5" />
                        </div>
                        <span class="absolute right-5 top-5 font-display text-sm italic text-ink-30">0{{ $i + 1 }}</span>
                        <h3 class="font-display text-lg font-bold leading-tight text-brand-black">{{ $v['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-ink-70">{{ $v['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 9. CLOSING CTA --}}
    <section class="relative overflow-hidden bg-brand-black py-20 text-white sm:py-24 lg:py-32">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(232,29,37,0.22)_0%,transparent_60%)]"></div>
        <div class="absolute left-0 right-0 top-0 h-[2px] bg-gradient-to-r from-transparent via-brand-red to-transparent"></div>
        <div class="container-shop relative mx-auto max-w-3xl text-center">
            <h2 class="font-display text-4xl font-bold tracking-tightest sm:text-5xl lg:text-6xl">
                Experience the <span class="italic text-brand-red">Bacha Stylo</span> standard.
            </h2>
            <p class="mx-auto mt-6 max-w-xl text-base leading-relaxed text-white/70 sm:text-lg">Explore our collections and discover fashion built on trust, tradition, and quality.</p>
            <div class="mt-9 flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4">
                <a href="{{ route('shop.products') }}" class="group inline-flex w-full items-center justify-center gap-3 bg-brand-red px-8 py-5 text-[13px] font-bold uppercase tracking-[0.18em] text-white transition-all duration-500 hover:bg-white hover:text-brand-black sm:w-auto">
                    Shop Collection
                    <x-lucide-arrow-up-right class="h-4 w-4 transition-transform group-hover:rotate-45" stroke-width="2.5" />
                </a>
                <a href="{{ $wa['href'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-full items-center justify-center gap-3 border-2 border-white/40 px-8 py-5 text-[13px] font-bold uppercase tracking-[0.18em] text-white transition-all duration-300 hover:border-brand-red hover:text-brand-red sm:w-auto">
                    <x-shop.social-icon name="whatsapp" class="h-5 w-5" />
                    Contact on WhatsApp
                </a>
            </div>
            <p class="mt-7 text-sm text-white/60">
                Or reach us at <a href="mailto:{{ $settings->supportEmail() }}" class="text-white transition-colors hover:text-brand-red">{{ $settings->supportEmail() }}</a>
                · <a href="{{ $wa['href'] }}" target="_blank" rel="noopener noreferrer" class="text-white transition-colors hover:text-brand-red">{{ $settings->supportPhone() }}</a> (Call &amp; WhatsApp)
            </p>
        </div>
    </section>
</div>
@endsection
