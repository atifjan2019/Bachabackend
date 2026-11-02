@extends('shop.layouts.app')
@section('title', 'Forgot password')
@section('description', 'Request a password reset link for your Bacha Stylo account.')
@section('robots', 'noindex, follow')

@php $sent = (bool) session('sent'); @endphp

@section('content')
<x-shop.auth-card title="Forgot password?" :subtitle="$sent ? 'Check your inbox for the reset link.' : 'Enter your email and we\'ll send you a reset link.'">
    @if($sent)
        <div class="space-y-4 text-center">
            <p class="text-sm leading-relaxed text-ink-70">If an account exists for that email, a password reset link is on its way. The link expires in 60 minutes.</p>
            <x-shop.button href="{{ route('shop.login') }}" class="w-full">Back to login</x-shop.button>
        </div>
    @else
        <form method="post" action="{{ route('shop.password.email') }}" class="space-y-4" novalidate>
            @csrf
            <div>
                <x-shop.label for="email">Email</x-shop.label>
                <x-shop.input id="email" name="email" type="email" class="mt-1.5" placeholder="you@example.com" autocomplete="email" autofocus />
                <x-shop.field-error name="email" />
            </div>
            <x-shop.button type="submit" class="w-full" x-data="{ s: false }" x-on:click="s = true" ::disabled="s">
                <span x-text="s ? 'Sending...' : 'Send reset link'">Send reset link</span>
            </x-shop.button>
        </form>
    @endif
    <x-slot:footer>
        Remembered it? <a href="{{ route('shop.login') }}" class="text-brand-red hover:underline">Back to login</a>
    </x-slot:footer>
</x-shop.auth-card>
@endsection
