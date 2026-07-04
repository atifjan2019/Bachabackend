@extends('emails.layout')

@section('title', 'Order Confirmed')
@section('preheader', 'Thank you! Your order '.$order->ref.' has been received and is being prepared.')

@section('content')
    @php
        $firstName = trim(strtok((string) ($order->customer_name ?? ''), ' ')) ?: 'there';

        $addressLine = $order->shipping_address;
        if (! empty($order->city)) { $addressLine .= ', ' . $order->city; }
        if (! empty($order->country)) { $addressLine .= ', ' . $order->country; }

        $helpLine = 'Questions about your order? Just reply to this email';
        if (! empty($brand['phone'])) { $helpLine .= ' or call us at ' . $brand['phone']; }
        $helpLine .= '.';
    @endphp

    <p style="margin:0 0 6px; font-size:11px; font-weight:bold; letter-spacing:2px; text-transform:uppercase; color:#e81d25;">Order Confirmed</p>
    <h1 class="h1" style="margin:0 0 14px; font-family:Georgia,'Times New Roman',serif; font-size:28px; line-height:1.2; color:#141414;">Thank you, {{ $firstName }}.</h1>
    <p style="margin:0 0 24px; font-size:15px; line-height:1.7; color:#3d3d3d;">
        We've received your order and it's being prepared with care. We'll send you another update as soon as it ships.
    </p>

    {{-- Order meta --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f6f6f7; margin:0 0 26px;">
        <tr>
            <td class="stack" style="padding:18px 20px;">
                <span style="font-size:11px; letter-spacing:1px; text-transform:uppercase; color:#6b6b6b;">Order Number</span><br>
                <span style="font-size:18px; font-weight:bold; color:#141414;">{{ $order->ref }}</span>
            </td>
            <td align="right" class="stack stack-r" style="padding:18px 20px;">
                <span style="font-size:11px; letter-spacing:1px; text-transform:uppercase; color:#6b6b6b;">Status</span><br>
                <span style="display:inline-block; margin-top:4px; background-color:#141414; color:#ffffff; font-size:11px; font-weight:bold; letter-spacing:1px; text-transform:uppercase; padding:5px 12px;">{{ $order->status }}</span>
            </td>
        </tr>
    </table>

    @include('emails.partials.order-details')

    {{-- Shipping --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:26px 0 0;">
        <tr>
            <td style="padding:18px 20px; border:1px solid #e6e6e8;">
                <p style="margin:0 0 8px; font-size:11px; font-weight:bold; letter-spacing:1px; text-transform:uppercase; color:#e81d25;">Shipping To</p>
                <p style="margin:0; font-size:14px; line-height:1.7; color:#3d3d3d;">
                    {{ $order->customer_name }}<br>
                    {{ $addressLine }}
                    @if(! empty($order->customer_phone))
                        <br>{{ $order->customer_phone }}
                    @endif
                </p>
                <p style="margin:12px 0 0; font-size:13px; color:#6b6b6b;">Payment: {{ $order->payment_method }}</p>
            </td>
        </tr>
    </table>

    {{-- CTA --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:28px 0 0;">
        <tr>
            <td>
                <a href="{{ $brand['site'] }}/products" class="btn" style="display:inline-block; background-color:#e81d25; color:#ffffff; font-size:12px; font-weight:bold; letter-spacing:1.5px; text-transform:uppercase; text-decoration:none; padding:14px 28px;">Continue Shopping</a>
                @if(! empty($brand['whatsapp']))
                    <a href="https://wa.me/{{ $brand['whatsapp'] }}" class="btn btn-ghost" style="display:inline-block; margin-left:8px; border:2px solid #141414; color:#141414; font-size:12px; font-weight:bold; letter-spacing:1.5px; text-transform:uppercase; text-decoration:none; padding:12px 26px;">Need Help?</a>
                @endif
            </td>
        </tr>
    </table>

    <p style="margin:26px 0 0; font-size:13px; line-height:1.7; color:#6b6b6b;">{{ $helpLine }}</p>
@endsection
