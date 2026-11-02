@props(['status'])
@php
    $key = strtolower(trim((string) $status));
    $tone = \App\Http\Controllers\Shop\AccountController::STATUS_TONE[$key] ?? 'neutral';
@endphp
<x-shop.badge :tone="$tone" {{ $attributes }}>{{ $key ?: 'pending' }}</x-shop.badge>
