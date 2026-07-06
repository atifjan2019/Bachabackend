@extends('layouts.admin')
@section('title', 'Dashboard')

@push('styles')
<style>
    @media (max-width: 900px) {
        .dash-charts { grid-template-columns: 1fr !important; }
    }
    .apexcharts-tooltip { font-family: 'Poppins', sans-serif !important; }
</style>
@endpush

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

@php
    $catNames  = array_map(fn($r) => $r['name'], $breakdown);
    $catTotals = array_map(fn($r) => round($r['total']), $breakdown);
    $catSlugs  = array_map(fn($r) => $r['slug'] === '__other' ? '' : $r['slug'], $breakdown);
    $hasTrend  = array_sum($trendRevenue) > 0;
    // Keep the category chart compact: ~44px per bar so a single category
    // doesn't balloon to full height.
    $catChartHeight = max(120, min(360, count($breakdown) * 44 + 48));
@endphp

{{-- ─── REVENUE & ORDERS TREND ──────────────────────────── --}}
<div class="bcard" style="margin-bottom:18px;">
    <div class="bcard-head">
        <span class="bcard-title"><i class="mdi mdi-chart-areaspline"></i> Revenue &amp; Orders Trend</span>
        <span class="text-muted" style="font-size:12px;">{{ $periodLabel }}</span>
    </div>
    <div class="bcard-body">
        @if($hasTrend)
            <div id="trendChart" style="min-height:320px;"></div>
        @else
            <div class="empty-state">
                <i class="mdi mdi-chart-areaspline"></i>
                <strong>No sales in this period</strong>
                The revenue trend will appear once orders are placed in the selected range.
            </div>
        @endif
    </div>
</div>

{{-- ─── CATEGORY BAR + STATUS DONUT ─────────────────────── --}}
<div class="dash-charts" style="display:grid;grid-template-columns:1.6fr 1fr;gap:18px;margin-bottom:18px;align-items:start;">
    <div class="bcard" style="margin-bottom:0;">
        <div class="bcard-head">
            <span class="bcard-title">Revenue by Category</span>
            <span class="text-muted" style="font-size:12px;">{{ $periodLabel }} · goods revenue</span>
        </div>
        <div class="bcard-body">
            @if(count($breakdown))
                <div id="categoryChart"></div>
            @else
                <div class="empty-state">
                    <i class="mdi mdi-chart-bar"></i>
                    <strong>No sales in this period</strong>
                    Revenue by category will appear once orders are placed.
                </div>
            @endif
        </div>
    </div>

    <div class="bcard" style="margin-bottom:0;">
        <div class="bcard-head">
            <span class="bcard-title">Orders by Status</span>
            <span class="text-muted" style="font-size:12px;">{{ $order_count }} total</span>
        </div>
        <div class="bcard-body">
            @if($statusData->sum() > 0)
                <div id="statusChart" style="min-height:300px;"></div>
            @else
                <div class="empty-state">
                    <i class="mdi mdi-chart-donut"></i>
                    <strong>No orders</strong>
                    Status breakdown appears once orders exist.
                </div>
            @endif
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>
<script>
(function () {
    if (typeof ApexCharts === 'undefined') return;

    var RED = '#d92d20', ORANGE = '#f97316', INDIGO = '#6366f1';
    function rs(v) { return 'Rs. ' + Math.round(v).toLocaleString('en-US'); }

    /* ── Revenue & Orders trend ─────────────────────────── */
    var trendEl = document.getElementById('trendChart');
    if (trendEl) {
        new ApexCharts(trendEl, {
            chart: { type: 'area', height: 320, fontFamily: 'Poppins, sans-serif', toolbar: { show: false }, zoom: { enabled: false } },
            series: [
                { name: 'Revenue', type: 'area', data: @json($trendRevenue) },
                { name: 'Orders',  type: 'line', data: @json($trendOrdersCount) }
            ],
            labels: @json($trendLabels),
            colors: [RED, INDIGO],
            stroke: { curve: 'smooth', width: [3, 2.5] },
            fill: {
                type: ['gradient', 'solid'],
                gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90] }
            },
            dataLabels: { enabled: false },
            markers: { size: 0, hover: { size: 5 } },
            grid: { borderColor: 'rgba(15,23,42,.06)', strokeDashArray: 4 },
            xaxis: {
                categories: @json($trendLabels),
                tickAmount: 8,
                labels: { style: { colors: '#94a3b8', fontSize: '11px' }, rotate: 0, hideOverlappingLabels: true },
                axisBorder: { show: false }, axisTicks: { show: false }
            },
            yaxis: [
                { seriesName: 'Revenue', labels: { style: { colors: '#94a3b8', fontSize: '11px' }, formatter: function (v) { return v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v; } } },
                { seriesName: 'Orders', opposite: true, labels: { style: { colors: '#94a3b8', fontSize: '11px' }, formatter: function (v) { return Math.round(v); } } }
            ],
            legend: { position: 'top', horizontalAlign: 'right', fontWeight: 600, markers: { radius: 4 } },
            tooltip: {
                shared: true, intersect: false,
                y: { formatter: function (v, opts) { return opts.seriesIndex === 0 ? rs(v) : Math.round(v) + ' orders'; } }
            }
        }).render();
    }

    /* ── Revenue by category (horizontal bar) ───────────── */
    var catEl = document.getElementById('categoryChart');
    if (catEl) {
        var catSlugs = @json($catSlugs);
        var catBase = '{{ route('admin.dashboard') }}';
        var keep = @json($keepPeriod);
        new ApexCharts(catEl, {
            chart: {
                type: 'bar', height: {{ $catChartHeight }}, fontFamily: 'Poppins, sans-serif', toolbar: { show: false },
                events: {
                    dataPointSelection: function (e, ctx, cfg) {
                        var slug = catSlugs[cfg.dataPointIndex];
                        if (!slug) return;
                        var p = new URLSearchParams(keep);
                        p.set('category', slug);
                        window.location = catBase + '?' + p.toString();
                    }
                }
            },
            series: [{ name: 'Revenue', data: @json($catTotals) }],
            xaxis: {
                categories: @json($catNames),
                labels: { style: { colors: '#94a3b8', fontSize: '11px' }, formatter: function (v) { return v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v; } },
                axisBorder: { show: false }, axisTicks: { show: false }
            },
            yaxis: { labels: { style: { colors: '#334155', fontSize: '12px', fontWeight: 600 } } },
            plotOptions: { bar: { horizontal: true, borderRadius: 5, barHeight: '46%', distributed: true } },
            colors: ['#d92d20', '#f97316', '#6366f1', '#10b981', '#06b6d4', '#8b5cf6', '#ec4899', '#f59e0b'],
            dataLabels: { enabled: true, textAnchor: 'start', offsetX: 0, formatter: function (v) { return rs(v); }, style: { fontSize: '11px', fontWeight: 700, colors: ['#fff'] } },
            grid: { borderColor: 'rgba(15,23,42,.06)', strokeDashArray: 4 },
            legend: { show: false },
            tooltip: { y: { formatter: function (v) { return rs(v); } } }
        }).render();
    }

    /* ── Orders by status (donut) ───────────────────────── */
    var statusEl = document.getElementById('statusChart');
    if (statusEl) {
        var statusColorMap = {
            'Pending': '#f59e0b', 'Paid': '#3b82f6', 'Processing': '#6366f1',
            'Shipped': '#06b6d4', 'Delivered': '#10b981', 'Cancelled': '#ef4444'
        };
        var statusLabels = @json($statusLabels);
        var colors = statusLabels.map(function (s) { return statusColorMap[s] || '#94a3b8'; });
        new ApexCharts(statusEl, {
            chart: { type: 'donut', height: 300, fontFamily: 'Poppins, sans-serif' },
            series: @json($statusData),
            labels: statusLabels,
            colors: colors,
            stroke: { width: 2, colors: ['#fff'] },
            plotOptions: { pie: { donut: { size: '68%', labels: { show: true, total: { show: true, label: 'Orders', fontSize: '13px', color: '#94a3b8', formatter: function (w) { return w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0); } } } } } },
            dataLabels: { enabled: true, formatter: function (v) { return Math.round(v) + '%'; }, style: { fontSize: '11px', fontWeight: 700 } },
            legend: { position: 'bottom', fontWeight: 600, markers: { radius: 4 } },
            tooltip: { y: { formatter: function (v) { return v + ' orders'; } } }
        }).render();
    }
})();
</script>
@endpush
@endsection
