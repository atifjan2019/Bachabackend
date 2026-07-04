@extends('emails.layout')

@section('title', 'A message about your order')
@section('preheader', 'A message about your order '.$order->ref)

@section('content')
    @php
        $firstName = trim(strtok((string) ($order->customer_name ?? ''), ' ')) ?: 'there';

        $helpLine = 'Questions? Just reply to this email';
        if (! empty($brand['phone'])) { $helpLine .= ' or call us at ' . $brand['phone']; }
        $helpLine .= '.';
    @endphp

    <p style="margin:0 0 6px; font-size:11px; font-weight:bold; letter-spacing:2px; text-transform:uppercase; color:#e81d25;">Order Message</p>
    <h1 class="h1" style="margin:0 0 14px; font-family:Georgia,'Times New Roman',serif; font-size:28px; line-height:1.2; color:#141414;">A message about your order</h1>
    <p style="margin:0 0 20px; font-size:15px; line-height:1.7; color:#3d3d3d;">
        Hi {{ $firstName }}, our team has added a note regarding your order <strong>{{ $order->ref }}</strong>:
    </p>

    {{-- The admin comment --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f6f6f7; border-left:3px solid #e81d25; margin:0 0 26px;">
        <tr>
            <td style="padding:18px 20px; font-size:15px; line-height:1.7; color:#141414; white-space:pre-line;">{{ $comment }}</td>
        </tr>
    </table>

    <p style="margin:0 0 24px; font-size:15px; line-height:1.7; color:#3d3d3d;">
        Please reply to this email or contact us if you need to respond or provide any additional information.
    </p>

    {{-- CTA --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:8px 0 0;">
        <tr>
            <td>
                @if(! empty($brand['whatsapp']))
                    <a href="https://wa.me/{{ $brand['whatsapp'] }}" class="btn" style="display:inline-block; background-color:#e81d25; color:#ffffff; font-size:12px; font-weight:bold; letter-spacing:1.5px; text-transform:uppercase; text-decoration:none; padding:14px 28px;">Contact Us</a>
                @endif
            </td>
        </tr>
    </table>

    <p style="margin:26px 0 0; font-size:13px; line-height:1.7; color:#6b6b6b;">{{ $helpLine }}</p>
@endsection
