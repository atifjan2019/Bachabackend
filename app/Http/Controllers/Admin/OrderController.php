<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderCommentMail;
use App\Mail\OrderStatusMail;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $statuses = ['Pending', 'Paid', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];

        $status = $request->input('status');
        $status = in_array($status, $statuses, true) ? $status : null;

        $q = trim((string) $request->input('q', ''));
        $product = trim((string) $request->input('product', ''));

        // Reusable search filter — reference, customer name/phone/email (and id).
        $applySearch = function ($query) use ($q) {
            if ($q !== '') {
                $query->where(function ($sub) use ($q) {
                    $sub->where('reference', 'like', "%{$q}%")
                        ->orWhere('customer_name', 'like', "%{$q}%")
                        ->orWhere('customer_phone', 'like', "%{$q}%")
                        ->orWhere('customer_email', 'like', "%{$q}%");
                    if (is_numeric($q)) {
                        $sub->orWhere('id', $q);
                    }
                });
            }
            return $query;
        };

        // Product-name filter — matches orders whose line items include the
        // searched product. Items are stored as a JSON array in the `items`
        // TEXT column, where product names are the only free-text content, so a
        // LIKE on the raw JSON reliably (and case-insensitively) matches names.
        $applyProduct = function ($query) use ($product) {
            if ($product !== '') {
                $query->where('items', 'like', '%' . $product . '%');
            }
            return $query;
        };

        $query = Order::orderBy('id', 'desc');
        $applySearch($query);
        $applyProduct($query);
        if ($status) {
            $query->where('status', $status);
        }
        $orders = $query->paginate(20)->appends($request->query());

        // Per-status counts for the filter tabs (respecting both searches so the
        // tab counts always match what a status filter would actually show).
        $counts = $applyProduct($applySearch(Order::query()))
            ->selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status');
        $totalCount = $applyProduct($applySearch(Order::query()))->count();

        return view('admin.orders.index', compact('orders', 'statuses', 'status', 'counts', 'totalCount', 'q', 'product'));
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

        // Delivered and Cancelled orders are permanently locked.
        if ($order->isLocked()) {
            return redirect()->route('admin.orders.show', $id)
                ->with('error', 'This order is ' . $order->status . ' and is locked. Its status can no longer be changed.');
        }

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

    /**
     * Add an admin comment/note to an order and email it to the customer.
     * Used for issues like "Payment not confirmed", "Invalid shipping address", etc.
     */
    public function addComment(Request $request, string $id)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $order = Order::findOrFail($id);
        $comment = $order->comments()->create(['body' => $validated['body']]);

        if (! $order->customer_email) {
            return redirect()->route('admin.orders.show', $id)
                ->with('success', 'Comment saved. This order has no customer email, so it was not sent.');
        }

        if ($this->emailComment($order, $comment)) {
            return redirect()->route('admin.orders.show', $id)
                ->with('success', 'Comment added and emailed to the customer.');
        }

        return redirect()->route('admin.orders.show', $id)
            ->with('error', 'Comment saved, but the email to the customer could not be sent. You can retry with "Resend".');
    }

    /**
     * Retry sending a previously-failed comment email to the customer.
     */
    public function resendComment(string $id, string $comment)
    {
        $order = Order::findOrFail($id);
        $note = $order->comments()->findOrFail($comment);

        if (! $order->customer_email) {
            return redirect()->route('admin.orders.show', $id)
                ->with('error', 'This order has no customer email, so the comment cannot be sent.');
        }

        if ($this->emailComment($order, $note)) {
            return redirect()->route('admin.orders.show', $id)
                ->with('success', 'Comment re-sent to the customer.');
        }

        return redirect()->route('admin.orders.show', $id)
            ->with('error', 'Still could not send the email. Check the mail configuration and try again.');
    }

    /**
     * Send an order comment to the customer by email.
     * Marks the comment as emailed on success; returns false (and logs) on failure.
     */
    private function emailComment(Order $order, $comment): bool
    {
        try {
            Mail::to($order->customer_email)->send(new OrderCommentMail($order, $comment->body));
            $comment->update(['emailed' => true]);
            return true;
        } catch (\Throwable $e) {
            Log::warning('Comment email failed for order #' . $order->id . ': ' . $e->getMessage());
            return false;
        }
    }

    public function destroy(string $id)
    {
        Order::findOrFail($id)->delete();
        return redirect()->route('admin.orders.index')->with('success', 'Order deleted.');
    }

    public function create() { abort(404); }
    public function store(Request $request) { abort(404); }
}
