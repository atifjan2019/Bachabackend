<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::orderBy('id', 'desc')->paginate(20);
        return view('admin.orders.index', compact('orders'));
    }

    public function show(string $id)
    {
        $order = Order::findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    public function edit(string $id)
    {
        $order = Order::findOrFail($id);
        $statuses = ['Pending', 'Paid', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
        return view('admin.orders.edit', compact('order', 'statuses'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate(['status' => 'required|string']);
        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);
        return redirect()->route('admin.orders.show', $id)->with('success', 'Order status updated.');
    }

    public function destroy(string $id)
    {
        Order::findOrFail($id)->delete();
        return redirect()->route('admin.orders.index')->with('success', 'Order deleted.');
    }

    public function create() { abort(404); }
    public function store(Request $request) { abort(404); }
}
