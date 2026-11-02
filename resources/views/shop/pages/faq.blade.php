@extends('shop.layouts.app')

@section('title', 'Frequently Asked Questions')
@section('description', 'Answers to common questions about Bacha Stylo Fashion — products, orders, delivery, returns, payment, and customer support.')
@section('canonical', $settings->canonical('/faq'))

@section('content')
@php $wa = $settings->whatsapp(); @endphp
<div class="flex flex-col bg-white">
    <x-shop.page-hero eyebrow="Help Center" title="Frequently Asked Questions"
        subtitle="Everything you need to know about shopping with Bacha Stylo — products, orders, delivery, returns, payment, and support."
        variant="dark" align="center" />

    <div class="container-shop py-16 lg:py-24">
        <div class="flex gap-10 lg:gap-16">
            <aside class="hidden w-60 shrink-0 lg:block">
                <div class="sticky top-28">
                    <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.28em] text-brand-red">Categories</p>
                    <nav class="space-y-1">
                        @foreach($faqCategories as $cat)
                            <a href="#{{ $cat['id'] }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-ink-70 transition-colors hover:bg-ink-5 hover:text-brand-black">
                                <x-dynamic-component :component="'lucide-'.$cat['icon']" class="h-4 w-4 shrink-0 text-brand-red" stroke-width="1.75" />
                                {{ $cat['title'] }}
                            </a>
                        @endforeach
                    </nav>
                </div>
            </aside>

            <div class="min-w-0 flex-1 space-y-14 lg:space-y-20">
                <div class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1 lg:hidden no-scrollbar">
                    @foreach($faqCategories as $cat)
                        <a href="#{{ $cat['id'] }}" class="whitespace-nowrap border border-ink-10 px-3.5 py-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-ink-70 transition-colors hover:border-brand-red hover:text-brand-red">{{ $cat['title'] }}</a>
                    @endforeach
                </div>

                @foreach($faqCategories as $cat)
                    <section id="{{ $cat['id'] }}" class="scroll-mt-28">
                        <div class="mb-6 flex items-center gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center border border-brand-red/50 text-brand-red">
                                <x-dynamic-component :component="'lucide-'.$cat['icon']" class="h-5 w-5" stroke-width="1.75" />
                            </div>
                            <h2 class="font-display text-2xl font-bold tracking-tight text-brand-black sm:text-3xl">{{ $cat['title'] }}</h2>
                        </div>

                        <div class="border-t border-ink-10">
                            @foreach($cat['items'] as $i => $item)
                                <div x-data="{ open: false }" class="border-b border-ink-10">
                                    <h3 class="flex">
                                        <button type="button" @click="open = !open" :aria-expanded="open" aria-controls="{{ $cat['id'] }}-{{ $i }}"
                                                class="flex flex-1 items-center justify-between py-5 text-base font-semibold text-brand-black text-left transition sm:text-[17px]">
                                            <span>{{ $item['q'] }}</span>
                                            <x-lucide-chevron-down class="h-4 w-4 shrink-0 text-muted transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
                                        </button>
                                    </h3>
                                    <div id="{{ $cat['id'] }}-{{ $i }}" x-show="open" x-cloak
                                         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                         class="overflow-hidden">
                                        <p class="max-w-2xl pb-5 text-[15px] leading-relaxed text-ink-70">{{ $item['a'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>

    <section class="relative overflow-hidden bg-brand-black py-16 text-white sm:py-20">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(232,29,37,0.2)_0%,transparent_60%)]"></div>
        <div class="container-shop relative mx-auto max-w-2xl text-center">
            <h2 class="font-display text-3xl font-bold tracking-tightest sm:text-4xl">Still have <span class="italic text-brand-red">questions?</span></h2>
            <p class="mx-auto mt-4 max-w-md text-base leading-relaxed text-white/70">Our team is here to help. Reach out and we'll get back to you as quickly as we can.</p>
            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4">
                <a href="{{ $wa['href'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-full items-center justify-center gap-2 bg-[#25D366] px-7 py-4 text-[12px] font-bold uppercase tracking-[0.18em] text-white transition-opacity hover:opacity-90 sm:w-auto">
                    <x-shop.social-icon name="whatsapp" class="h-5 w-5" />
                    Chat on WhatsApp
                </a>
                <a href="{{ route('shop.contact') }}" class="inline-flex w-full items-center justify-center gap-2 border-2 border-white/40 px-7 py-4 text-[12px] font-bold uppercase tracking-[0.18em] text-white transition-colors hover:border-brand-red hover:text-brand-red sm:w-auto">Contact Us</a>
            </div>
            <p class="mt-6 text-sm text-white/60">
                <a href="{{ $wa['href'] }}" target="_blank" rel="noopener noreferrer" class="text-white transition-colors hover:text-brand-red">{{ $settings->supportPhone() }}</a> (Call &amp; WhatsApp) ·
                <a href="mailto:{{ $settings->supportEmail() }}" class="text-white transition-colors hover:text-brand-red">{{ $settings->supportEmail() }}</a>
            </p>
            <div class="mt-10">
                <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.28em] text-brand-red">Connect with us</p>
                <x-shop.social-links tone="onDark" size="md" class="justify-center" />
            </div>
        </div>
    </section>
</div>
@endsection
