@extends('shop.layouts.app')
@section('title', 'Your Orders')
@section('robots', 'noindex, nofollow')

@section('content')
<div class="container-shop py-10 lg:py-14">
    <div class="flex items-end justify-between flex-wrap gap-3 mb-8">
        <div>
            <h1 class="font-display text-3xl sm:text-4xl">Your Orders</h1>
        </div>
        <x-shop.button href="{{ route('shop.account') }}" variant="outline">Back to account</x-shop.button>
    </div>

    @if($orders->isEmpty())
        <div class="py-20 text-center border border-dashed border-ink-10 bg-ink-5/30">
            <x-lucide-package class="h-12 w-12 text-ink-10 mx-auto mb-4" />
            <h3 class="font-display text-xl mb-2">No orders found</h3>
            <p class="text-ink-50 mb-8 max-w-xs mx-auto">Once you place your first order, you'll be able to track it here.</p>
            <x-shop.button href="{{ route('shop.products') }}">Start Shopping</x-shop.button>
        </div>
    @else
        <div class="hidden lg:block bg-white border border-ink-10 overflow-hidden shadow-sm">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-ink-5 text-ink-50 border-b border-ink-10">
                        <th class="text-left px-6 py-4 font-bold uppercase tracking-widest text-[10px]">Order</th>
                        <th class="text-left px-6 py-4 font-bold uppercase tracking-widest text-[10px]">Date</th>
                        <th class="text-left px-6 py-4 font-bold uppercase tracking-widest text-[10px]">Items</th>
                        <th class="text-left px-6 py-4 font-bold uppercase tracking-widest text-[10px]">Status</th>
                        <th class="text-left px-6 py-4 font-bold uppercase tracking-widest text-[10px]">Total</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-10">
                    @foreach($orders as $o)
                        @php $items = is_array($o->items) ? $o->items : []; $names = implode(', ', array_column($items, 'name')); @endphp
                        <tr class="group hover:bg-ink-5/50 transition-colors">
                            <td class="px-6 py-6 font-bold text-brand-black">#{{ $o->ref }}</td>
                            <td class="px-6 py-6 text-ink-50">{{ $o->created_at?->format('d M Y') }}</td>
                            <td class="px-6 py-6">
                                <div class="max-w-[240px]">
                                    <p class="font-bold text-brand-black truncate">{{ $names }}</p>
                                    <p class="text-[10px] text-ink-30 uppercase mt-0.5 tracking-wider">{{ count($items) }} {{ count($items) === 1 ? 'item' : 'items' }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <x-shop.account-status-badge :status="$o->status" class="uppercase tracking-widest text-[9px] px-2 py-1 font-bold" />
                            </td>
                            <td class="px-6 py-6 font-display text-xl font-black">{{ pkr($o->total_amount) }}</td>
                            <td class="px-6 py-6 text-right">
                                <x-shop.button href="{{ route('shop.account.order', $o->reference ?: $o->id) }}" size="sm" variant="outline"
                                    class="h-10 px-6 hover:bg-brand-red hover:text-white hover:border-brand-red transition-all duration-300 font-bold uppercase tracking-widest text-[10px]">Details</x-shop.button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="lg:hidden space-y-4">
            @foreach($orders as $o)
                @php $items = is_array($o->items) ? $o->items : []; $names = implode(', ', array_column($items, 'name')); @endphp
                <div class="bg-white border border-ink-10 p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <span class="font-bold text-brand-black">#{{ $o->ref }}</span>
                        <x-shop.account-status-badge :status="$o->status" class="uppercase tracking-widest text-[9px] font-bold" />
                    </div>
                    <div class="mb-4">
                        <p class="text-[10px] text-ink-30 uppercase tracking-widest mb-1">Items</p>
                        <p class="text-sm font-bold text-brand-black line-clamp-1">{{ $names }}</p>
                        <p class="text-[10px] text-ink-30 uppercase mt-0.5">{{ count($items) }} {{ count($items) === 1 ? 'item' : 'items' }}</p>
                    </div>
                    <div class="flex items-end justify-between border-t border-ink-5 pt-4">
                        <div>
                            <p class="text-[10px] text-ink-30 uppercase tracking-widest mb-1">Placed on</p>
                            <p class="text-xs text-brand-black font-medium">{{ $o->created_at?->format('d M Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] text-ink-30 uppercase tracking-widest mb-0.5">Total</p>
                            <p class="font-display text-lg font-black leading-none">{{ pkr($o->total_amount) }}</p>
                        </div>
                    </div>
                    <x-shop.button href="{{ route('shop.account.order', $o->reference ?: $o->id) }}" variant="outline" class="w-full mt-5 h-12 uppercase tracking-widest text-[10px] font-bold">View Full Details</x-shop.button>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
