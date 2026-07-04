@extends('layouts.admin')
@section('title', 'Orders')
@section('content')

<div class="ph">
    <div>
        <h4>Orders</h4>
        <div class="ph-sub">{{ $orders->total() }} {{ $status ? $status.' ' : '' }}order{{ $orders->total() === 1 ? '' : 's' }}@if($q) matching &ldquo;{{ $q }}&rdquo;@endif</div>
    </div>
</div>

@php
    $statusLabels = ['Pending'=>'New Orders','Paid'=>'Paid','Processing'=>'Processing','Shipped'=>'Shipped','Delivered'=>'Delivered','Cancelled'=>'Cancelled'];
@endphp
<div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;margin-bottom:16px;">
    <div style="display:flex;flex-wrap:wrap;gap:8px;">
        <a href="{{ route('admin.orders.index', array_filter(['q'=>$q ?: null])) }}" class="btn btn-sm {{ !$status ? 'btn-primary' : 'btn-light' }}">All <span style="opacity:.65;">({{ $totalCount }})</span></a>
        @foreach($statuses as $s)
            <a href="{{ route('admin.orders.index', array_filter(['status'=>$s,'q'=>$q ?: null])) }}" class="btn btn-sm {{ $status === $s ? 'btn-primary' : 'btn-light' }}">
                {{ $statusLabels[$s] ?? $s }} <span style="opacity:.65;">({{ $counts[$s] ?? 0 }})</span>
            </a>
        @endforeach
    </div>
    <form method="GET" action="{{ route('admin.orders.index') }}" style="display:flex;gap:6px;align-items:center;">
        @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Search reference, name, phone, email…" style="height:36px;min-width:240px;">
        <button type="submit" class="btn btn-sm btn-primary"><i class="mdi mdi-magnify"></i></button>
        @if($q !== '')
            <a href="{{ route('admin.orders.index', array_filter(['status'=>$status])) }}" class="btn btn-sm btn-light" title="Clear search"><i class="mdi mdi-close"></i></a>
        @endif
    </form>
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
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="table-link">{{ $order->reference ?? ('#'.$order->id) }}</a>
                        <div class="entity-meta">#{{ $order->id }}</div>
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
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-light btn-icon" title="View"><i class="mdi mdi-eye-outline"></i></a>
                            @if($order->isLocked())
                                <span class="btn btn-sm btn-light btn-icon" style="opacity:.5;cursor:default;" title="{{ $order->status }} — locked"><i class="mdi mdi-lock-outline"></i></span>
                            @else
                                <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-sm btn-light btn-icon" title="Update status"><i class="mdi mdi-pencil-outline"></i></a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="empty-state">
                        <i class="mdi mdi-cart-off"></i>
                        @if($q || $status)
                            <strong>No matching orders</strong>
                            Try a different search term or filter.
                        @else
                            <strong>No orders yet</strong>
                            New purchases will appear here as they come in.
                        @endif
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
