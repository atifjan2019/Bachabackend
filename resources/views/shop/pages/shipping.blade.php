@extends('shop.layouts.app')

@section('title', 'Shipping & Delivery Policy')
@section('description', 'How Bacha Stylo ships across Pakistan: delivery charges, free shipping, delivery times, Cash on Delivery, order tracking and what to do if a parcel is late or damaged.')
@section('canonical', $settings->canonical('/shipping'))

@section('content')
<div class="flex flex-col">
    <x-shop.page-hero eyebrow="Help" title="Shipping & Delivery" subtitle="Nationwide delivery across Pakistan" />

    <section class="container-shop py-16 lg:py-20 max-w-3xl prose-policy">
        <h2>1. Delivery Charges</h2>
        @if($shipping['freeEnabled'])
            <p>Delivery costs <strong>{{ pkr($shipping['fee']) }}</strong> per order. Orders of <strong>{{ pkr($shipping['threshold']) }}</strong> or more ship <strong>free</strong> anywhere in Pakistan.</p>
        @else
            <p>Delivery costs <strong>{{ pkr($shipping['fee']) }}</strong> per order, anywhere in Pakistan.</p>
        @endif
        <p>The exact charge is shown in your cart and at checkout before you place the order. There are no hidden fees.</p>

        @if($policy !== [])
            <h2>2. Our Delivery Policy</h2>
            @foreach($policy as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        @endif

        <h2>{{ $policy !== [] ? 3 : 2 }}. Delivery Times</h2>
        <ul>
            <li><strong>Major cities</strong> (Karachi, Lahore, Islamabad, Rawalpindi, Peshawar, Faisalabad, Multan): usually <strong>2–4 working days</strong> after dispatch.</li>
            <li><strong>Other cities and towns</strong>: usually <strong>3–6 working days</strong> after dispatch.</li>
            <li><strong>Remote and northern areas</strong>: up to <strong>7–10 working days</strong>, depending on courier coverage.</li>
        </ul>
        <p>Orders are dispatched within 1–2 working days of confirmation. Made-to-order and customised pieces take longer, and we tell you the expected time before stitching begins.</p>

        <h2>{{ $policy !== [] ? 4 : 3 }}. Cash on Delivery</h2>
        <p>Cash on Delivery is available across Pakistan. Please keep the exact amount ready for the courier. For bank transfer, JazzCash and Easypaisa orders, dispatch starts once your payment screenshot is verified.</p>

        <h2>{{ $policy !== [] ? 5 : 4 }}. Order Confirmation &amp; Tracking</h2>
        <p>You receive an order confirmation by email as soon as you place an order, and a status update when it ships. Need the courier tracking number? Message us on WhatsApp at <strong>{{ $settings->supportPhone() }}</strong> with your order number.</p>

        <h2>{{ $policy !== [] ? 6 : 5 }}. Late, Lost or Damaged Parcels</h2>
        <p>If your parcel has not arrived within the times above, contact us and we will trace it with the courier. If an item arrives damaged or incorrect, tell us within <strong>48 hours</strong> of delivery with photos and we will arrange a replacement or refund, including delivery charges. See our <a href="{{ route('shop.refund') }}" class="underline hover:text-brand-black">Refund &amp; Return Policy</a> for returns and exchanges.</p>

        <h2>{{ $policy !== [] ? 7 : 6 }}. Contact</h2>
        <p>Questions about delivery? Email <strong>{{ $settings->supportEmail() }}</strong> or call <strong>{{ $settings->supportPhone() }}</strong> ({{ $settings->supportHours() }}).</p>
    </section>
</div>
@endsection
