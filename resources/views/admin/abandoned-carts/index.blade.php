@extends('layouts.admin')
@section('title', 'Abandoned Carts')
@section('content')

<div class="ph">
    <div>
        <h4>Abandoned Carts</h4>
        <div class="ph-sub">{{ $carts->total() }} records</div>
    </div>
</div>

<div class="bcard">
    <div class="table-wrap">
        <table class="table table-stack mb-0">
            <thead>
                <tr>
                    <th>CUSTOMER</th>
                    <th>PHONE</th>
                    <th>ITEMS</th>
                    <th>TOTAL</th>
                    <th>DATE</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($carts as $cart)
                @php
                    // cart_data is cast to an array on the model.
                    $items = is_array($cart->cart_data) ? $cart->cart_data : (json_decode((string) $cart->cart_data, true) ?: []);
                    $count = is_array($items) ? array_sum(array_map(fn($i) => (int) ($i['quantity'] ?? 1), $items)) : 0;
                    $names = is_array($items) ? array_slice(array_map(fn($i) => $i['name'] ?? 'Item', $items), 0, 3) : [];
                @endphp
                <tr>
                    <td data-label="Customer" class="text-strong">
                        {{ $cart->name ?: 'Guest' }}
                        <div class="entity-meta">{{ $cart->email }}</div>
                    </td>
                    <td data-label="Phone" class="text-muted">{{ $cart->phone ?? '—' }}</td>
                    <td data-label="Items">
                        <span class="soft-badge">{{ $count }} item(s)</span>
                        @if(!empty($names))<div class="entity-meta">{{ implode(', ', $names) }}{{ count($items) > 3 ? '…' : '' }}</div>@endif
                    </td>
                    <td data-label="Total" class="text-strong text-nowrap">Rs. {{ number_format((float) $cart->total) }}</td>
                    <td data-label="Date" class="text-muted">{{ \Carbon\Carbon::parse($cart->updated_at)->format('d M Y, h:i A') }}</td>
                    <td data-label="Actions">
                        <form action="{{ route('admin.abandoned-carts.destroy', $cart->id) }}" method="POST" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn-ghost-del"><i class="mdi mdi-trash-can-outline"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="empty-state">
                        <i class="mdi mdi-cart-remove"></i>
                        <strong>No abandoned carts recorded</strong>
                        Recovery opportunities will appear here when carts are left behind.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($carts->hasPages())
    <div class="card-footer">{{ $carts->links() }}</div>
    @endif
</div>
@endsection
