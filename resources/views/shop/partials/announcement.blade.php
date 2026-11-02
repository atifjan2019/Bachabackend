@php
    $rule = $settings->shippingRule();
    $items = [
        $rule['freeEnabled']
            ? ['icon' => 'truck', 'text' => 'Free shipping on orders of Rs. '.number_format($rule['threshold']).' or more']
            : ['icon' => 'truck', 'text' => 'Nationwide delivery across Pakistan'],
        ['icon' => 'zap', 'text' => 'Nationwide delivery in 2–5 working days'],
        ['icon' => 'credit-card', 'text' => 'Cash on Delivery · JazzCash · Easypaisa'],
        ['icon' => 'sparkles', 'text' => 'New drops every Thursday'],
    ];
@endphp
<div class="relative bg-brand-black text-white overflow-hidden border-b border-brand-red/40">
    <div class="absolute left-0 top-0 bottom-0 w-20 bg-gradient-to-r from-brand-black to-transparent z-10 pointer-events-none"></div>
    <div class="absolute right-0 top-0 bottom-0 w-20 bg-gradient-to-l from-brand-black to-transparent z-10 pointer-events-none"></div>
    <div class="flex animate-marquee whitespace-nowrap py-2.5">
        @for($r = 0; $r < 3; $r++)
            @foreach($items as $item)
                <span class="flex items-center gap-2 sm:gap-2.5 px-5 sm:px-8 text-[10px] sm:text-[11px] uppercase tracking-[0.18em] sm:tracking-[0.22em] font-medium">
                    <x-dynamic-component :component="'lucide-'.$item['icon']" class="h-3 w-3 sm:h-3.5 sm:w-3.5 text-brand-red shrink-0" stroke-width="2.5" />
                    <span class="text-white/90">{{ $item['text'] }}</span>
                    <span class="text-brand-red ml-1 sm:ml-2">◆</span>
                </span>
            @endforeach
        @endfor
    </div>
</div>
