<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\Category;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $product_count = Product::count();
        $order_count = Order::count();
        $total_revenue = Order::where('status', '!=', 'Cancelled')->sum('total_amount');
        $pending_count = Order::where('status', 'Pending')->count();
        
        $recent_orders = Order::orderBy('id', 'desc')->limit(10)->get();
        $category_count = Category::count();

        return view('admin.dashboard', compact(
            'product_count',
            'order_count',
            'total_revenue',
            'pending_count',
            'recent_orders',
            'category_count'
        ));
    }
}
