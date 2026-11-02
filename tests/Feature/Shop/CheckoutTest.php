<?php

namespace Tests\Feature\Shop;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function cartJson(): string
    {
        return json_encode([
            ['name' => 'Ruby Winter Shawl', 'price' => 4500, 'quantity' => 1, 'size' => 'M', 'slug' => 'ruby-winter-shawl', 'sku' => 'BS-SH-001'],
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ayesha Khan',
            'phone' => '03001234567',
            'email' => 'ayesha@example.com',
            'full_address' => 'House 12, Street 4, Gulberg',
            'city' => 'Lahore',
            'province' => 'Punjab',
            'postal_code' => '54000',
            'notes' => 'Call before delivery',
            'payment_method' => 'cod',
            'cart_json' => $this->cartJson(),
        ], $overrides);
    }

    public function test_cart_and_checkout_pages_render(): void
    {
        $this->get('/cart')->assertOk()->assertSee('Your Cart');
        $this->get('/checkout')->assertOk()->assertSee('Checkout')->assertSee('Cash on Delivery');
    }

    public function test_cod_checkout_creates_an_order_and_redirects_to_success(): void
    {
        $response = $this->post('/checkout', $this->payload());

        $order = Order::firstOrFail();
        $response->assertRedirect(route('shop.checkout.success', ['id' => $order->reference]));

        $this->assertSame('Cash on Delivery', $order->payment_method);
        $this->assertSame('Lahore', $order->city);
        $this->assertSame('Pakistan', $order->country);
        $this->assertStringContainsString('Punjab 54000', $order->shipping_address);
        $this->assertStringContainsString('Notes: Call before delivery', $order->shipping_address);
        $this->assertEquals('4500.00', (string) $order->subtotal);
        $this->assertEquals('250.00', (string) $order->shipping_fee);
        $this->assertEquals('4750.00', (string) $order->total_amount);
        $this->assertDatabaseHas('customers', ['email' => 'ayesha@example.com', 'orders_count' => 1]);

        $this->get('/checkout/success?id='.$order->reference)
            ->assertOk()
            ->assertSee('Order placed, shukria!')
            ->assertSee($order->reference)
            ->assertSee('Ruby Winter Shawl');
    }

    public function test_non_cod_without_receipt_fails_validation(): void
    {
        $this->from('/checkout')
            ->post('/checkout', $this->payload(['payment_method' => 'bank_transfer']))
            ->assertRedirect('/checkout')
            ->assertSessionHasErrors('payment_receipt');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_bank_transfer_with_receipt_stores_the_file(): void
    {
        Storage::fake('public');
        config(['filesystems.default' => 'public']);

        $this->post('/checkout', $this->payload([
            'payment_method' => 'bank_transfer',
            'payment_receipt' => UploadedFile::fake()->image('receipt.png'),
        ]))->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame('Bank Transfer', $order->payment_method);
        $this->assertNotEmpty($order->payment_receipt);
        $this->assertCount(1, Storage::disk('public')->files('receipts'));
    }

    public function test_guest_must_give_an_email(): void
    {
        $this->from('/checkout')
            ->post('/checkout', $this->payload(['email' => '']))
            ->assertRedirect('/checkout')
            ->assertSessionHasErrors('email');
    }

    public function test_invalid_phone_and_empty_cart_are_rejected(): void
    {
        $this->from('/checkout')
            ->post('/checkout', $this->payload(['phone' => '12345']))
            ->assertSessionHasErrors('phone');

        $this->from('/checkout')
            ->post('/checkout', $this->payload(['cart_json' => '[]']))
            ->assertSessionHasErrors('cart_json');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_signed_in_customer_with_blank_email_uses_their_account_email(): void
    {
        $customer = Customer::create(['name' => 'Bilal', 'email' => 'bilal@example.com', 'password' => Hash::make('secret1')]);

        $this->actingAs($customer, 'customer')
            ->post('/checkout', $this->payload(['email' => '']))
            ->assertRedirect();

        $this->assertSame('bilal@example.com', Order::firstOrFail()->customer_email);
    }

    public function test_success_page_404s_for_unknown_reference(): void
    {
        $this->get('/checkout/success?id=BS-000000')->assertNotFound();
        $this->get('/checkout/success')->assertNotFound();
    }
}
