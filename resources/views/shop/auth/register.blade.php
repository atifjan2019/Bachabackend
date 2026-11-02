@extends('shop.layouts.app')
@section('title', 'Create account')
@section('description', 'Create a Bacha Stylo account to save addresses and track orders.')
@section('robots', 'noindex, follow')

@section('content')
<x-shop.auth-card title="Create account" subtitle="Save addresses, track orders, keep your favourites.">
    <form method="post" action="{{ route('shop.register.store') }}" class="space-y-4" novalidate x-data="{ s: false }" x-on:submit="s = true">
        @csrf
        <div>
            <x-shop.label for="name">Full name</x-shop.label>
            <x-shop.input id="name" name="name" class="mt-1.5" placeholder="Your name" autocomplete="name" autofocus />
            <x-shop.field-error name="name" />
        </div>
        <div>
            <x-shop.label for="email">Email</x-shop.label>
            <x-shop.input id="email" name="email" type="email" class="mt-1.5" placeholder="you@example.com" autocomplete="email" />
            <x-shop.field-error name="email" />
        </div>
        <div>
            <x-shop.label for="password">Password</x-shop.label>
            <x-shop.input id="password" name="password" type="password" class="mt-1.5" placeholder="At least 6 characters" autocomplete="new-password" />
            <x-shop.field-error name="password" />
        </div>
        <div>
            <x-shop.label for="password_confirmation">Confirm password</x-shop.label>
            <x-shop.input id="password_confirmation" name="password_confirmation" type="password" class="mt-1.5" autocomplete="new-password" />
            <x-shop.field-error name="password_confirmation" />
        </div>
        <x-shop.button type="submit" class="w-full" ::disabled="s">
            <span x-text="s ? 'Creating account...' : 'Create account'">Create account</span>
        </x-shop.button>
    </form>
    <x-slot:footer>
        Already with us? <a href="{{ route('shop.login') }}" class="text-brand-red hover:underline">Sign in</a>
    </x-slot:footer>
</x-shop.auth-card>
@endsection
