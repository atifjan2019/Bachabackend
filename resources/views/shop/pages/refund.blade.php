@extends('shop.layouts.app')

@section('title', 'Refund & Return Policy')
@section('description', "Bacha Stylo's refund, return, and exchange policy.")
@section('canonical', $settings->canonical('/refund'))

@section('content')
<div class="flex flex-col">
    <x-shop.page-hero eyebrow="Legal" title="Refund & Return Policy" subtitle="Last updated: April 2026" />

    <section class="container-shop py-16 lg:py-20 max-w-3xl prose-policy">
        <h2>1. Return Window</h2>
        <p>You may return any item within <strong>14 days</strong> of delivery. Items must be unworn, unwashed, and in their original packaging with all tags attached.</p>

        <h2>2. Non-Returnable Items</h2>
        <ul>
            <li>Items marked as "Final Sale" or purchased during clearance promotions.</li>
            <li>Undergarments and socks, for hygiene reasons.</li>
            <li>Customised or made-to-order items.</li>
        </ul>

        <h2>3. How to Initiate a Return</h2>
        <ol>
            <li>Email <strong>{{ $settings->supportEmail() }}</strong> or message us on WhatsApp at <strong>{{ $settings->supportPhone() }}</strong> with your order number and reason for return.</li>
            <li>Our team will confirm eligibility and arrange a reverse pickup at no extra cost.</li>
            <li>Once we receive and inspect the item, we'll process your refund within <strong>5–7 working days</strong>.</li>
        </ol>

        <h2>4. Refund Method</h2>
        <ul>
            <li><strong>Online payments</strong> (card, JazzCash, Easypaisa) — refunded to the original payment method.</li>
            <li><strong>Cash on Delivery orders</strong> — refunded via bank transfer or Easypaisa/JazzCash. We'll ask for your preferred account details.</li>
        </ul>

        <h2>5. Exchanges</h2>
        <p>Want a different size or colour? We're happy to exchange. Contact us within 14 days and we'll ship the replacement as soon as we receive the original item. If the new item costs more, we'll let you know the difference before shipping.</p>

        <h2>6. Damaged or Incorrect Items</h2>
        <p>If you receive a damaged, defective, or wrong item, contact us within <strong>48 hours</strong> of delivery with photos. We'll arrange an immediate replacement or full refund — including shipping charges — at no cost to you.</p>

        <h2>7. Late or Missing Refunds</h2>
        <p>If you haven't received your refund within the stated timeframe, first check with your bank or payment provider. Processing times vary. If the issue persists, email <strong>{{ $settings->supportEmail() }}</strong> and we'll investigate immediately.</p>

        <h2>8. Contact</h2>
        <p>Questions about returns or refunds? Reach us at <strong>{{ $settings->supportEmail() }}</strong> or call <strong>{{ $settings->supportPhone() }}</strong> ({{ $settings->supportHours() }}).</p>
    </section>
</div>
@endsection
