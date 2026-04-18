<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AbandonedCart;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function storeOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'shipping_address' => 'required|string|max:2000',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.size' => 'nullable|string|max:50',
            'subtotal' => 'nullable|numeric|min:0',
            'shipping_fee' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:255',
        ]);

        // If authenticated customer, always use their email for order linking
        $authCustomer = auth('api')->user();
        $customerEmail = $validated['customer_email'];
        if ($authCustomer && $authCustomer->email) {
            $customerEmail = $authCustomer->email;
        }

        $order = Order::create([
            'customer_name' => $validated['customer_name'],
            'customer_email' => $customerEmail,
            'customer_phone' => $validated['customer_phone'] ?? null,
            'shipping_address' => $validated['shipping_address'],
            'city' => $validated['city'] ?? null,
            'country' => $validated['country'] ?? null,
            'items' => $validated['items'],
            'subtotal' => $validated['subtotal'] ?? 0,
            'shipping_fee' => $validated['shipping_fee'] ?? 0,
            'total_amount' => $validated['total_amount'],
            'payment_method' => $validated['payment_method'] ?? 'Cash on Delivery',
            'status' => 'Pending',
        ]);

        $customer = Customer::updateOrCreate(
            ['email' => $validated['customer_email']],
            [
                'name' => $validated['customer_name'],
                'phone' => $validated['customer_phone'] ?? null,
                'address' => $validated['shipping_address'],
            ]
        );

        $customer->increment('orders_count');
        $customer->increment('total_spent', (float) $validated['total_amount']);

        return response()->json([
            'message' => 'Order created successfully.',
            'data' => [
                'id' => $order->id,
                'status' => $order->status,
            ],
        ], 201);
    }

    public function storeAbandonedCart(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'cart_data' => 'required|array',
        ]);

        $record = AbandonedCart::create([
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'cart_data' => $validated['cart_data'],
        ]);

        return response()->json([
            'message' => 'Abandoned cart captured.',
            'data' => ['id' => $record->id],
        ], 201);
    }
}
