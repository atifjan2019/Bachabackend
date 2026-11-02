@props(['value', 'min' => '1', 'max' => '99', 'decrement', 'increment'])
{{-- Alpine expressions: value/min/max evaluate in the surrounding scope; decrement/increment run on click. --}}
<div {{ $attributes->class(['inline-flex items-center border border-border rounded-md bg-ivory']) }}>
    <button type="button" aria-label="Decrease quantity" class="h-10 w-10 flex items-center justify-center text-brand-black disabled:opacity-40"
            @click="{{ $decrement }}" :disabled="({{ $value }}) <= ({{ $min }})">
        <x-lucide-minus class="h-4 w-4" />
    </button>
    <span class="w-10 text-center text-sm font-medium" x-text="{{ $value }}"></span>
    <button type="button" aria-label="Increase quantity" class="h-10 w-10 flex items-center justify-center text-brand-black disabled:opacity-40"
            @click="{{ $increment }}" :disabled="({{ $value }}) >= ({{ $max }})">
        <x-lucide-plus class="h-4 w-4" />
    </button>
</div>
