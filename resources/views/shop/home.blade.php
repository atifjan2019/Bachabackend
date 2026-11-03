@extends('shop.layouts.app')

@section('canonical', $settings->canonical('/'))

@push('head')
    @foreach($jsonLd as $ld)
        <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endforeach
@endpush

@section('content')
<div class="flex flex-col bg-white">
    @include('shop.home.promo-popup')
    @include('shop.home.hero')

    {{-- ─── MARQUEE ─── --}}
    <section class="relative bg-brand-red text-white overflow-hidden py-2 sm:py-4 border-y-2 border-brand-black">
        <div class="flex animate-marquee whitespace-nowrap gap-8 sm:gap-12">
            @for($i = 0; $i < 12; $i++)
                <div class="flex items-center gap-6 sm:gap-12 px-4 sm:px-6">
                    <span class="font-display text-sm md:text-xl font-black uppercase tracking-tight">New Arrivals</span>
                    <span class="text-lg">✦</span>
                    <span class="font-display text-sm md:text-xl font-black uppercase tracking-tight">Limited Drops</span>
                    <span class="text-lg">✦</span>
                    <span class="font-display text-sm md:text-xl font-black uppercase tracking-tight">Free Shipping</span>
                    <span class="text-lg">✦</span>
                </div>
            @endfor
        </div>
    </section>

    {{-- ─── CATEGORIES ─── --}}
    <section class="py-16 sm:py-20 lg:py-32 bg-white relative">
        <div class="container-shop">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-6 mb-10 sm:mb-14">
                <x-shop.section-heading eyebrow="Categories" title="Explore Our Collection"
                    subtitle="Carefully curated pieces that blend tradition, comfort, and modern sophistication." />
                <a href="{{ route('shop.products') }}" class="hidden sm:inline-flex items-center gap-2 text-brand-red font-bold text-sm uppercase tracking-[0.2em] hover:gap-3 transition-all shrink-0">
                    View All <x-lucide-arrow-right class="h-4 w-4" />
                </a>
            </div>

            {{-- Four-up on desktop at a fixed 4:5 ratio, so every card is the same size whatever its image. --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 lg:gap-4">
                @foreach($categories as $idx => $c)
                    <a href="{{ route('shop.products', ['category' => $c->slug]) }}"
                       class="group relative block overflow-hidden bg-brand-black shadow-[0_2px_20px_-10px_rgba(20,20,20,0.25)] transition-all duration-500 ease-out hover:-translate-y-1.5 hover:shadow-[0_35px_60px_-20px_rgba(20,20,20,0.5)]">
                        <div class="relative aspect-[4/5] overflow-hidden">
                            <x-shop.home-collection-image :src="$c->image" :alt="$c->name" :label="$c->name" sizes="(max-width: 640px) calc(50vw - 22px), (max-width: 1024px) calc(33vw - 16px), 290px" />
                            <div class="absolute inset-0 bg-gradient-to-t from-brand-black via-brand-black/30 to-transparent"></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-brand-black/50 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
                        </div>
                        <div class="pointer-events-none absolute inset-3 sm:inset-4 border border-white/0 transition-colors duration-500 group-hover:border-white/25"></div>
                        <div class="absolute left-0 top-0 h-0.5 w-0 bg-brand-red transition-all duration-500 ease-out group-hover:w-full"></div>
                        <div class="absolute right-2.5 top-2.5 sm:right-4 sm:top-4 flex h-7 w-7 items-center justify-center rounded-full border border-white/25 bg-white/10 text-white backdrop-blur-md transition-all duration-500 group-hover:border-brand-red group-hover:bg-brand-red sm:h-9 sm:w-9">
                            <x-lucide-arrow-up-right class="h-3.5 w-3.5 transition-transform duration-500 group-hover:rotate-45 sm:h-4 sm:w-4" stroke-width="2" />
                        </div>
                        <div class="absolute inset-x-0 bottom-0 p-3 sm:p-4 lg:p-5">
                            <div class="mb-1 flex items-center gap-1.5 sm:mb-1.5 sm:gap-2">
                                <span class="font-display text-[11px] italic text-brand-red sm:text-sm">{{ str_pad($idx + 1, 2, "0", STR_PAD_LEFT) }}</span>
                                <span class="h-px w-5 bg-gradient-to-r from-brand-red to-transparent sm:w-8"></span>
                            </div>
                            <h3 class="font-display text-[13px] font-bold leading-snug text-white transition-transform duration-500 ease-out group-hover:-translate-y-0.5 sm:text-lg sm:leading-tight lg:text-xl">{{ $c->name }}</h3>
                            <div class="mt-1 inline-flex items-center gap-1.5 text-[9px] font-semibold uppercase tracking-[0.18em] text-white/70 transition-colors duration-300 group-hover:text-white sm:mt-2 sm:gap-2 sm:text-[10px] sm:tracking-[0.2em]">
                                <span class="h-px w-3 bg-brand-red transition-all duration-500 group-hover:w-7 sm:w-4"></span>
                                Shop Now
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ─── FEATURED COLLECTION ─── --}}
    @if(count($spotlights) > 0)
        <section class="relative overflow-hidden bg-brand-black py-16 text-white sm:py-20 lg:py-32">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(232,29,37,0.18)_0%,transparent_55%)]"></div>
            <div class="container-shop relative">
                <div class="mb-10 flex flex-col gap-6 sm:mb-14 sm:flex-row sm:items-end sm:justify-between">
                    <x-shop.section-heading eyebrow="Featured Collection" title="Define your signature look."
                        subtitle="From formal gatherings to everyday elegance, explore collections designed to bring confidence, simplicity, and timeless style to every occasion." variant="dark" />
                    <a href="{{ route('shop.products') }}" class="hidden shrink-0 items-center gap-2 text-sm font-bold uppercase tracking-[0.2em] text-brand-red transition-all hover:gap-3 sm:inline-flex">
                        View All <x-lucide-arrow-right class="h-4 w-4" />
                    </a>
                </div>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:gap-6">
                    @php $first = $spotlights[0]; @endphp
                    <a href="{{ $first['href'] }}" class="group relative aspect-[4/5] overflow-hidden bg-brand-black-soft sm:aspect-[16/10] lg:col-span-7 lg:aspect-[4/3]">
                        <x-shop.home-collection-image :src="$first['image']" :alt="$first['title']" :label="$first['title']" sizes="(max-width: 1024px) 100vw, 58vw" />
                        <div class="absolute inset-0 bg-gradient-to-t from-brand-black via-brand-black/30 to-transparent"></div>
                        <div class="pointer-events-none absolute inset-4 border border-white/0 transition-colors duration-500 group-hover:border-white/25 sm:inset-6"></div>
                        <div class="absolute inset-x-0 bottom-0 p-6 sm:p-8 lg:p-10">
                            <p class="mb-3 text-[11px] font-bold uppercase tracking-[0.28em] text-brand-red">{{ $first['eyebrow'] }}</p>
                            <h3 class="font-display text-3xl font-bold leading-[1.05] sm:text-4xl lg:text-5xl">{{ $first['title'] }}</h3>
                            <p class="mt-3 max-w-md text-sm leading-relaxed text-white/70 sm:text-base">{{ $first['subtitle'] }}</p>
                            <span class="mt-5 inline-flex items-center gap-2 text-[12px] font-bold uppercase tracking-[0.2em] text-white">
                                Discover
                                <x-lucide-arrow-up-right class="h-4 w-4 transition-transform duration-500 group-hover:rotate-45" stroke-width="2.5" />
                            </span>
                        </div>
                    </a>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:col-span-5 lg:grid-cols-1 lg:grid-rows-2 lg:gap-6">
                        @foreach(array_slice($spotlights, 1) as $s)
                            <a href="{{ $s['href'] }}" class="group relative aspect-[16/10] overflow-hidden bg-brand-black-soft sm:aspect-[4/5] lg:aspect-auto lg:min-h-[200px]">
                                <x-shop.home-collection-image :src="$s['image']" :alt="$s['title']" :label="$s['title']" sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 42vw" />
                                <div class="absolute inset-0 bg-gradient-to-t from-brand-black via-brand-black/25 to-transparent"></div>
                                <div class="pointer-events-none absolute inset-3 border border-white/0 transition-colors duration-500 group-hover:border-white/25 sm:inset-4"></div>
                                <div class="absolute inset-x-0 bottom-0 p-5 sm:p-6 lg:p-7">
                                    <p class="mb-1.5 text-[10px] font-bold uppercase tracking-[0.24em] text-brand-red">{{ $s['eyebrow'] }}</p>
                                    <h3 class="font-display text-xl font-bold leading-tight sm:text-2xl lg:text-3xl">{{ $s['title'] }}</h3>
                                    <p class="mt-2 text-xs leading-relaxed text-white/70 sm:text-sm">{{ $s['subtitle'] }}</p>
                                    <span class="mt-3 inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-[0.2em] text-white/80 transition-colors group-hover:text-brand-red">
                                        Discover
                                        <x-lucide-arrow-up-right class="h-3.5 w-3.5 transition-transform duration-500 group-hover:rotate-45" stroke-width="2.5" />
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ─── BESTSELLERS ─── --}}
    <section class="py-16 sm:py-20 lg:py-32 bg-ink-5 relative noise-overlay">
        <div class="container-shop relative">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-6 mb-10 sm:mb-14">
                <x-shop.section-heading eyebrow="Most Loved" title="Best Sellers"
                    subtitle="Discover our most admired pieces, selected for their craftsmanship, comfort, and enduring style." />
                <a href="{{ route('shop.products') }}" class="hidden sm:inline-flex items-center gap-2 text-brand-red font-bold text-sm uppercase tracking-[0.2em] hover:gap-3 transition-all shrink-0">
                    View All <x-lucide-arrow-right class="h-4 w-4" />
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-5 lg:gap-8">
                @foreach($bestSellers as $i => $p)
                    <x-shop.product-card :product="$p" :label="$p->label ?? $bestsellerLabels[$i % count($bestsellerLabels)]" />
                @endforeach
            </div>

            <div class="mt-10 sm:mt-14 text-center sm:hidden">
                <a href="{{ route('shop.products') }}" class="btn-outline">View all products</a>
            </div>
        </div>
    </section>

    {{-- ─── CRAFT / QUALITY ─── --}}
    <section class="relative overflow-hidden bg-brand-black py-16 text-white sm:py-24 lg:py-32">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(232,29,37,0.16)_0%,transparent_60%)]"></div>
        <div class="container-shop relative">
            <x-shop.section-heading eyebrow="Craftsmanship" title="Crafted with precision. Designed to last."
                subtitle="At Bacha Stylo, we collaborate with skilled local artisans to create products that reflect quality craftsmanship, refined tradition, and everyday comfort." variant="dark" align="center" />

            <div class="mt-12 grid gap-px border border-white/10 bg-white/10 sm:mt-16 sm:grid-cols-3">
                @foreach([
                    ['icon' => 'gem', 'title' => 'Premium Materials', 'desc' => 'Hand-selected fabrics chosen for their richness, durability, and a refined finish.'],
                    ['icon' => 'scissors', 'title' => 'Refined Finishing', 'desc' => 'Meticulous stitching and detailing, completed by skilled artisan hands.'],
                    ['icon' => 'feather', 'title' => 'Everyday Comfort', 'desc' => 'Tailored for ease of movement and all-day comfort, without compromise.'],
                ] as $i => $item)
                    <div class="group relative bg-brand-black p-8 transition-colors duration-500 hover:bg-brand-black-soft sm:p-10 lg:p-12">
                        <span class="absolute right-6 top-6 font-display text-sm italic text-white/50">0{{ $i + 1 }}</span>
                        <div class="mb-6 flex h-14 w-14 items-center justify-center border border-brand-red/60 text-brand-red transition-colors duration-500 group-hover:bg-brand-red group-hover:text-white sm:h-16 sm:w-16">
                            <x-dynamic-component :component="'lucide-'.$item['icon']" class="h-6 w-6" stroke-width="1.5" />
                        </div>
                        <h3 class="font-display text-xl font-bold sm:text-2xl">{{ $item['title'] }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-white/65 sm:text-base">{{ $item['desc'] }}</p>
                        <span class="mt-6 block h-px w-10 bg-brand-red transition-all duration-500 group-hover:w-20"></span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ─── TRUST ─── --}}
    <section class="border-t border-ink-10 bg-white py-16 sm:py-20 lg:py-28">
        <div class="container-shop">
            <x-shop.section-heading eyebrow="Why Bacha Stylo" title="Why customers trust Bacha Stylo"
                subtitle="A premium shopping experience built on quality products, dependable service, and customer satisfaction." align="center" />
            <div class="mt-12 grid grid-cols-2 gap-4 sm:mt-14 sm:gap-5 md:grid-cols-3 lg:grid-cols-5">
                @foreach([
                    ['icon' => 'truck', 'title' => 'Nationwide Delivery'],
                    ['icon' => 'banknote', 'title' => 'Cash on Delivery'],
                    ['icon' => 'message-circle', 'title' => 'WhatsApp Support'],
                    ['icon' => 'sparkles', 'title' => 'New Weekly Arrivals'],
                    ['icon' => 'badge-check', 'title' => 'Carefully Curated'],
                ] as $t)
                    <div class="group flex flex-col items-center gap-4 border border-ink-10 bg-white px-5 py-8 text-center transition-all duration-300 hover:border-brand-red/40 hover:shadow-[0_20px_40px_-20px_rgba(20,20,20,0.18)] sm:px-6 sm:py-10">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-ink-5 text-brand-red transition-colors duration-300 group-hover:bg-brand-red group-hover:text-white">
                            <x-dynamic-component :component="'lucide-'.$t['icon']" class="h-6 w-6" stroke-width="1.5" />
                        </div>
                        <h3 class="text-[12px] font-bold uppercase tracking-[0.12em] text-brand-black sm:text-[13px]">{{ $t['title'] }}</h3>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ─── TRADITIONAL COLLECTION HIGHLIGHT ─── --}}
    <section class="relative overflow-hidden bg-brand-black py-16 text-white sm:py-24 lg:py-32">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,rgba(232,29,37,0.2)_0%,transparent_55%)]"></div>
        <div class="container-shop relative grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
            <div class="group relative order-2 aspect-[4/5] overflow-hidden sm:aspect-[16/11] lg:order-1 lg:aspect-[4/5]">
                <x-shop.home-collection-image :src="$settings->get('home_highlight_image') ?: ($heritageCategory?->image ?: null)" alt="Traditional Pakistani wear"
                    :label="$heritageCategory?->name ?? 'Heritage'" sizes="(max-width: 1024px) 100vw, 50vw" />
                <div class="absolute inset-0 bg-gradient-to-t from-brand-black/70 via-transparent to-transparent"></div>
                <div class="absolute left-0 top-0 h-16 w-16 border-l-2 border-t-2 border-brand-red"></div>
                <div class="absolute bottom-0 right-0 h-16 w-16 border-b-2 border-r-2 border-brand-red"></div>
            </div>

            <div class="order-1 lg:order-2">
                <div class="mb-5 flex items-center gap-3">
                    <span class="h-[2px] w-10 bg-brand-red"></span>
                    <span class="text-[11px] font-bold uppercase tracking-[0.28em] text-brand-red">Heritage</span>
                </div>
                <h2 class="font-display text-4xl font-bold leading-[1.02] tracking-tightest sm:text-5xl lg:text-6xl">
                    @if($settings->get('home_highlight_title'))
                        {{ $settings->get('home_highlight_title') }}
                    @else
                        Traditional wear with <span class="italic text-brand-red">modern elegance.</span>
                    @endif
                </h2>
                <p class="mt-6 max-w-lg text-base leading-relaxed text-white/70 sm:text-lg">
                    {{ $settings->get('home_highlight_description') ?: 'Explore premium waistcoats, clothes, authentic Chitrali pakol caps, elegant shawls, fragrances, and timeless accessories inspired by Pakistani heritage.' }}
                </p>
                <div class="mt-8 flex flex-wrap gap-2.5">
                    @foreach(['Waistcoats', 'Chitrali Pakol', 'Shawls', 'Fragrances', 'Accessories'] as $tag)
                        <span class="border border-white/15 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-white/70">{{ $tag }}</span>
                    @endforeach
                </div>
                <div class="mt-9">
                    <a href="{{ $settings->get('home_highlight_link') ?: route('shop.products') }}"
                       class="group inline-flex items-center gap-3 bg-brand-red px-8 py-5 text-[13px] font-bold uppercase tracking-[0.18em] text-white transition-all duration-500 hover:bg-white hover:text-brand-black">
                        {{ $settings->get('home_highlight_button') ?: 'View Collection' }}
                        <x-lucide-arrow-up-right class="h-4 w-4 transition-transform group-hover:rotate-45" stroke-width="2.5" />
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ─── SOCIAL MEDIA ─── --}}
    <section class="bg-white py-16 sm:py-20 lg:py-28">
        <div class="container-shop">
            <x-shop.section-heading eyebrow="@bachastylo" title="Experience the Bacha Stylo lifestyle."
                subtitle="Follow us for new arrivals, customer reviews, styling inspiration, behind-the-scenes moments, and exclusive product highlights." align="center" />
            <div class="mt-12 grid grid-cols-2 gap-4 sm:mt-14 sm:gap-5 lg:grid-cols-4">
                @foreach($socialCards as $s)
                    <x-shop.home-social-card :name="$s['name']" :icon="$s['key']" :href="$s['href']" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- ─── NEWSLETTER ─── --}}
    <section class="relative overflow-hidden bg-brand-black py-20 text-white sm:py-24 lg:py-28">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(232,29,37,0.2)_0%,transparent_60%)]"></div>
        <div class="container-shop relative">
            <x-shop.section-heading eyebrow="Newsletter" title="Get seasonal access to new arrivals."
                subtitle="Join the Bacha Stylo community for exclusive launches, seasonal collections, and premium style updates." align="center" variant="dark" />
            <div class="mt-8 sm:mt-10" x-data="newsletterForm()">
                <div x-show="status === 'success'" x-cloak class="mx-auto flex max-w-md items-center justify-center gap-3 border border-brand-red/40 bg-brand-red/10 px-6 py-5 text-sm font-semibold text-white">
                    <x-lucide-check class="h-5 w-5 text-brand-red" stroke-width="2.5" />
                    <span x-text="message"></span>
                </div>
                <form x-show="status !== 'success'" @submit.prevent="submit()" action="{{ url('/api/v1/newsletter') }}" method="post" class="mx-auto max-w-md">
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <input type="email" name="email" required x-model="email" placeholder="Enter your email"
                               class="w-full flex-1 border border-white/20 bg-white/5 px-5 py-4 text-sm text-white placeholder:text-white/40 outline-none transition-colors focus:border-brand-red">
                        <button type="submit" :disabled="status === 'loading'"
                                class="group inline-flex shrink-0 items-center justify-center gap-2 bg-brand-red px-7 py-4 text-[12px] font-bold uppercase tracking-[0.18em] text-white transition-all duration-500 hover:bg-white hover:text-brand-black disabled:cursor-not-allowed disabled:opacity-70">
                            <span x-text="status === 'loading' ? 'Joining…' : 'Subscribe Now'">Subscribe Now</span>
                            <x-lucide-arrow-up-right class="h-4 w-4 transition-transform group-hover:rotate-45" stroke-width="2.5" />
                        </button>
                    </div>
                    <p x-show="status === 'error'" x-cloak class="mt-3 text-xs text-brand-red-soft" x-text="message"></p>
                </form>
            </div>
            <p class="mt-4 text-center text-[11px] uppercase tracking-[0.18em] text-white/40">No spam — unsubscribe anytime</p>
        </div>
    </section>
</div>
@endsection
