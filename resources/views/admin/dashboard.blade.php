@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')

@php
    $periodLabels = ['today'=>'Today','weekly'=>'This Week','monthly'=>'This Month','yearly'=>'This Year','all'=>'All Time','custom'=>'Custom Range'];
    $periodLabel = $periodLabels[$period] ?? 'All Time';
    $quick = ['today'=>'Today','weekly'=>'Weekly','monthly'=>'Monthly','yearly'=>'Yearly','all'=>'All Time'];
    $catName = $categorySlug ? (optional($categories->firstWhere('slug', $categorySlug))->name ?? $categorySlug) : null;
@endphp

@php
    $customFrom = $period === 'custom' && $from ? $from->format('Y-m-d') : null;
    $customTo   = $period === 'custom' && $to ? $to->format('Y-m-d') : null;
    // Query preserving current period/range, used when only the category changes.
    $keepPeriod = array_filter(['period' => $period, 'from' => $customFrom, 'to' => $customTo]);
@endphp

<div class="bcard" style="margin-bottom:18px;">
    <div class="bcard-head">
        <span class="bcard-title"><i class="mdi mdi-filter-variant"></i> Filters</span>
        @if($period !== 'all' || $categorySlug)
            <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-light"><i class="mdi mdi-refresh"></i> Reset</a>
        @endif
    </div>
    <div class="bcard-body">
        {{-- Time period --}}
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--t2);font-weight:600;margin-bottom:8px;">Time period</div>
        <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
            @foreach($quick as $key => $label)
                <a href="{{ route('admin.dashboard', array_filter(['period'=>$key,'category'=>$categorySlug])) }}"
                   class="btn btn-sm {{ $period === $key ? 'btn-primary' : 'btn-light' }}">{{ $label }}</a>
            @endforeach

            {{-- Custom range --}}
            <form method="GET" action="{{ route('admin.dashboard') }}" style="display:flex;flex-wrap:wrap;gap:6px;align-items:center;margin:0;padding-left:10px;margin-left:2px;border-left:1px solid var(--line,#e5e7eb);">
                <input type="hidden" name="period" value="custom">
                @if($categorySlug)<input type="hidden" name="category" value="{{ $categorySlug }}">@endif
                <span class="btn btn-sm {{ $period==='custom' ? 'btn-primary' : 'btn-light' }}" style="cursor:default;"><i class="mdi mdi-calendar-range"></i> Custom</span>
                <input type="date" name="from" class="form-control" style="height:34px;width:150px;" value="{{ $customFrom }}" aria-label="From date">
                <span style="color:var(--t2);">–</span>
                <input type="date" name="to" class="form-control" style="height:34px;width:150px;" value="{{ $customTo }}" aria-label="To date">
                <button type="submit" class="btn btn-sm btn-primary">Apply</button>
            </form>
        </div>

        {{-- Revenue by category --}}
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--t2);font-weight:600;margin:18px 0 8px;">Revenue by category</div>
        <form method="GET" action="{{ route('admin.dashboard') }}" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin:0;">
            <input type="hidden" name="period" value="{{ $period }}">
            @if($customFrom)<input type="hidden" name="from" value="{{ $customFrom }}">@endif
            @if($customTo)<input type="hidden" name="to" value="{{ $customTo }}">@endif
            <select name="category" class="form-select" style="height:36px;max-width:260px;" onchange="this.form.submit()">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}" {{ $categorySlug === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            @if($categorySlug)
                <a href="{{ route('admin.dashboard', $keepPeriod) }}" class="btn btn-sm btn-light"><i class="mdi mdi-close"></i> Clear</a>
            @endif
        </form>

        {{-- Active summary --}}
        <div style="margin-top:16px;padding-top:12px;border-top:1px solid var(--line,#eee);font-size:13px;color:var(--t2);">
            Showing <strong style="color:var(--t1,#141414);">{{ $periodLabel }}</strong>
            @if($period==='custom' && ($from || $to))
                <span>({{ $from ? $from->format('d M Y') : '…' }} – {{ $to ? $to->format('d M Y') : '…' }})</span>
            @endif
            @if($catName)
                &middot; revenue for <strong style="color:var(--red);">{{ $catName }}</strong>
            @endif
        </div>
    </div>
</div>

<div class="kpi-grid">
    <div>
        <div class="stat-card" style="--a1:var(--red);--a2:#ff6b6b;">
            <div class="stat-icon" style="background:var(--red-bg);color:var(--red);">
                <i class="mdi mdi-shopping-outline"></i>
            </div>
            <div class="stat-label">Total Orders</div>
            <div class="stat-value">{{ $order_count }}</div>
            <div class="stat-sub">{{ $periodLabel }}</div>
        </div>
    </div>
    <div>
        <div class="stat-card" style="--a1:#10b981;--a2:#34d399;">
            <div class="stat-icon" style="background:rgba(16,185,129,.08);color:#10b981;">
                <i class="mdi mdi-cash-multiple"></i>
            </div>
            <div class="stat-label">{{ $catName ? 'Revenue · '.$catName : 'Total Revenue' }}</div>
            <div class="stat-value">Rs. {{ number_format($total_revenue) }}</div>
            <div class="stat-sub">{{ $catName ? $periodLabel : $periodLabel.' · non-cancelled' }}</div>
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

<div class="bcard" style="margin-bottom:18px;">
    <div class="bcard-head">
        <span class="bcard-title">Revenue by Category</span>
        <span class="text-muted" style="font-size:12px;">{{ $periodLabel }} · goods revenue</span>
    </div>
    <div class="bcard-body">
        @php $maxCat = collect($breakdown)->max('total') ?: 1; @endphp
        @forelse($breakdown as $row)
            <div style="margin-bottom:14px;">
                <div style="display:flex;justify-content:space-between;gap:12px;font-size:13px;margin-bottom:5px;">
                    <a href="{{ route('admin.dashboard', array_filter(['period'=>$period,'from'=>$period==='custom' && $from ? $from->format('Y-m-d') : null,'to'=>$period==='custom' && $to ? $to->format('Y-m-d') : null,'category'=>$row['slug']==='__other' ? null : $row['slug']])) }}"
                       class="table-link" style="{{ $categorySlug===$row['slug'] ? 'font-weight:700;color:var(--red);' : '' }}">{{ $row['name'] }}</a>
                    <span class="text-strong">Rs. {{ number_format($row['total']) }}</span>
                </div>
                <div style="height:8px;background:#f1f1f4;border-radius:99px;overflow:hidden;">
                    <div style="height:100%;width:{{ max(2, round($row['total'] / $maxCat * 100)) }}%;background:linear-gradient(90deg,var(--red),#f97316);border-radius:99px;"></div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="mdi mdi-chart-bar"></i>
                <strong>No sales in this period</strong>
                Revenue by category will appear once orders are placed in the selected range.
            </div>
        @endforelse
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
