<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\NewOrderMail;
use App\Mail\OrderPlacedMail;
use App\Models\AbandonedCart;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
            'items.*.color' => 'nullable|string|max:60',
            'items.*.product_id' => 'nullable|integer|min:1',
            'items.*.slug' => 'nullable|string|max:255',
            'items.*.image' => 'nullable|string|max:2000',
            'items.*.sku' => 'nullable|string|max:64',
            'items.*.variant_sku' => 'nullable|string|max:120',
            'payment_method' => 'nullable|string|max:255',
            'payment_receipt' => 'nullable|string|max:2000',
        ]);

        // Non-COD methods must include a payment receipt/screenshot.
        $method = $validated['payment_method'] ?? 'Cash on Delivery';
        $isCod = in_array(strtolower(trim($method)), ['cod', 'cash on delivery'], true);
        if (!$isCod && empty($validated['payment_receipt'])) {
            return response()->json([
                'message' => 'A payment receipt is required for this payment method.',
                'errors' => ['payment_receipt' => ['Please upload your payment receipt.']],
            ], 422);
        }

        // Snapshot each line against the catalogue so the order records the
        // exact product (code, image, chosen colour) as it was at purchase,
        // even if the product is edited or deleted later.
        $items = $this->snapshotItems($validated['items']);

        // Calculate totals on backend for accuracy
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $shippingFee = self::shippingFor($subtotal);
        $totalAmount = $subtotal + $shippingFee;

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
            'country' => $validated['country'] ?? 'Pakistan',
            'items' => $items,
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'total_amount' => $totalAmount,
            'payment_method' => $validated['payment_method'] ?? 'Cash on Delivery',
            'payment_receipt' => $validated['payment_receipt'] ?? null,
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
        $customer->increment('total_spent', $totalAmount);

        // The customer completed checkout — remove any abandoned cart for this
        // email so it's no longer counted as a recovery opportunity.
        AbandonedCart::where('email', strtolower(trim($validated['customer_email'])))->delete();

        // Send branded order emails (customer confirmation + admin notification).
        // Failures must never break order creation.
        try {
            if ($order->customer_email) {
                Mail::to($order->customer_email)->send(new OrderPlacedMail($order));
            }

            $adminEmail = config('mail.admin_address')
                ?: Setting::where('setting_key', 'order_notification_email')->value('setting_value')
                ?: Setting::where('setting_key', 'business_email')->value('setting_value')
                ?: config('mail.from.address');

            if ($adminEmail) {
                Mail::to($adminEmail)->send(new NewOrderMail($order));
            }
        } catch (\Throwable $e) {
            Log::warning('Order email failed for order #' . $order->id . ': ' . $e->getMessage());
        }

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
     * Delivery charge for a subtotal, from the admin's Shipping settings:
     * the base charge, waived at or above the threshold while the
     * free-shipping switch is ON. Also used by the storefront's quote endpoint
     * so cart, checkout and the saved order always agree.
     */
    public static function shippingFor(float $subtotal): float
    {
        $settings = Setting::whereIn('setting_key', ['shipping_fee', 'free_shipping_threshold', 'free_shipping_enabled'])
            ->pluck('setting_value', 'setting_key');

        $baseFee = (float) ($settings['shipping_fee'] ?? 250);
        $threshold = (float) ($settings['free_shipping_threshold'] ?? 5000);
        $enabled = ($settings['free_shipping_enabled'] ?? '1') !== '0';

        if ($subtotal <= 0) {
            return 0.0;
        }

        return ($enabled && $subtotal >= $threshold) ? 0.0 : $baseFee;
    }

    /**
     * Fill in product code, slug, image and colour for every line from the
     * catalogue, keeping whatever the client already sent. The result is what
     * gets stored on the order, so later product edits never change it.
     */
    private function snapshotItems(array $items): array
    {
        $ids = array_values(array_unique(array_filter(array_map(
            fn ($i) => isset($i['product_id']) ? (int) $i['product_id'] : null,
            $items
        ))));

        $products = $ids
            ? Product::whereIn('id', $ids)->get(['id', 'name', 'slug', 'sku', 'image', 'colors'])->keyBy('id')
            : collect();

        $out = [];
        foreach ($items as $item) {
            $product = isset($item['product_id']) ? $products->get((int) $item['product_id']) : null;

            $color = trim((string) ($item['color'] ?? ''));
            if (strtolower($color) === 'default') {
                $color = '';
            }

            $image = trim((string) ($item['image'] ?? ''));
            $sku = trim((string) ($item['sku'] ?? ''));
            $slug = trim((string) ($item['slug'] ?? ''));

            if ($product) {
                $sku = $sku !== '' ? $sku : (string) ($product->sku ?: Product::defaultSku((int) $product->id));
                $slug = $slug !== '' ? $slug : (string) $product->slug;

                // Prefer the chosen colour's own photo when the product has one.
                if ($color !== '' && is_array($product->colors)) {
                    foreach ($product->colors as $c) {
                        if (strcasecmp((string) ($c['name'] ?? ''), $color) === 0 && !empty($c['image'])) {
                            $image = $image !== '' ? $image : (string) $c['image'];
                            break;
                        }
                    }
                }
                $image = $image !== '' ? $image : (string) $product->image;
            }

            $variantSku = trim((string) ($item['variant_sku'] ?? ''));
            if ($variantSku === '' && $sku !== '') {
                $parts = array_filter([
                    $sku,
                    $color !== '' ? strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $color) ?? '') : null,
                    !empty($item['size']) ? strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', (string) $item['size']) ?? '') : null,
                ]);
                $variantSku = implode('-', $parts);
            }

            $out[] = [
                'product_id' => $product?->id ?? (isset($item['product_id']) ? (int) $item['product_id'] : null),
                'name' => $item['name'],
                'slug' => $slug !== '' ? $slug : null,
                'sku' => $sku !== '' ? $sku : null,
                'variant_sku' => $variantSku !== '' ? $variantSku : null,
                'image' => $image !== '' ? $image : null,
                'size' => $item['size'] ?? null,
                'color' => $color !== '' ? $color : null,
                'price' => (float) $item['price'],
                'quantity' => (int) $item['quantity'],
            ];
        }

        return $out;
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
