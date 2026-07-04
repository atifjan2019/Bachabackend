@extends('emails.layout')

@section('title', 'New Order')
@section('preheader', 'New order '.$order->ref.' for Rs. '.number_format((float)$order->total_amount))

@section('content')
    @php
        $contactLine = $order->customer_email;
        if (! empty($order->customer_phone)) { $contactLine .= ' · ' . $order->customer_phone; }

        $addressLine = $order->shipping_address;
        if (! empty($order->city)) { $addressLine .= ', ' . $order->city; }
        if (! empty($order->country)) { $addressLine .= ', ' . $order->country; }
    @endphp

    <p style="margin:0 0 6px; font-size:11px; font-weight:bold; letter-spacing:2px; text-transform:uppercase; color:#e81d25;">New Order</p>
    <h1 class="h1" style="margin:0 0 14px; font-family:Georgia,'Times New Roman',serif; font-size:28px; line-height:1.2; color:#141414;">Order {{ $order->ref }} received</h1>
    <p style="margin:0 0 24px; font-size:15px; line-height:1.7; color:#3d3d3d;">
        A new order has just been placed. Review the details below and process it from the dashboard.
    </p>

    {{-- Customer --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f6f6f7; margin:0 0 26px;">
        <tr>
            <td style="padding:18px 20px;">
                <p style="margin:0 0 10px; font-size:11px; font-weight:bold; letter-spacing:1px; text-transform:uppercase; color:#e81d25;">Customer</p>
                <p style="margin:0; font-size:14px; line-height:1.8; color:#3d3d3d;">
                    <strong style="color:#141414;">{{ $order->customer_name }}</strong><br>
                    {{ $contactLine }}<br>
                    {{ $addressLine }}
                </p>
                <p style="margin:12px 0 0; font-size:13px; color:#6b6b6b;">
                    Payment: <strong style="color:#141414;">{{ $order->payment_method }}</strong> &nbsp;&middot;&nbsp; Status: <strong style="color:#141414;">{{ $order->status }}</strong>
                </p>
            </td>
        </tr>
    </table>

    {{-- Payment receipt (Bank Transfer / EasyPaisa / JazzCash) --}}
    @if(!empty($order->payment_receipt))
        @php $isPdf = \Illuminate\Support\Str::endsWith(strtolower($order->payment_receipt), '.pdf'); @endphp
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f6f6f7; margin:0 0 26px;">
            <tr>
                <td style="padding:18px 20px;">
                    <p style="margin:0 0 10px; font-size:11px; font-weight:bold; letter-spacing:1px; text-transform:uppercase; color:#e81d25;">Payment Receipt</p>
                    @unless($isPdf)
                        <a href="{{ $order->payment_receipt }}" target="_blank" style="text-decoration:none;">
                            <img src="{{ $order->payment_receipt }}" alt="Payment receipt" style="display:block; max-width:100%; max-height:320px; border:1px solid #e6e6e8; border-radius:6px; margin:0 0 10px;">
                        </a>
                    @endunless
                    <a href="{{ $order->payment_receipt }}" target="_blank" style="font-size:13px; color:#e81d25; text-decoration:underline;">View / download full receipt</a>
                </td>
            </tr>
        </table>
    @endif

    @include('emails.partials.order-details')

    {{-- CTA --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:28px 0 0;">
        <tr>
            <td>
                <a href="{{ $brand['admin_url'] }}/{{ $order->id }}" class="btn" style="display:inline-block; background-color:#141414; color:#ffffff; font-size:12px; font-weight:bold; letter-spacing:1.5px; text-transform:uppercase; text-decoration:none; padding:14px 28px;">View in Dashboard</a>
            </td>
        </tr>
    </table>
@endsection
