@extends('emails.layout')

@section('title', 'Reset your password')
@section('preheader', 'Reset your '.($brand['name'] ?? 'Bacha Stylo').' password — link expires in 60 minutes.')

@section('content')
    @php
        $firstName = trim(strtok((string) ($customer->name ?? ''), ' ')) ?: 'there';
    @endphp

    <p style="margin:0 0 6px; font-size:11px; font-weight:bold; letter-spacing:2px; text-transform:uppercase; color:#e81d25;">Account Security</p>
    <h1 class="h1" style="margin:0 0 14px; font-family:Georgia,'Times New Roman',serif; font-size:28px; line-height:1.2; color:#141414;">Reset your password</h1>
    <p style="margin:0 0 22px; font-size:15px; line-height:1.7; color:#3d3d3d;">
        Hi {{ $firstName }}, we received a request to reset the password for your {{ $brand['name'] }} account.
        Click the button below to choose a new password. This link expires in 60 minutes.
    </p>

    {{-- CTA --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 26px;">
        <tr>
            <td>
                <a href="{{ $resetUrl }}" class="btn" style="display:inline-block; background-color:#e81d25; color:#ffffff; font-size:12px; font-weight:bold; letter-spacing:1.5px; text-transform:uppercase; text-decoration:none; padding:14px 28px;">Reset Password</a>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 8px; font-size:13px; line-height:1.7; color:#6b6b6b;">
        If the button doesn&rsquo;t work, copy and paste this link into your browser:
    </p>
    <p style="margin:0 0 24px; font-size:13px; line-height:1.6; word-break:break-all;">
        <a href="{{ $resetUrl }}" style="color:#e81d25;">{{ $resetUrl }}</a>
    </p>

    <p style="margin:0; font-size:13px; line-height:1.7; color:#6b6b6b;">
        Didn&rsquo;t request this? You can safely ignore this email &mdash; your password will stay the same.
    </p>
@endsection
