<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_order_and_updates_customer_metrics(): void
    {
        $payload = [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '923001234567',
            'shipping_address' => 'Street 1, Lahore',
            'city' => 'Lahore',
            'country' => 'Pakistan',
            'items' => [
                [
                    'name' => 'Premium Shawl',
                    'price' => 4500,
                    'quantity' => 1,
                    'size' => 'M',
                ],
            ],
            'subtotal' => 4500,
            'shipping_fee' => 250,
            'total_amount' => 4750,
            'payment_method' => 'Cash on Delivery',
        ];

        $response = $this->postJson('/api/v1/orders', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'Pending');

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('customers', [
            'email' => 'john@example.com',
            'orders_count' => 1,
        ]);

        $order = Order::firstOrFail();
        $customer = Customer::where('email', 'john@example.com')->firstOrFail();

        $this->assertEquals('Lahore', $order->city);
        $this->assertEquals('Pakistan', $order->country);
        $this->assertEquals('Cash on Delivery', $order->payment_method);
        $this->assertEquals('4750.00', (string) $order->total_amount);
        $this->assertEquals('4750.00', (string) $customer->total_spent);
    }
}
