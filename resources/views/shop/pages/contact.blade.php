@extends('shop.layouts.app')

@section('title', 'Contact')
@section('description', "Get in touch with the Bacha Stylo team. We're here to help.")
@section('canonical', $settings->canonical('/contact'))

@section('content')
<div class="flex flex-col bg-white">
    {{-- HERO --}}
    <section class="relative min-h-[55vh] sm:min-h-[60vh] lg:min-h-[70vh] overflow-hidden bg-brand-black text-white">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(232,29,37,0.35)_0%,transparent_55%)]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,rgba(232,29,37,0.2)_0%,transparent_55%)]"></div>
        <div class="absolute inset-0 opacity-[0.05] bg-contact-grid"></div>
        <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-brand-red via-brand-red/50 to-transparent"></div>

        <div class="relative h-full flex flex-col justify-center container-shop py-20 sm:py-24 lg:py-32 min-h-[55vh] sm:min-h-[60vh] lg:min-h-[70vh]">
            <div class="flex items-center gap-3 mb-6">
                <span class="h-[2px] w-10 bg-brand-red"></span>
                <span class="text-[11px] uppercase tracking-[0.28em] text-brand-red font-bold">Contact Us</span>
            </div>
            <h1 class="font-display font-bold tracking-tightest leading-[0.94] text-[clamp(2.5rem,9vw,8rem)] text-white">
                Let's<br><span class="italic text-brand-red">talk.</span>
            </h1>
            <p class="mt-6 sm:mt-8 text-base sm:text-lg lg:text-xl text-white/70 max-w-2xl leading-relaxed">
                Have a question about sizing, an order, or a wholesale enquiry? We're here to help — usually within a few hours.
            </p>
        </div>
    </section>

    {{-- QUICK CONTACT CARDS --}}
    <section class="relative -mt-12 sm:-mt-16 lg:-mt-20 z-10">
        <div class="container-shop">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-0 bg-white border border-ink-10 shadow-deep">
                <x-shop.page-contact-card icon="mail" title="Email" :info="$email" sub="For general enquiries" :href="'mailto:'.$email" :index="1" />
                <x-shop.page-contact-card icon="message-circle" title="WhatsApp" :info="$phone" sub="Fastest way to reach us" :href="'https://wa.me/'.$phoneDigits" :index="2" />
                <x-shop.page-contact-card icon="phone" title="Call Us" :info="$phone" sub="Mon–Sat, 10am – 7pm" :href="$telHref" :index="3" />
                <x-shop.page-contact-card icon="map-pin" title="Visit Us" :info="$address" sub="By appointment only" :index="4" />
            </div>
        </div>
    </section>

    {{-- FORM SECTION --}}
    <section class="py-20 sm:py-24 lg:py-36 bg-white">
        <div class="container-shop grid lg:grid-cols-5 gap-12 lg:gap-20">
            <div class="lg:col-span-2">
                <div class="flex items-center gap-3 mb-5 sm:mb-6">
                    <span class="h-[2px] w-8 sm:w-10 bg-brand-red"></span>
                    <span class="text-[11px] uppercase tracking-[0.28em] text-brand-red font-bold">Send a Message</span>
                </div>
                <h2 class="font-display font-bold tracking-tightest leading-[0.98] text-4xl sm:text-5xl lg:text-6xl text-brand-black">
                    Drop us<br><span class="italic text-brand-red">a note.</span>
                </h2>
                <p class="mt-6 sm:mt-8 text-base sm:text-lg text-ink-70 leading-relaxed max-w-lg">
                    Fill out the form and our team will get back to you within one working day. For urgent queries, WhatsApp is the quickest route.
                </p>

                <div class="mt-10 space-y-5">
                    <div class="flex items-center gap-4 group">
                        <div class="h-11 w-11 border-2 border-brand-red text-brand-red group-hover:bg-brand-red group-hover:text-white flex items-center justify-center transition-colors shrink-0">
                            <x-lucide-clock class="h-4 w-4" stroke-width="2.5" />
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.22em] text-brand-red font-bold mb-0.5">Response Time</p>
                            <p class="text-sm text-ink-70">Under 24 hours</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 group">
                        <div class="h-11 w-11 border-2 border-brand-red text-brand-red group-hover:bg-brand-red group-hover:text-white flex items-center justify-center transition-colors shrink-0">
                            <x-lucide-message-circle class="h-4 w-4" stroke-width="2.5" />
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.22em] text-brand-red font-bold mb-0.5">WhatsApp</p>
                            <p class="text-sm text-ink-70">Usually within minutes</p>
                        </div>
                    </div>
                </div>

                <div class="mt-10">
                    <p class="mb-3 text-[10px] uppercase tracking-[0.22em] text-brand-red font-bold">Follow us</p>
                    <x-shop.social-links tone="onLight" size="md" />
                </div>

                <div class="hidden lg:block mt-14 relative">
                    <div class="absolute -top-3 -left-3 w-16 h-16 border-t-2 border-l-2 border-brand-red"></div>
                    <div class="bg-brand-black p-8 relative overflow-hidden">
                        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_right,rgba(232,29,37,0.25)_0%,transparent_55%)]"></div>
                        <div class="relative">
                            <p class="text-[10px] uppercase tracking-[0.28em] text-brand-red font-bold mb-3">For Wholesale</p>
                            <p class="font-display text-xl font-bold text-white leading-tight mb-2">Stocking Bacha Stylo?</p>
                            <p class="text-sm text-white/70 leading-relaxed">
                                Email <a href="mailto:{{ $email }}" class="text-brand-red hover:underline">{{ $email }}</a> with subject &ldquo;Wholesale&rdquo; for our line sheet.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="relative">
                    <div class="absolute -top-3 -right-3 hidden sm:block">
                        <div class="bg-brand-red text-white px-4 py-2 text-[10px] uppercase tracking-[0.22em] font-bold shadow-red-glow">We reply fast</div>
                    </div>
                    <div class="border-2 border-brand-black bg-white p-6 sm:p-8 lg:p-12 shadow-deep" x-data="contactForm()">
                        <div x-show="status === 'sent'" x-cloak class="text-center py-8">
                            <div class="h-16 w-16 mx-auto mb-6 border-2 border-brand-red text-brand-red flex items-center justify-center">
                                <x-lucide-check class="h-7 w-7" stroke-width="2.5" />
                            </div>
                            <p class="text-[11px] uppercase tracking-[0.28em] text-brand-red font-bold mb-3">Message Sent</p>
                            <h3 class="font-display text-2xl sm:text-3xl font-bold text-brand-black mb-3 tracking-tight">Thanks for reaching out!</h3>
                            <p class="text-ink-70 leading-relaxed max-w-sm mx-auto">We'll get back to you within one working day. For urgent queries, WhatsApp is the quickest route.</p>
                        </div>

                        <form x-show="status !== 'sent'" @submit.prevent="submit($event)" action="{{ url('/api/v1/contact') }}" method="post" class="space-y-6">
                            <div class="grid sm:grid-cols-2 gap-5">
                                <div>
                                    <label for="cf-name" class="block text-[10px] uppercase tracking-[0.28em] text-brand-red font-bold mb-2">Full Name</label>
                                    <input id="cf-name" name="name" type="text" placeholder="Ahmed Ali" required class="w-full border-2 border-ink-10 focus:border-brand-red bg-white px-4 py-3 text-sm text-brand-black placeholder:text-ink-30 outline-none transition-colors">
                                </div>
                                <div>
                                    <label for="cf-email" class="block text-[10px] uppercase tracking-[0.28em] text-brand-red font-bold mb-2">Email</label>
                                    <input id="cf-email" name="email" type="email" placeholder="you@example.com" required class="w-full border-2 border-ink-10 focus:border-brand-red bg-white px-4 py-3 text-sm text-brand-black placeholder:text-ink-30 outline-none transition-colors">
                                </div>
                            </div>
                            <div>
                                <label for="cf-subject" class="block text-[10px] uppercase tracking-[0.28em] text-brand-red font-bold mb-2">Subject</label>
                                <input id="cf-subject" name="subject" type="text" placeholder="Order #1234 — size question" required class="w-full border-2 border-ink-10 focus:border-brand-red bg-white px-4 py-3 text-sm text-brand-black placeholder:text-ink-30 outline-none transition-colors">
                            </div>
                            <div>
                                <label for="cf-message" class="block text-[10px] uppercase tracking-[0.28em] text-brand-red font-bold mb-2">Message</label>
                                <textarea id="cf-message" name="message" rows="5" required placeholder="Tell us how we can help…" class="w-full border-2 border-ink-10 focus:border-brand-red bg-white px-4 py-3 text-sm text-brand-black placeholder:text-ink-30 outline-none transition-colors resize-none"></textarea>
                            </div>
                            <p x-show="error" x-cloak x-text="error" class="text-sm font-medium text-brand-red" role="alert"></p>
                            <button type="submit" :disabled="status === 'sending'"
                                    class="group relative inline-flex items-center justify-center gap-3 bg-brand-red text-white px-8 py-5 text-[12px] sm:text-[13px] font-bold uppercase tracking-[0.18em] transition-all duration-500 hover:bg-brand-black disabled:opacity-60 disabled:cursor-not-allowed w-full sm:w-auto">
                                <span x-text="status === 'sending' ? 'Sending…' : 'Send Message'">Send Message</span>
                                <x-lucide-arrow-up-right class="h-4 w-4 transition-transform group-hover:rotate-45" stroke-width="2.5" />
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- STORE LOCATION / MAP --}}
    <section class="bg-ink-5 py-20 sm:py-24 lg:py-32">
        <div class="container-shop">
            <div class="grid items-stretch gap-10 lg:grid-cols-12 lg:gap-12">
                <div class="lg:col-span-4">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="h-[2px] w-10 bg-brand-red"></span>
                        <span class="text-[11px] font-bold uppercase tracking-[0.28em] text-brand-red">Visit Us</span>
                    </div>
                    <h2 class="font-display text-4xl font-bold tracking-tightest leading-[0.98] text-brand-black sm:text-5xl">Our <span class="italic text-brand-red">store.</span></h2>
                    <p class="mt-6 max-w-md text-base leading-relaxed text-ink-70 sm:text-lg">Drop by our physical store, or reach out before you visit — we're always happy to help in person.</p>

                    <div class="mt-8 space-y-5">
                        <div class="flex items-start gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center border-2 border-brand-red text-brand-red"><x-lucide-map-pin class="h-4 w-4" stroke-width="2.5" /></div>
                            <div>
                                <p class="mb-0.5 text-[10px] font-bold uppercase tracking-[0.22em] text-brand-red">Address</p>
                                <p class="text-sm text-ink-70">{{ $address }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center border-2 border-brand-red text-brand-red"><x-lucide-phone class="h-4 w-4" stroke-width="2.5" /></div>
                            <div>
                                <p class="mb-0.5 text-[10px] font-bold uppercase tracking-[0.22em] text-brand-red">Phone</p>
                                <a href="{{ $telHref }}" class="text-sm text-ink-70 hover:text-brand-black">{{ $phone }}</a>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center border-2 border-brand-red text-brand-red"><x-lucide-clock class="h-4 w-4" stroke-width="2.5" /></div>
                            <div>
                                <p class="mb-0.5 text-[10px] font-bold uppercase tracking-[0.22em] text-brand-red">Hours</p>
                                <p class="text-sm text-ink-70">Mon–Sat, 10am – 7pm</p>
                            </div>
                        </div>
                    </div>

                    <a href="{{ $directionsUrl }}" target="_blank" rel="noopener noreferrer" class="group mt-9 inline-flex items-center gap-3 bg-brand-black px-7 py-4 text-[12px] font-bold uppercase tracking-[0.18em] text-white transition-colors hover:bg-brand-red">
                        <x-lucide-navigation class="h-4 w-4" stroke-width="2.5" />
                        Get Directions
                        <x-lucide-arrow-up-right class="h-4 w-4 transition-transform group-hover:rotate-45" stroke-width="2.5" />
                    </a>
                </div>

                <div class="lg:col-span-8">
                    <div class="relative min-h-[340px] overflow-hidden border border-ink-10 bg-white lg:h-full lg:min-h-0">
                        <iframe title="Bacha Stylo store location" src="{{ $mapEmbed }}" class="absolute inset-0 h-full w-full" style="border:0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
