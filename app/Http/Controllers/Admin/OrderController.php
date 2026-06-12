<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderStatusMail;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        $previousStatus = $order->status;
        $order->update(['status' => $request->status]);

        // Email the customer when the status actually changes (shipped, delivered,
        // cancelled, etc.). Email failures must never block the status update.
        if ($order->customer_email && strtolower($previousStatus) !== strtolower($request->status)) {
            try {
                Mail::to($order->customer_email)->send(new OrderStatusMail($order, $request->status));
            } catch (\Throwable $e) {
                Log::warning('Status email failed for order #' . $order->id . ': ' . $e->getMessage());
            }
        }

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
