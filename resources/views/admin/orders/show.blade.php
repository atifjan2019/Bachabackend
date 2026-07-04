@extends('layouts.admin')
@section('title', 'Order Details')
@section('content')

<div class="ph">
    <div>
        <h4>Order #{{ $order->id }}</h4>
        <div class="ph-sub">Placed on {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y, h:i A') }}</div>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-primary"><i class="mdi mdi-pencil-outline"></i> Update Status</a>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-light"><i class="mdi mdi-arrow-left"></i> Back</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="bcard">
            <div class="bcard-head"><span class="bcard-title">Order Items</span></div>
            <div class="table-wrap">
                <table class="table table-stack mb-0">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="text-end">Price</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $items = is_string($order->items) ? json_decode($order->items, true) : $order->items;
                        @endphp
                        @if(is_array($items))
                            @foreach($items as $item)
                            <tr>
                                <td data-label="Item" class="text-strong">
                                    {{ $item['name'] ?? 'Product' }}
                                    @if(!empty($item['size']))
                                        <div class="entity-meta">Size: {{ $item['size'] }}</div>
                                    @endif
                                </td>
                                <td data-label="Price" class="text-end text-nowrap">Rs. {{ number_format($item['price'] ?? 0) }}</td>
                                <td data-label="Qty" class="text-center">{{ $item['quantity'] ?? 1 }}</td>
                                <td data-label="Total" class="text-end text-nowrap text-strong">Rs. {{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1)) }}</td>
                            </tr>
                            @endforeach
                        @else
                            <tr><td colspan="4" class="empty-state"><i class="mdi mdi-package-variant"></i><strong>No item data found</strong>Order line items could not be loaded for this record.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="bcard-body border-top surface-strip">
                <div class="metric-list" style="margin-left:auto;max-width:280px;">
                    <div class="metric-row">
                        <span class="metric-row-label">Subtotal</span>
                        <span class="metric-row-value">Rs. {{ number_format($order->subtotal ?? 0) }}</span>
                    </div>
                    <div class="metric-row">
                        <span class="metric-row-label">Shipping</span>
                        <span class="metric-row-value">Rs. {{ number_format($order->shipping_fee ?? 0) }}</span>
                    </div>
                    <div class="metric-row">
                        <span class="metric-row-label">Total</span>
                        <span class="metric-row-value" style="color:var(--red);">Rs. {{ number_format($order->total_amount ?? 0) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="bcard mb-4">
            <div class="bcard-head"><span class="bcard-title">Customer Info</span></div>
            <div class="bcard-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Customer</div>
                        <div class="detail-value">{{ $order->customer_name }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div class="detail-value">{{ $order->customer_email }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Phone</div>
                        <div class="detail-value">{{ $order->customer_phone }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Shipping Address</div>
                        <div class="detail-value">{{ $order->shipping_address }}<br>{{ $order->city }}, {{ $order->country }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bcard">
            <div class="bcard-head"><span class="bcard-title">Order Status</span></div>
            <div class="bcard-body">
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
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Current Status</div>
                        <div class="detail-value"><span class="status-badge" style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }};">{{ $order->status }}</span></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Payment Method</div>
                        <div class="detail-value">{{ $order->payment_method ?? 'Cash on Delivery' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Payment Receipt</div>
                        <div class="detail-value">
                            @if(!empty($order->payment_receipt))
                                <a href="{{ $order->payment_receipt }}" target="_blank" rel="noopener">
                                    <img src="{{ $order->payment_receipt }}" alt="Payment receipt" style="max-width:100%;max-height:220px;border-radius:6px;border:1px solid var(--line,#e5e7eb);">
                                    <div style="margin-top:6px;font-size:12px;"><i class="mdi mdi-open-in-new"></i> View full receipt</div>
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
