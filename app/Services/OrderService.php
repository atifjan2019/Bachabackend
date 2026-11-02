<?php

namespace App\Services;

use App\Mail\NewOrderMail;
use App\Mail\OrderPlacedMail;
use App\Models\AbandonedCart;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Turns a validated checkout submission into an Order.
 *
 * Shared by the JSON API (/api/v1/orders) and the web checkout so both price
 * the order identically: catalogue snapshot per line, server-side totals, the
 * admin's delivery rule, customer upsert, abandoned-cart cleanup and the two
 * confirmation emails.
 */
class OrderService
{
    public const COD_LABEL = 'Cash on Delivery';

    /** Validation rules both entry points apply to the order payload. */
    public static function rules(): array
    {
        return [
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
        ];
    }

    /** True for Cash on Delivery (the only method that needs no receipt). */
    public static function isCod(?string $method): bool
    {
        $method = strtolower(trim((string) ($method ?: self::COD_LABEL)));

        return in_array($method, ['cod', 'cash on delivery'], true);
    }

    /** Non-COD methods must include a payment receipt/screenshot. */
    public static function needsReceipt(?string $method, ?string $receipt): bool
    {
        return ! self::isCod($method) && empty($receipt);
    }

    /**
     * Create the order. $validated follows rules(); $authCustomer, when given,
     * owns the order (its email replaces the submitted one for linking).
     */
    public function create(array $validated, ?Customer $authCustomer = null): Order
    {
        $items = $this->snapshotItems($validated['items']);

        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $shippingFee = self::shippingFor($subtotal);
        $totalAmount = $subtotal + $shippingFee;

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
            'payment_method' => $validated['payment_method'] ?? self::COD_LABEL,
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

        // Checkout completed — this email is no longer a recovery opportunity.
        AbandonedCart::where('email', strtolower(trim($validated['customer_email'])))->delete();

        $this->sendEmails($order);

        return $order;
    }

    /**
     * Delivery charge for a subtotal, from the admin's Shipping settings: the
     * base charge, waived at or above the threshold while free shipping is ON.
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
    public function snapshotItems(array $items): array
    {
        $ids = array_values(array_unique(array_filter(array_map(
            fn ($i) => isset($i['product_id']) ? (int) $i['product_id'] : null,
            $items
        ))));

        $columns = array_values(array_filter([
            'id', 'name', 'slug', 'image',
            Product::hasColumn('sku') ? 'sku' : null,
            Product::hasColumn('colors') ? 'colors' : null,
        ]));
        $products = $ids
            ? Product::whereIn('id', $ids)->get($columns)->keyBy('id')
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
                        if (strcasecmp((string) ($c['name'] ?? ''), $color) === 0 && ! empty($c['image'])) {
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
                    ! empty($item['size']) ? strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', (string) $item['size']) ?? '') : null,
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

    /** Branded confirmation + admin notification. Failures never break the order. */
    private function sendEmails(Order $order): void
    {
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
            Log::warning('Order email failed for order #'.$order->id.': '.$e->getMessage());
        }
    }
}
