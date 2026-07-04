@extends('emails.layout')

@section('title', 'New contact message')
@section('preheader', 'New message from '.$contact->name.' — '.$contact->subject)

@section('content')
    <p style="margin:0 0 6px; font-size:11px; font-weight:bold; letter-spacing:2px; text-transform:uppercase; color:#e81d25;">Contact Form</p>
    <h1 class="h1" style="margin:0 0 14px; font-family:Georgia,'Times New Roman',serif; font-size:28px; line-height:1.2; color:#141414;">New message received</h1>
    <p style="margin:0 0 24px; font-size:15px; line-height:1.7; color:#3d3d3d;">
        Someone reached out through the website contact form. Reply to this email to respond to them directly.
    </p>

    {{-- Sender --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f6f6f7; margin:0 0 20px;">
        <tr>
            <td style="padding:18px 20px;">
                <p style="margin:0 0 4px; font-size:11px; letter-spacing:1px; text-transform:uppercase; color:#6b6b6b;">From</p>
                <p style="margin:0 0 12px; font-size:16px; font-weight:bold; color:#141414;">{{ $contact->name }}</p>
                <p style="margin:0 0 4px; font-size:11px; letter-spacing:1px; text-transform:uppercase; color:#6b6b6b;">Email</p>
                <p style="margin:0 0 12px; font-size:14px; color:#141414;"><a href="mailto:{{ $contact->email }}" style="color:#e81d25; text-decoration:none;">{{ $contact->email }}</a></p>
                <p style="margin:0 0 4px; font-size:11px; letter-spacing:1px; text-transform:uppercase; color:#6b6b6b;">Subject</p>
                <p style="margin:0; font-size:15px; font-weight:bold; color:#141414;">{{ $contact->subject }}</p>
            </td>
        </tr>
    </table>

    {{-- Message --}}
    <p style="margin:0 0 6px; font-size:11px; letter-spacing:1px; text-transform:uppercase; color:#6b6b6b;">Message</p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e6e6e8; margin:0 0 24px;">
        <tr>
            <td style="padding:18px 20px; font-size:15px; line-height:1.7; color:#141414; white-space:pre-line;">{{ $contact->message }}</td>
        </tr>
    </table>

    {{-- CTA --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:8px 0 0;">
        <tr>
            <td>
                <a href="mailto:{{ $contact->email }}?subject=RE: {{ rawurlencode($contact->subject) }}" class="btn" style="display:inline-block; background-color:#e81d25; color:#ffffff; font-size:12px; font-weight:bold; letter-spacing:1.5px; text-transform:uppercase; text-decoration:none; padding:14px 28px;">Reply to {{ \Illuminate\Support\Str::before($contact->name, ' ') }}</a>
            </td>
        </tr>
    </table>
@endsection
