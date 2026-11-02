@extends('shop.layouts.app')
@section('title', 'Your Profile')
@section('robots', 'noindex, nofollow')

@section('content')
<div class="container-shop py-10 lg:py-14">
    <div class="max-w-xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="font-display text-3xl sm:text-4xl">Your Profile</h1>
            <p class="text-sm text-muted mt-1">Keep your delivery details up to date.</p>
        </div>

        <form method="post" action="{{ route('shop.account.profile.update') }}" class="bg-ivory border border-border rounded-lg p-5 lg:p-6 space-y-4" novalidate>
            @csrf
            @method('PUT')
            <div>
                <x-shop.label for="name">Full name</x-shop.label>
                <x-shop.input id="name" name="name" :value="$customer->name" class="mt-1.5" autocomplete="name" />
                <x-shop.field-error name="name" />
            </div>
            <div>
                <x-shop.label for="email">Email</x-shop.label>
                <input id="email" type="email" value="{{ $customer->email }}" disabled class="input-shop mt-1.5">
                <p class="text-xs text-muted mt-1">Email cannot be changed in this release.</p>
            </div>
            <div>
                <x-shop.label for="phone">Phone</x-shop.label>
                <x-shop.input id="phone" name="phone" type="tel" :value="$customer->phone" class="mt-1.5" placeholder="03XXXXXXXXX" autocomplete="tel" />
                <x-shop.field-error name="phone" />
            </div>
            <div>
                <x-shop.label for="address">Default address</x-shop.label>
                <x-shop.textarea id="address" name="address" :value="$customer->address" class="mt-1.5" autocomplete="street-address" />
                <x-shop.field-error name="address" />
            </div>
            <div class="flex gap-3 pt-2">
                <x-shop.button type="submit">Save changes</x-shop.button>
                <x-shop.button href="{{ route('shop.account') }}" variant="outline">Cancel</x-shop.button>
            </div>
        </form>
    </div>
</div>
@endsection
