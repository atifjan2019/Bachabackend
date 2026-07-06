@extends('layouts.admin')
@section('title', 'Order Details')
@section('content')

<div class="ph">
    <div>
        <h4>Order {{ $order->reference ?? ('#'.$order->id) }}</h4>
        <div class="ph-sub">Internal #{{ $order->id }} · Placed on {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y, h:i A') }}</div>
    </div>
    <div class="page-actions">
        @if($order->isLocked())
            <span class="btn btn-light" style="cursor:default;" title="Delivered and cancelled orders are locked"><i class="mdi mdi-lock-outline"></i> {{ $order->status }} · Locked</span>
        @else
            <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-primary"><i class="mdi mdi-pencil-outline"></i> Update Status</a>
        @endif
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

        {{-- Admin Comments --}}
        <div class="bcard mt-4">
            <div class="bcard-head"><span class="bcard-title">Admin Comments</span></div>
            <div class="bcard-body">
                @forelse($order->comments as $c)
                    <div style="border-left:3px solid var(--red);background:var(--surf2,#f6f6f7);padding:10px 14px;border-radius:6px;margin-bottom:10px;">
                        <div style="white-space:pre-line;font-size:14px;color:var(--t1,#141414);">{{ $c->body }}</div>
                        <div style="font-size:11px;color:var(--t2);margin-top:6px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <span>{{ \Carbon\Carbon::parse($c->created_at)->format('d M Y, h:i A') }}</span>
                            @if($c->emailed)
                                <span style="color:#1f9d55;">&middot; <i class="mdi mdi-email-check-outline"></i> emailed to customer</span>
                            @else
                                <span style="color:#b45309;">&middot; <i class="mdi mdi-email-alert-outline"></i> not emailed</span>
                                @if($order->customer_email)
                                    <form method="POST" action="{{ route('admin.orders.comments.resend', [$order->id, $c->id]) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger" style="padding:2px 8px;font-size:11px;">
                                            <i class="mdi mdi-refresh"></i> Resend
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted" style="margin-bottom:14px;">No comments yet. Add a note below and it will be emailed to the customer.</p>
                @endforelse

                <form method="POST" action="{{ route('admin.orders.comments.store', $order->id) }}">
                    @csrf
                    <label class="form-label">Add a comment @if($order->customer_email)<span class="text-muted" style="font-weight:400;">(emailed to {{ $order->customer_email }})</span>@endif</label>
                    {{-- @php
                        $presets = [
                            'Payment not confirmed' => 'We could not confirm the payment for this order. Please complete your payment and re-share your payment receipt so we can process your order.',
                            'Invalid shipping address' => 'The shipping address on your order appears to be incomplete or invalid. Please reply with your complete and correct delivery address so we can ship your order.',
                            'Additional information required' => 'We need some additional information to process your order. Please reply with the requested details.',
                        ];
                    @endphp
                    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px;">
                        @foreach($presets as $label => $text)
                            <button type="button" class="btn btn-sm btn-light" onclick="var t=document.getElementById('order_comment'); t.value=@js($text); t.focus();">{{ $label }}</button>
                        @endforeach
                    </div> --}}
                    <textarea id="order_comment" name="body" class="form-control" rows="3" required placeholder="Write a note to the customer…">{{ old('body') }}</textarea>
                    @error('body')<div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
                    <button type="submit" class="btn btn-primary mt-2"><i class="mdi mdi-send-outline"></i> Send to customer</button>
                    @unless($order->customer_email)
                        <div class="form-hint" style="color:#b45309;margin-top:6px;">This order has no customer email — the comment will be saved but not emailed.</div>
                    @endunless
                </form>
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
