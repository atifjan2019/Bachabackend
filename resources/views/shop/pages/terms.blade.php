@extends('shop.layouts.app')

@section('title', 'Terms & Conditions')
@section('description', 'Terms and conditions for shopping at Bacha Stylo.')
@section('canonical', $settings->canonical('/terms'))

@section('content')
<div class="flex flex-col">
    <x-shop.page-hero eyebrow="Legal" title="Terms & Conditions" subtitle="Last updated: April 2026" />

    <section class="container-shop py-16 lg:py-20 max-w-3xl prose-policy">
        <h2>1. General</h2>
        <p>These terms govern your use of Bachastylo.com and any purchase you make through the site. By placing an order you agree to these terms in full. Bacha Stylo reserves the right to update them at any time; the latest version will always be available on this page.</p>

        <h2>2. Products &amp; Pricing</h2>
        <ul>
            <li>All prices are listed in Pakistani Rupees (PKR) and include applicable taxes.</li>
            <li>We make every effort to display accurate colours and details, but slight variations may occur due to screen settings and photography.</li>
            <li>Prices are subject to change without prior notice. The price charged is the price shown at the time of order placement.</li>
        </ul>

        <h2>3. Orders &amp; Payment</h2>
        <p>We accept Cash on Delivery (COD), JazzCash, Easypaisa, and major debit/credit cards. An order confirmation email is sent once the order is placed. We reserve the right to cancel or refuse any order for reasons including stock availability, pricing errors, or suspected fraud.</p>

        <h2>4. Shipping</h2>
        <ul>
            <li>We deliver nationwide across Pakistan within 2–5 working days.</li>
            <li>Delivery charge {{ pkr($shipping['fee']) }}{{ $shipping['freeEnabled'] ? '; orders of '.pkr($shipping['threshold']).' or more qualify for free shipping.' : ' on every order.' }}</li>
            @foreach($policy as $p)
                <li>{{ $p }}</li>
            @endforeach
            <li>Orders placed before 2 pm (PKT) on working days are dispatched the same day.</li>
            <li>Delivery timelines are estimates and may vary due to courier delays, public holidays, or unforeseen circumstances.</li>
        </ul>

        <h2>5. Returns &amp; Exchanges</h2>
        <p>Please see our <a href="{{ route('shop.refund') }}" class="underline hover:text-brand-black">Refund &amp; Return Policy</a> for full details. In summary, unworn and tagged items may be returned within 14 days of delivery.</p>

        <h2>6. Intellectual Property</h2>
        <p>All content on this website — including text, images, logos, and design — is the property of Bacha Stylo and may not be reproduced, distributed, or used without written permission.</p>

        <h2>7. Limitation of Liability</h2>
        <p>To the maximum extent permitted by law, Bacha Stylo shall not be liable for any indirect, incidental, or consequential damages arising from the use of our website or products. Our total liability for any claim shall not exceed the amount you paid for the relevant order.</p>

        <h2>8. Governing Law</h2>
        <p>These terms are governed by the laws of Pakistan. Any disputes shall be subject to the exclusive jurisdiction of the courts of Lahore.</p>

        <h2>9. Contact</h2>
        <p>For questions about these terms, email <strong>{{ $settings->supportEmail() }}</strong> or call <strong>{{ $settings->supportPhone() }}</strong>.</p>
    </section>
</div>
@endsection
