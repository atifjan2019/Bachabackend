@extends('layouts.admin')
@section('title', 'Orders')
@section('content')

<div class="ph">
    <div>
        <h4>Orders</h4>
        <div class="ph-sub">{{ $orders->total() }} total orders</div>
    </div>
</div>

<div class="bcard">
    <div class="table-wrap">
        <table class="table table-stack mb-0">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                @php
                    $sc = match($order->status) {
                        'Pending'    => ['bg'=>'#fef3c7','color'=>'#92400e'],
                        'Paid'       => ['bg'=>'#dbeafe','color'=>'#1e40af'],
                        'Processing' => ['bg'=>'#e0e7ff','color'=>'#3730a3'],
                        'Shipped'    => ['bg'=>'#cffafe','color'=>'#155e75'],
                        'Delivered'  => ['bg'=>'#d1fae5','color'=>'#065f46'],
                        'Cancelled'  => ['bg'=>'#fee2e2','color'=>'#dc2626'],
                        default      => ['bg'=>'var(--surf2)','color'=>'var(--t2)'],
                    };
                @endphp
                <tr>
                    <td data-label="Order">
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="table-link">#{{ $order->id }}</a>
                    </td>
                    <td data-label="Customer">{{ $order->customer_name }}</td>
                    <td data-label="Phone" class="text-muted">{{ $order->customer_phone ?? '—' }}</td>
                    <td data-label="Amount" class="text-strong">Rs. {{ number_format($order->total_amount) }}</td>
                    <td data-label="Status">
                        <span class="status-badge" style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }};">{{ $order->status }}</span>
                    </td>
                    <td data-label="Date" class="text-muted">{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}</td>
                    <td data-label="Actions">
                        <div class="action-group">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-light btn-icon"><i class="mdi mdi-eye-outline"></i></a>
                            <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-sm btn-light btn-icon"><i class="mdi mdi-pencil-outline"></i></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="empty-state">
                        <i class="mdi mdi-cart-off"></i>
                        <strong>No orders yet</strong>
                        New purchases will appear here as they come in.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div class="card-footer">
        {{ $orders->links() }}
    </div>
    @endif
</div>
@endsection
