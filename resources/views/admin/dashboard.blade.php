@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')

<section class="hero-banner">
    <div class="hero-kicker">Operations Overview</div>
    <h1 class="hero-title">Store performance at a glance.</h1>
    <p class="hero-subtitle">Track orders, revenue, fulfilment load, and product inventory from one responsive control surface.</p>
    <div class="hero-meta">
        <div class="hero-pill"><i class="mdi mdi-shopping-outline"></i> {{ $order_count }} total orders</div>
        <div class="hero-pill"><i class="mdi mdi-cash-multiple"></i> Rs. {{ number_format($total_revenue) }} revenue</div>
        <div class="hero-pill"><i class="mdi mdi-package-variant-closed"></i> {{ $product_count }} products live</div>
    </div>
</section>

<div class="kpi-grid">
    <div>
        <div class="stat-card" style="--a1:var(--red);--a2:#ff6b6b;">
            <div class="stat-icon" style="background:var(--red-bg);color:var(--red);">
                <i class="mdi mdi-shopping-outline"></i>
            </div>
            <div class="stat-label">Total Orders</div>
            <div class="stat-value">{{ $order_count }}</div>
            <div class="stat-sub">All time</div>
        </div>
    </div>
    <div>
        <div class="stat-card" style="--a1:#10b981;--a2:#34d399;">
            <div class="stat-icon" style="background:rgba(16,185,129,.08);color:#10b981;">
                <i class="mdi mdi-cash-multiple"></i>
            </div>
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">Rs. {{ number_format($total_revenue) }}</div>
            <div class="stat-sub">Non-cancelled orders</div>
        </div>
    </div>
    <div>
        <div class="stat-card" style="--a1:#f59e0b;--a2:#fbbf24;">
            <div class="stat-icon" style="background:rgba(245,158,11,.08);color:#f59e0b;">
                <i class="mdi mdi-clock-outline"></i>
            </div>
            <div class="stat-label">Pending Orders</div>
            <div class="stat-value">{{ $pending_count }}</div>
            <div class="stat-sub">Awaiting action</div>
        </div>
    </div>
    <div>
        <div class="stat-card" style="--a1:#6366f1;--a2:#818cf8;">
            <div class="stat-icon" style="background:rgba(99,102,241,.08);color:#6366f1;">
                <i class="mdi mdi-package-variant-closed"></i>
            </div>
            <div class="stat-label">Products</div>
            <div class="stat-value">{{ $product_count }}</div>
            <div class="stat-sub">{{ $category_count }} categories</div>
        </div>
    </div>
</div>

<div class="bcard">
    <div class="bcard-head">
        <span class="bcard-title">Recent Orders</span>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-light">View all <i class="mdi mdi-arrow-right"></i></a>
    </div>
    <div class="table-wrap">
        <table class="table table-stack mb-0">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($recent_orders as $order)
                @php
                    $sc = match($order->status) {
                        'Pending'    => ['bg'=>'#fef3c7','color'=>'#92400e'],
                        'Paid'       => ['bg'=>'#dbeafe','color'=>'#1e40af'],
                        'Processing' => ['bg'=>'#e0e7ff','color'=>'#3730a3'],
                        'Shipped'    => ['bg'=>'#cffafe','color'=>'#155e75'],
                        'Delivered'  => ['bg'=>'#d1fae5','color'=>'#065f46'],
                        'Cancelled'  => ['bg'=>'#fee2e2','color'=>'#dc2626'],
                        default      => ['bg'=>'#f3f4f6','color'=>'var(--t2)'],
                    };
                @endphp
                <tr>
                    <td data-label="Order">
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="table-link">
                            #{{ $order->id }}
                        </a>
                    </td>
                    <td data-label="Customer">{{ $order->customer_name }}</td>
                    <td data-label="Amount" class="text-strong">Rs. {{ number_format($order->total_amount) }}</td>
                    <td data-label="Status">
                        <span class="status-badge" style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }};">
                            {{ $order->status }}
                        </span>
                    </td>
                    <td data-label="Date" class="text-muted">{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}</td>
                    <td data-label="Actions">
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-light btn-icon"><i class="mdi mdi-eye-outline"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="empty-state">
                        <i class="mdi mdi-cart-off"></i>
                        <strong>No orders yet</strong>
                        Recent sales activity will appear here once customers start checking out.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
