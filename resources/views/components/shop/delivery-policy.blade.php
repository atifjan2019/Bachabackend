@php
    $rule = $settings->shippingRule();
    $paragraphs = $settings->deliveryPolicyParagraphs();
@endphp
<div {{ $attributes->class(['bg-white border border-ink-10 p-4 sm:p-5 text-sm']) }}>
    <h4 class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.18em] text-ink-50 mb-3">
        <x-lucide-truck class="h-3.5 w-3.5 text-brand-red" stroke-width="2.5" />
        Delivery
    </h4>
    <p class="text-brand-black">
        @if($rule['freeEnabled'])
            Delivery charge {{ pkr($rule['fee']) }}. Free on orders of {{ pkr($rule['threshold']) }} or more.
        @else
            Delivery charge {{ pkr($rule['fee']) }} on every order.
        @endif
    </p>
    @if($paragraphs)
        <div class="mt-2 space-y-1.5 text-xs text-muted leading-relaxed">
            @foreach($paragraphs as $p)
                <p>{{ $p }}</p>
            @endforeach
        </div>
    @endif
</div>
