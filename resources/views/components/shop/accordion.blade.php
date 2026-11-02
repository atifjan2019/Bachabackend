@props(['title', 'open' => false, 'id' => null])
@php $id = $id ?: 'acc-'.\Illuminate\Support\Str::random(6); @endphp
<div x-data="{ open: {{ $open ? 'true' : 'false' }} }" {{ $attributes->class(['border-b border-border']) }}>
    <h3 class="flex">
        <button type="button" @click="open = !open" :aria-expanded="open" aria-controls="{{ $id }}"
                class="flex flex-1 items-center justify-between py-4 text-sm font-medium text-brand-black text-left transition">
            <span>{{ $title }}</span>
            <x-lucide-chevron-down class="h-4 w-4 shrink-0 text-muted transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
        </button>
    </h3>
    <div id="{{ $id }}" x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
         class="overflow-hidden text-sm text-muted">
        <div class="pb-4">{{ $slot }}</div>
    </div>
</div>
