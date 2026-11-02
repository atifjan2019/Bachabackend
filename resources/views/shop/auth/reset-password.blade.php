@extends('shop.layouts.app')
@section('title', 'Set a new password')
@section('description', 'Choose a new password for your Bacha Stylo account.')
@section('robots', 'noindex, nofollow')

@php $invalidLink = $token === '' || $email === ''; @endphp

@section('content')
<x-shop.auth-card title="Set a new password" :subtitle="$invalidLink ? 'This reset link is incomplete.' : 'For '.$email">
    @if($invalidLink)
        <div class="space-y-4 text-center">
            <p class="text-sm leading-relaxed text-ink-70">This password reset link is invalid or incomplete. Please request a new one.</p>
            <x-shop.button href="{{ route('shop.password.request') }}" class="w-full">Request a new link</x-shop.button>
        </div>
    @else
        <form method="post" action="{{ route('shop.password.update') }}" class="space-y-4" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">
            <x-shop.field-error name="token" class="text-center" />
            <x-shop.field-error name="email" class="text-center" />
            <div>
                <x-shop.label for="password">New password</x-shop.label>
                <x-shop.input id="password" name="password" type="password" class="mt-1.5" placeholder="At least 6 characters" autocomplete="new-password" autofocus />
                <x-shop.field-error name="password" />
            </div>
            <div>
                <x-shop.label for="password_confirmation">Confirm password</x-shop.label>
                <x-shop.input id="password_confirmation" name="password_confirmation" type="password" class="mt-1.5" placeholder="Re-enter your password" autocomplete="new-password" />
                <x-shop.field-error name="password_confirmation" />
            </div>
            <x-shop.button type="submit" class="w-full" x-data="{ s: false }" x-on:click="s = true" ::disabled="s">
                <span x-text="s ? 'Updating...' : 'Reset password'">Reset password</span>
            </x-shop.button>
        </form>
    @endif
    <x-slot:footer>
        <a href="{{ route('shop.login') }}" class="text-brand-red hover:underline">Back to login</a>
    </x-slot:footer>
</x-shop.auth-card>
@endsection
