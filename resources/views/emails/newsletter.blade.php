@extends('emails.layout')

@section('title', $subject ?? 'Newsletter')
@section('preheader', $subject ?? ('An update from '.($brand['name'] ?? 'Bacha Stylo')))

@section('content')
    <div style="font-size:15px; line-height:1.7; color:#3d3d3d;">
        {!! $bodyContent !!}
    </div>

    {{-- Subscription footer note --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:30px 0 0; border-top:1px solid #e6e6e8;">
        <tr>
            <td style="padding:18px 0 0; font-size:12px; line-height:1.7; color:#9a9a9a;">
                You're receiving this email because you subscribed to the {{ $brand['name'] ?? 'Bacha Stylo' }} newsletter.
                <a href="{{ $unsubscribeUrl }}" style="color:#e81d25;">Unsubscribe</a>
            </td>
        </tr>
    </table>
@endsection
