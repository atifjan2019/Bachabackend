@php
    $columns = [
        'Explore' => [
            ['href' => route('shop.products'), 'label' => 'All products'],
            ['href' => route('shop.about'), 'label' => 'About us'],
            ['href' => route('shop.blogs'), 'label' => 'Journal'],
            ['href' => route('shop.account'), 'label' => 'My account'],
        ],
        'Customer Care' => [
            ['href' => route('shop.faq'), 'label' => 'FAQ'],
            ['href' => route('shop.contact'), 'label' => 'Shipping & Delivery'],
            ['href' => route('shop.refund'), 'label' => 'Returns & Exchanges'],
            ['href' => route('shop.contact'), 'label' => 'Contact Us'],
        ],
        'Legal' => [
            ['href' => route('shop.privacy'), 'label' => 'Privacy Policy'],
            ['href' => route('shop.terms'), 'label' => 'Terms & Conditions'],
            ['href' => route('shop.refund'), 'label' => 'Refund Policy'],
        ],
    ];
    $wa = $settings->whatsapp();
@endphp
<footer class="relative overflow-hidden bg-brand-black pb-20 text-white lg:pb-0">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(232,29,37,0.18)_0%,transparent_55%)]"></div>
    <div class="absolute left-0 right-0 top-0 h-[2px] bg-gradient-to-r from-transparent via-brand-red to-transparent"></div>

    <div class="container-shop relative">
        <div class="h-px bg-gradient-to-r from-transparent via-white/15 to-transparent"></div>
    </div>

    <div class="container-shop relative grid grid-cols-2 gap-8 py-14 sm:gap-10 lg:grid-cols-5 lg:gap-12 lg:py-20">
        <div class="col-span-2 lg:col-span-2">
            <div class="mb-6 inline-block">
                <x-shop.brand-mark size="lg" :logo-url="$settings->footerLogoUrl()" class="max-h-[100px]" />
            </div>
            <p class="max-w-sm text-sm leading-relaxed text-white/60">{{ $settings->footerAbout() }}</p>
            <div class="mt-7">
                <x-shop.social-links tone="onDark" size="md" />
            </div>

            <div class="mt-8">
                <p class="mb-3 text-[11px] font-bold uppercase tracking-[0.28em] text-brand-red">Get in touch</p>
                <div class="space-y-2.5 text-sm">
                    <a href="mailto:{{ $settings->supportEmail() }}" class="group flex items-center gap-3 text-white/70 transition-colors hover:text-brand-red">
                        <x-lucide-mail class="h-4 w-4 shrink-0 text-brand-red" stroke-width="2" />
                        {{ $settings->supportEmail() }}
                    </a>
                    <a href="{{ $wa['href'] }}" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-3 text-white/70 transition-colors hover:text-brand-red">
                        <x-lucide-phone class="h-4 w-4 shrink-0 text-brand-red" stroke-width="2" />
                        {{ $wa['number'] }}
                        <span class="text-white/40">· Call &amp; WhatsApp</span>
                    </a>
                </div>
            </div>
        </div>

        @foreach($columns as $title => $links)
            <div>
                <div class="mb-5 flex items-center gap-2">
                    <span class="h-[2px] w-5 bg-brand-red"></span>
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.28em] text-brand-red">{{ $title }}</h2>
                </div>
                <ul class="space-y-3 text-sm">
                    @foreach($links as $l)
                        <li>
                            <a href="{{ $l['href'] }}" class="group inline-flex items-center gap-1.5 text-white/70 transition-colors hover:text-brand-red">
                                {{ $l['label'] }}
                                <x-lucide-arrow-up-right class="h-3 w-3 -translate-x-1 opacity-0 transition-all group-hover:translate-x-0 group-hover:opacity-100" stroke-width="2.5" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>

    <div class="border-t border-white/10 bg-black">
        <div class="container-shop flex flex-col items-center justify-between gap-3 py-6 pr-16 text-xs text-white/50 sm:flex-row sm:pr-20 lg:pr-24">
            <p>&copy; {{ date('Y') }} Bacha Stylo. All rights reserved.</p>
            <p class="flex items-center gap-1.5 text-white/40">
                Made with
                <x-lucide-heart class="h-3.5 w-3.5 fill-brand-red text-brand-red" aria-hidden="true" />
                by
                <a href="https://www.webspires.com.pk?utm_source=bachastylo" target="_blank" rel="noopener noreferrer" class="font-medium text-white/70 transition-colors hover:text-brand-red">Webspires</a>
            </p>
        </div>
    </div>
</footer>
