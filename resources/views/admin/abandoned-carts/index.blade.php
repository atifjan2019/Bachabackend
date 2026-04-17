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
                    <th>EMAIL</th>
                    <th>PHONE</th>
                    <th>ITEMS</th>
                    <th>DATE</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($carts as $cart)
                @php
                    $items = is_string($cart->cart_data) ? json_decode($cart->cart_data, true) : [];
                    $count = is_array($items) ? count($items) : 0;
                @endphp
                <tr>
                    <td data-label="Email" class="text-strong">{{ $cart->email }}</td>
                    <td data-label="Phone" class="text-muted">{{ $cart->phone ?? '—' }}</td>
                    <td data-label="Items">
                        <span class="soft-badge">{{ $count }} item(s)</span>
                    </td>
                    <td data-label="Date" class="text-muted">{{ \Carbon\Carbon::parse($cart->created_at)->format('d M Y, h:i A') }}</td>
                    <td data-label="Actions">
                        <form action="{{ route('admin.abandoned-carts.destroy', $cart->id) }}" method="POST" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn-ghost-del"><i class="mdi mdi-trash-can-outline"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="empty-state">
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
