@extends('shop.layouts.app')

@section('title', 'Privacy Policy')
@section('description', 'How Bacha Stylo collects, uses, and protects your personal information.')
@section('canonical', $settings->canonical('/privacy'))

@section('content')
<div class="flex flex-col">
    <x-shop.page-hero eyebrow="Legal" title="Privacy Policy" subtitle="Last updated: April 2026" />

    <section class="container-shop py-16 lg:py-20 max-w-3xl prose-policy">
        <h2>1. Information We Collect</h2>
        <p>When you place an order or create an account on Bachastylo.com we collect your name, email address, phone number, shipping address, and payment details (processed securely by our payment partners). We also collect basic browsing data such as IP address, browser type, and pages visited through cookies and analytics tools.</p>

        <h2>2. How We Use Your Information</h2>
        <ul>
            <li>Process and fulfil your orders, including delivery and returns.</li>
            <li>Send order confirmations, shipping updates, and customer-service replies.</li>
            <li>Improve our website, products, and marketing based on aggregated analytics.</li>
            <li>Send promotional emails only if you have opted in. You may unsubscribe at any time.</li>
        </ul>

        <h2>3. Data Sharing</h2>
        <p>We never sell your personal data. We share information only with trusted third parties needed to operate our business — courier partners (e.g., Leopards, TCS) for delivery, payment processors (JazzCash, Easypaisa, bank gateways), and analytics providers. Each partner is bound by their own privacy obligations.</p>

        <h2>4. Cookies</h2>
        <p>Our site uses first-party cookies to keep you signed in and remember your cart. We may use analytics cookies (e.g., Google Analytics) to understand traffic. You can disable cookies in your browser settings, though some site features may not work correctly.</p>

        <h2>5. Data Security</h2>
        <p>All data transmitted between your browser and our servers is encrypted via TLS/SSL. Payment card details are never stored on our servers — they are processed directly by PCI-compliant payment gateways.</p>

        <h2>6. Your Rights</h2>
        <p>You may request access to, correction of, or deletion of your personal data at any time by emailing <strong>{{ $settings->supportEmail() }}</strong>. We will respond within 7 working days.</p>

        <h2>7. Children's Privacy</h2>
        <p>Our website and accounts are intended for use by adults. We do not knowingly collect personal information from anyone under the age of 18.</p>

        <h2>8. Changes to This Policy</h2>
        <p>We may update this policy from time to time. Changes will be posted on this page with a revised "Last updated" date. Continued use of the site constitutes acceptance of the updated policy.</p>

        <h2>9. Contact</h2>
        <p>Questions about this policy? Reach us at <strong>{{ $settings->supportEmail() }}</strong> or call <strong>{{ $settings->supportPhone() }}</strong> ({{ $settings->supportHours() }}).</p>
    </section>
</div>
@endsection
