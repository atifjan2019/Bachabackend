<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'all');
        $categorySlug = $request->input('category') ?: null;

        // Resolve the [from, to] window for the selected period.
        [$from, $to] = $this->resolveWindow($request, $period);

        // Helper: a fresh order query scoped to the selected date window.
        $scoped = function () use ($from, $to) {
            $q = Order::query();
            if ($from) {
                $q->where('created_at', '>=', $from);
            }
            if ($to) {
                $q->where('created_at', '<=', $to);
            }
            return $q;
        };

        $order_count = $scoped()->count();
        $pending_count = $scoped()->where('status', 'Pending')->count();

        // ── Revenue by category ────────────────────────────────────────────
        // Order items only store {name, price, quantity, size}, so we map each
        // item's name → product's category slug → category name, in PHP.
        $nameToSlug = Product::pluck('category', 'name');   // product name => category slug
        $slugToName = Category::pluck('name', 'slug');      // slug => display name

        $orders = $scoped()->where('status', '!=', 'Cancelled')->get(['items']);

        $revenueBySlug = [];
        foreach ($orders as $order) {
            $items = is_array($order->items) ? $order->items : [];
            foreach ($items as $item) {
                $line = (float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 1);
                $slug = $nameToSlug[$item['name'] ?? ''] ?? '__other';
                $revenueBySlug[$slug] = ($revenueBySlug[$slug] ?? 0) + $line;
            }
        }

        // Sorted breakdown for the view (name + total), largest first.
        $breakdown = [];
        foreach ($revenueBySlug as $slug => $total) {
            $breakdown[] = [
                'slug' => $slug,
                'name' => $slug === '__other'
                    ? 'Uncategorized'
                    : ($slugToName[$slug] ?? ucwords(str_replace('-', ' ', $slug))),
                'total' => $total,
            ];
        }
        usort($breakdown, fn ($a, $b) => $b['total'] <=> $a['total']);

        // Total revenue: order-level (incl. shipping) for the whole window, or a
        // single category's goods revenue when a category filter is applied.
        if ($categorySlug) {
            $total_revenue = $revenueBySlug[$categorySlug] ?? 0;
        } else {
            $total_revenue = $scoped()->where('status', '!=', 'Cancelled')->sum('total_amount');
        }

        $product_count = Product::count();
        $category_count = Category::count();
        $categories = Category::orderBy('name')->get(['name', 'slug']);

        $recent_orders = $scoped()->orderBy('id', 'desc')->limit(10)->get();

        // ── Revenue & orders trend (time series) ───────────────────────────
        $trendOrders = $scoped()->where('status', '!=', 'Cancelled')->get(['created_at', 'total_amount']);
        [$mode, $start, $end] = $this->resolveTrendRange($from, $to, $period, $trendOrders);
        [$trendLabels, $trendRevenue, $trendOrdersCount] = $this->buildTrend($trendOrders, $mode, $start, $end);

        // ── Orders by status (for donut) ───────────────────────────────────
        $statusCounts = $scoped()->get(['status'])
            ->groupBy('status')
            ->map->count()
            ->sortDesc();
        $statusLabels = $statusCounts->keys()->values();
        $statusData = $statusCounts->values();

        return view('admin.dashboard', compact(
            'product_count',
            'order_count',
            'total_revenue',
            'pending_count',
            'recent_orders',
            'category_count',
            'period',
            'categorySlug',
            'categories',
            'breakdown',
            'from',
            'to',
            'trendLabels',
            'trendRevenue',
            'trendOrdersCount',
            'statusLabels',
            'statusData'
        ));
    }

    /**
     * Decide the bucketing granularity ('hour'|'day'|'month') and the
     * [start, end] range to plot for the revenue trend chart.
     */
    private function resolveTrendRange($from, $to, string $period, $orders): array
    {
        $now = Carbon::now();
        $end = $to ? $to->copy() : $now->copy();

        if ($period === 'today') {
            $mode = 'hour';
        } elseif (in_array($period, ['yearly', 'all'], true)) {
            $mode = 'month';
        } elseif ($period === 'custom' && $from && $to) {
            $mode = $from->diffInDays($to) > 92 ? 'month' : 'day';
        } else {
            $mode = 'day'; // weekly, monthly, unbounded custom
        }

        if ($from) {
            $start = $from->copy();
        } else {
            // All time: begin at the first order, but cap month view to 12 months.
            $min = $orders->min('created_at');
            $start = $min ? Carbon::parse($min) : $now->copy()->subMonths(11);
        }

        if ($mode === 'month') {
            $cap = $end->copy()->subMonths(11)->startOfMonth();
            if (!$from && $start->lt($cap)) {
                $start = $cap;
            }
        }

        return [$mode, $start, $end];
    }

    /**
     * Bucket orders into evenly-spaced time slots and return
     * [labels[], revenue[], orderCounts[]] for charting.
     */
    private function buildTrend($orders, string $mode, Carbon $start, Carbon $end): array
    {
        $keyFmt = ['hour' => 'Y-m-d H', 'day' => 'Y-m-d', 'month' => 'Y-m'][$mode];
        $labelFmt = ['hour' => 'g A', 'day' => 'd M', 'month' => 'M Y'][$mode];

        $buckets = [];
        $cursor = $start->copy();
        $cursor = match ($mode) {
            'hour'  => $cursor->startOfHour(),
            'day'   => $cursor->startOfDay(),
            'month' => $cursor->startOfMonth(),
        };

        $guard = 0;
        while ($cursor->lte($end) && $guard < 400) {
            $buckets[$cursor->format($keyFmt)] = ['label' => $cursor->format($labelFmt), 'rev' => 0, 'cnt' => 0];
            match ($mode) {
                'hour'  => $cursor->addHour(),
                'day'   => $cursor->addDay(),
                'month' => $cursor->addMonth(),
            };
            $guard++;
        }

        foreach ($orders as $o) {
            $key = Carbon::parse($o->created_at)->format($keyFmt);
            if (isset($buckets[$key])) {
                $buckets[$key]['rev'] += (float) $o->total_amount;
                $buckets[$key]['cnt'] += 1;
            }
        }

        $labels = $revenue = $counts = [];
        foreach ($buckets as $b) {
            $labels[] = $b['label'];
            $revenue[] = round($b['rev']);
            $counts[] = $b['cnt'];
        }

        return [$labels, $revenue, $counts];
    }

    /**
     * Resolve a [Carbon|null $from, Carbon|null $to] window for a named period.
     * A null bound means "unbounded" (used by All Time).
     */
    private function resolveWindow(Request $request, string $period): array
    {
        $now = Carbon::now();

        switch ($period) {
            case 'today':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
            case 'weekly':
                return [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()];
            case 'monthly':
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
            case 'yearly':
                return [$now->copy()->startOfYear(), $now->copy()->endOfYear()];
            case 'custom':
                $from = $to = null;
                try {
                    if ($request->filled('from')) {
                        $from = Carbon::parse($request->input('from'))->startOfDay();
                    }
                    if ($request->filled('to')) {
                        $to = Carbon::parse($request->input('to'))->endOfDay();
                    }
                } catch (\Throwable $e) {
                    // Invalid date input → treat as unbounded on that side.
                }
                return [$from, $to];
            case 'all':
            default:
                return [null, null];
        }
    }
}
