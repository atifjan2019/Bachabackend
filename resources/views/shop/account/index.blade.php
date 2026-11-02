@extends('shop.layouts.app')
@section('title', 'My Account')
@section('robots', 'noindex, nofollow')

@php $first = explode(' ', trim($customer->name ?? ''))[0] ?: 'there'; @endphp

@section('content')
<div class="container-shop py-10 lg:py-14">
    <div class="text-center mb-10">
        <h1 class="font-display text-3xl sm:text-4xl">Assalam o Alaikum, {{ $first }}</h1>
        <p class="mt-2 text-sm text-muted">Welcome to your account.</p>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 max-w-4xl mx-auto">
        <div class="bg-ivory border border-border rounded-lg p-5 flex flex-col">
            <div class="h-10 w-10 rounded-full bg-cream border border-border flex items-center justify-center text-gold">
                <x-lucide-package class="h-6 w-6" />
            </div>
            <h3 class="font-display text-lg mt-3">Recent Orders</h3>
            <p class="text-sm text-muted mt-1 truncate">{{ $orderCount }} {{ $orderCount === 1 ? 'order' : 'orders' }}</p>
            <x-shop.button href="{{ route('shop.account.orders') }}" variant="outline" class="mt-auto w-fit">View all</x-shop.button>
        </div>
        <div class="bg-ivory border border-border rounded-lg p-5 flex flex-col">
            <div class="h-10 w-10 rounded-full bg-cream border border-border flex items-center justify-center text-gold">
                <x-lucide-circle-user class="h-6 w-6" />
            </div>
            <h3 class="font-display text-lg mt-3">Profile</h3>
            <p class="text-sm text-muted mt-1 truncate">{{ $customer->email }}</p>
            <x-shop.button href="{{ route('shop.account.profile') }}" variant="outline" class="mt-auto w-fit">Edit profile</x-shop.button>
        </div>
        <div class="bg-ivory border border-border rounded-lg p-5 flex flex-col">
            <div class="h-10 w-10 rounded-full bg-cream border border-border flex items-center justify-center text-gold">
                <x-lucide-log-out class="h-5 w-5" />
            </div>
            <h3 class="font-display text-lg mt-3">Sign out</h3>
            <p class="text-sm text-muted mt-1">End your session on this device.</p>
            <form method="post" action="{{ route('shop.logout') }}" class="mt-auto">
                @csrf
                <x-shop.button type="submit" variant="outline" class="w-fit">Logout</x-shop.button>
            </form>
        </div>
    </div>
</div>
@endsection
