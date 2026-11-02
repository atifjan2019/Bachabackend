<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AbandonedCart;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(private readonly OrderService $orders)
    {
    }

    public function storeOrder(Request $request): JsonResponse
    {
        $validated = $request->validate(OrderService::rules());

        // Non-COD methods must include a payment receipt/screenshot.
        $method = $validated['payment_method'] ?? OrderService::COD_LABEL;
        if (OrderService::needsReceipt($method, $validated['payment_receipt'] ?? null)) {
            return response()->json([
                'message' => 'A payment receipt is required for this payment method.',
                'errors' => ['payment_receipt' => ['Please upload your payment receipt.']],
            ], 422);
        }

        // Snapshot, totals, customer upsert, abandoned-cart cleanup and emails
        // all live in OrderService so the web checkout prices orders identically.
        $order = $this->orders->create($validated, auth('api')->user());
        $items = $order->items;
        $subtotal = (float) $order->subtotal;
        $shippingFee = (float) $order->shipping_fee;
        $totalAmount = (float) $order->total_amount;

        return response()->json([
            'message' => 'Order created successfully.',
            'data' => [
                'id' => $order->id,
                'reference' => $order->reference,
                'status' => $order->status,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'total_amount' => $totalAmount,
                'items' => $items,
            ],
        ], 201);
    }

    /**
     * Delivery charge for a subtotal. Kept as a public static so existing
     * callers keep working; the rule itself lives in OrderService.
     */
    public static function shippingFor(float $subtotal): float
    {
        return OrderService::shippingFor($subtotal);
    }

    /**
     * Delivery charge the storefront should show for a cart subtotal, using
     * exactly the rule storeOrder() applies.
     */
    public function shippingQuote(Request $request): JsonResponse
    {
        $subtotal = (float) $request->query('subtotal', 0);
        $fee = self::shippingFor($subtotal);

        return response()->json(['data' => ['subtotal' => $subtotal, 'shipping_fee' => $fee, 'total' => $subtotal + $fee]]);
    }

    public function storeAbandonedCart(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'total' => 'nullable|numeric|min:0',
            'cart_data' => 'required|array',
        ]);

        // One row per email — keep it updated as the cart changes (no duplicates).
        $record = AbandonedCart::updateOrCreate(
            ['email' => strtolower(trim($validated['email']))],
            [
                'name' => $validated['name'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'total' => $validated['total'] ?? 0,
                'cart_data' => $validated['cart_data'],
            ]
        );

        return response()->json([
            'message' => 'Abandoned cart captured.',
            'data' => ['id' => $record->id],
        ], 201);
    }

    public function getOrder(string $id): JsonResponse
    {
        // Accept either the public reference ("BS-482913") or the internal id.
        $order = Order::where('reference', $id)->orWhere('id', $id)->firstOrFail();
        return response()->json(['data' => $order]);
    }
}
