@props(['value', 'title', 'subtitle'])
{{-- One payment method card; expects `method` in the surrounding Alpine scope. --}}
<label for="pm-{{ $value }}" class="flex gap-4 p-3 lg:p-5 border transition-all cursor-pointer group"
       :class="method === '{{ $value }}' ? 'border-brand-red bg-brand-red/[0.02]' : 'border-ink-10 bg-white hover:border-ink-30'">
    <div class="pt-0.5">
        <span class="relative flex aspect-square h-5 w-5 items-center justify-center rounded-full border border-border bg-ivory"
              :class="method === '{{ $value }}' ? 'border-brand-red' : ''">
            <input type="radio" name="payment_method" id="pm-{{ $value }}" value="{{ $value }}" x-model="method" class="peer absolute inset-0 h-full w-full cursor-pointer appearance-none rounded-full opacity-0">
            <span class="h-2.5 w-2.5 rounded-full bg-brand-red" x-show="method === '{{ $value }}'"></span>
        </span>
    </div>
    <div class="flex-1">
        <span class="label-shop cursor-pointer font-bold text-[13px] lg:text-sm uppercase tracking-wider block mb-1">{{ $title }}</span>
        <p class="text-xs text-ink-50 leading-relaxed">{{ $subtitle }}</p>
        <div x-cloak x-show="method === '{{ $value }}'" class="mt-4 pt-4 border-t border-brand-red/10 animate-fade-in">{{ $slot }}</div>
    </div>
</label>
