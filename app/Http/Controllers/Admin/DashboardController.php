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
            'to'
        ));
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
