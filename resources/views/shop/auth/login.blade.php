@extends('shop.layouts.app')
@section('title', 'Login')
@section('description', 'Sign in to your Bacha Stylo account to see your orders and saved addresses.')
@section('robots', 'noindex, follow')

@section('content')
<x-shop.auth-card title="Welcome back" subtitle="Login to see your orders and save addresses.">
    <form method="post" action="{{ route('shop.login.store') }}" class="space-y-4" novalidate x-data="{ s: false }" x-on:submit="s = true">
        @csrf
        <div>
            <x-shop.label for="email">Email</x-shop.label>
            <x-shop.input id="email" name="email" type="email" class="mt-1.5" placeholder="you@example.com" autocomplete="email" autofocus />
            <x-shop.field-error name="email" />
        </div>
        <div>
            <div class="flex items-center justify-between">
                <x-shop.label for="password">Password</x-shop.label>
                <a href="{{ route('shop.password.request') }}" class="text-xs text-brand-red hover:underline">Forgot password?</a>
            </div>
            <x-shop.input id="password" name="password" type="password" class="mt-1.5" placeholder="At least 6 characters" autocomplete="current-password" />
            <x-shop.field-error name="password" />
        </div>
        <label class="flex items-center gap-2 text-xs text-muted">
            <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-border accent-brand-red" @checked(old('remember'))>
            Keep me signed in
        </label>
        <x-shop.button type="submit" class="w-full" ::disabled="s">
            <span x-text="s ? 'Signing in...' : 'Login'">Login</span>
        </x-shop.button>
    </form>
    <x-slot:footer>
        New to Bacha Stylo? <a href="{{ route('shop.register') }}" class="text-brand-red hover:underline">Create an account</a>
    </x-slot:footer>
</x-shop.auth-card>
@endsection
