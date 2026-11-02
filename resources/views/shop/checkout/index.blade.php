@extends('shop.layouts.app')
@section('title', 'Checkout')
@section('description', 'Complete your Bacha Stylo order — Cash on Delivery, Bank Transfer, EasyPaisa or JazzCash.')
@section('robots', 'noindex, follow')

@php
    $labelClass = 'text-[10px] lg:text-[11px] uppercase tracking-wider mb-1.5 block';
    $inputClass = 'h-10 lg:h-12 text-sm';
    $cfg = [
        'guest' => ! $customer,
        'methods' => $methods,
        'method' => old('payment_method', $defaultMethod),
        'name' => old('name', $customer?->name),
        'email' => old('email', $customer?->email),
        'phone' => old('phone', $customer?->phone),
    ];
@endphp

@section('content')
<div class="container-shop py-6 sm:py-8 lg:py-16" x-data="checkoutForm(@js($cfg))">
    <div class="text-center mb-6 sm:mb-10">
        <h1 class="font-display text-2xl sm:text-3xl lg:text-5xl">Checkout</h1>
        <p class="mt-2 text-xs sm:text-sm text-muted uppercase tracking-[0.1em]">Complete your order</p>
    </div>

    <template x-if="$store.cart.lines.length === 0">
        <div class="py-6">
            <x-shop.empty-state icon="shopping-bag" title="Your cart is waiting"
                description="Kuch pyara sa choose karein. Your little one's next favourite outfit is only a click away."
                :cta-href="route('shop.products')" cta-label="Continue Shopping" />
        </div>
    </template>

    @if($errors->has('cart_json'))
        <div class="mb-6 max-w-2xl mx-auto bg-[#fdecec] border border-[#f4c7ca] text-brand-red p-4 text-sm">{{ $errors->first('cart_json') }}</div>
    @endif

    <form x-cloak x-show="$store.cart.lines.length > 0" method="post" action="{{ route('shop.checkout.store') }}" enctype="multipart/form-data"
          @submit="submit($event)" novalidate
          class="grid lg:grid-cols-[1fr_380px] gap-4 lg:gap-12 items-start">
        @csrf
        <input type="hidden" name="cart_json" :value="cartJson">

        <div class="space-y-4 lg:space-y-8">
            {{-- 01 Details --}}
            <section class="bg-white border border-ink-10 p-4 sm:p-6 lg:p-10">
                <h3 class="font-display text-base lg:text-2xl mb-4 lg:mb-8 flex items-center gap-3">
                    <span class="flex h-6 w-6 items-center justify-center bg-brand-red text-white text-[10px] font-bold">01</span>
                    Your Details
                </h3>
                <div class="grid sm:grid-cols-2 gap-3 lg:gap-6">
                    <div>
                        <x-shop.label for="name" class="{{ $labelClass }}">Full name</x-shop.label>
                        <x-shop.input id="name" name="name" :value="$customer?->name" x-model="name" class="{{ $inputClass }}" placeholder="Ayesha Khan" autocomplete="name" />
                        <x-shop.field-error name="name" />
                    </div>
                    <div>
                        <x-shop.label for="phone" class="{{ $labelClass }}">Phone Number</x-shop.label>
                        <x-shop.input id="phone" name="phone" type="tel" :value="$customer?->phone" x-model="phone" class="{{ $inputClass }}" placeholder="03XXXXXXXXX" autocomplete="tel" />
                        <x-shop.field-error name="phone" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-shop.label for="email" class="{{ $labelClass }}">
                            Email Address
                            @if($customer)
                                (Optional)
                            @else
                                <span class="text-brand-red">*</span>
                            @endif
                        </x-shop.label>
                        <x-shop.input id="email" name="email" type="email" :value="$customer?->email" x-model="email" class="{{ $inputClass }}" placeholder="you@example.com" autocomplete="email" :required="! $customer" />
                        <x-shop.field-error name="email" />
                        @unless($customer)
                            <p class="mt-1 text-[11px] text-muted">We email your order confirmation here. <a href="{{ route('shop.login') }}" class="text-brand-red hover:underline">Sign in</a> to skip this next time.</p>
                        @endunless
                    </div>
                </div>
            </section>

            {{-- 02 Shipping --}}
            <section class="bg-white border border-ink-10 p-4 sm:p-6 lg:p-10">
                <h3 class="font-display text-base lg:text-2xl mb-4 lg:mb-8 flex items-center gap-3">
                    <span class="flex h-6 w-6 items-center justify-center bg-brand-red text-white text-[10px] font-bold">02</span>
                    Shipping Address
                </h3>
                <div class="grid sm:grid-cols-2 gap-3 lg:gap-6">
                    <div class="sm:col-span-2">
                        <x-shop.label for="full_address" class="{{ $labelClass }}">Street Address</x-shop.label>
                        <x-shop.input id="full_address" name="full_address" :value="$customer?->address" class="{{ $inputClass }}" placeholder="House #, Street name, Area..." autocomplete="street-address" />
                        <x-shop.field-error name="full_address" />
                    </div>
                    <div>
                        <x-shop.label for="city" class="{{ $labelClass }}">City</x-shop.label>
                        <x-shop.input id="city" name="city" class="{{ $inputClass }}" placeholder="e.g. Lahore" autocomplete="address-level2" />
                        <x-shop.field-error name="city" />
                    </div>
                    <div>
                        <x-shop.label for="province" class="{{ $labelClass }}">Province</x-shop.label>
                        <div class="relative">
                            <select id="province" name="province" class="input-shop {{ $inputClass }} appearance-none pr-9 {{ $errors->has('province') ? 'border-brand-red' : '' }}">
                                @foreach($provinces as $p)
                                    <option value="{{ $p }}" @selected(old('province', 'Sindh') === $p)>{{ $p }}</option>
                                @endforeach
                            </select>
                            <x-lucide-chevron-down class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 opacity-60" />
                        </div>
                        <x-shop.field-error name="province" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-shop.label for="postal_code" class="{{ $labelClass }}">Postal Code (Optional)</x-shop.label>
                        <x-shop.input id="postal_code" name="postal_code" class="{{ $inputClass }}" placeholder="75500" autocomplete="postal-code" />
                        <x-shop.field-error name="postal_code" />
                    </div>
                </div>
                <div class="mt-4 lg:mt-8">
                    <x-shop.label for="notes" class="{{ $labelClass }}">Order Notes (Optional)</x-shop.label>
                    <x-shop.textarea id="notes" name="notes" class="text-sm min-h-[80px] lg:min-h-[120px]" placeholder="Anything else we should know?" />
                    <x-shop.field-error name="notes" />
                </div>
            </section>

            {{-- 03 Payment --}}
            <section class="bg-white border border-ink-10 p-4 sm:p-6 lg:p-10">
                <h3 class="font-display text-base lg:text-2xl mb-4 lg:mb-8 flex items-center gap-3">
                    <span class="flex h-6 w-6 items-center justify-center bg-brand-red text-white text-[10px] font-bold">03</span>
                    Payment Method
                </h3>
                <div class="grid gap-3" role="radiogroup">
                    @if(in_array('cod', $methods, true))
                        <x-shop.checkout-payment-option value="cod" title="Cash on Delivery" subtitle="Pay when it reaches your door.">
                            <p class="text-xs text-muted">Our rider will collect the exact amount at delivery. No payment proof needed — just place your order.</p>
                        </x-shop.checkout-payment-option>
                    @endif
                    @if(in_array('bank_transfer', $methods, true))
                        <x-shop.checkout-payment-option value="bank_transfer" title="Bank Transfer" subtitle="Transfer to our bank account.">
                            <x-shop.checkout-payment-rows :rows="$paymentRows['bank_transfer']" method="bank_transfer" />
                        </x-shop.checkout-payment-option>
                    @endif
                    @if(in_array('easypaisa', $methods, true))
                        <x-shop.checkout-payment-option value="easypaisa" title="EasyPaisa" subtitle="Pay via EasyPaisa mobile account.">
                            <x-shop.checkout-payment-rows :rows="$paymentRows['easypaisa']" method="easypaisa" />
                        </x-shop.checkout-payment-option>
                    @endif
                    @if(in_array('jazzcash', $methods, true))
                        <x-shop.checkout-payment-option value="jazzcash" title="JazzCash" subtitle="Pay via JazzCash mobile account.">
                            <x-shop.checkout-payment-rows :rows="$paymentRows['jazzcash']" method="jazzcash" />
                        </x-shop.checkout-payment-option>
                    @endif
                </div>
                <x-shop.field-error name="payment_method" />

                <div x-cloak x-show="requiresReceipt" class="mt-5 border-t border-ink-10 pt-5">
                    <x-shop.label for="receipt" class="{{ $labelClass }}">Payment Receipt / Screenshot <span class="text-brand-red">*</span></x-shop.label>
                    <input id="receipt" name="payment_receipt" type="file" accept="image/*,application/pdf" x-ref="receipt" @change="onReceipt($event)"
                           class="block w-full text-sm text-ink-70 file:mr-4 file:border-0 file:bg-brand-black file:px-4 file:py-2.5 file:text-xs file:font-bold file:uppercase file:tracking-wider file:text-white hover:file:bg-brand-red file:cursor-pointer cursor-pointer border border-ink-10">
                    <p class="mt-2 text-xs text-ink-70" x-show="receiptName">Selected: <span class="font-medium" x-text="receiptName"></span></p>
                    <p class="text-xs text-brand-red mt-1" x-show="receiptError" x-text="receiptError"></p>
                    <x-shop.field-error name="payment_receipt" />
                    <p class="mt-1 text-[11px] text-muted">Upload proof of your Bank Transfer / EasyPaisa / JazzCash payment (image or PDF) before placing the order.</p>
                </div>
            </section>

            <div class="hidden lg:block pt-4">
                <x-shop.button type="submit" size="lg" class="w-full h-14 text-sm font-bold uppercase tracking-widest" ::disabled="submitting">
                    <span x-text="submitting ? 'Processing...' : 'Confirm Order'">Confirm Order</span>
                </x-shop.button>
            </div>
        </div>

        <div class="sticky top-24 space-y-4">
            <x-shop.checkout-summary />
            <x-shop.delivery-policy />
            <div class="lg:hidden">
                <x-shop.button type="submit" size="lg" class="w-full h-12 text-xs font-bold uppercase tracking-widest" ::disabled="submitting">
                    <span x-text="submitting ? 'Processing...' : 'Place Order'">Place Order</span>
                </x-shop.button>
            </div>
            <p class="text-[10px] text-center text-ink-50 px-4">By placing your order, you agree to our <a href="{{ route('shop.terms') }}" class="underline">Terms of Service</a> and <a href="{{ route('shop.privacy') }}" class="underline">Privacy Policy</a>.</p>
            <div class="flex flex-col items-center gap-2.5 border-t border-ink-10 pt-4">
                <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-ink-50">Need help? Reach us on</span>
                <x-shop.social-links tone="onLight" size="sm" class="justify-center" />
            </div>
        </div>
    </form>

    <noscript>
        <p class="mt-6 text-center text-sm text-muted">Checkout needs JavaScript to read the cart saved in your browser. You can also <a href="{{ $settings->whatsapp()['href'] }}" class="text-brand-red underline">order on WhatsApp</a>.</p>
    </noscript>
</div>
@endsection
