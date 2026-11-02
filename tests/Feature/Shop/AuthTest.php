<?php

namespace Tests\Feature\Shop;

use App\Mail\CustomerPasswordResetMail;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): Customer
    {
        return Customer::create(['name' => 'Ayesha Khan', 'email' => 'ayesha@example.com', 'password' => Hash::make('secret1')]);
    }

    public function test_auth_pages_render(): void
    {
        $this->get('/login')->assertOk()->assertSee('Welcome back');
        $this->get('/register')->assertOk()->assertSee('Create account');
        $this->get('/forgot-password')->assertOk()->assertSee('Forgot password?');
        $this->get('/reset-password?token=x&email=a@b.c')->assertOk()->assertSee('Set a new password');
        $this->get('/reset-password')->assertOk()->assertSee('This reset link is incomplete.');
    }

    public function test_register_signs_the_customer_in(): void
    {
        $this->post('/register', [
            'name' => 'New Customer',
            'email' => 'new@example.com',
            'password' => 'secret1',
            'password_confirmation' => 'secret1',
        ])->assertRedirect(route('shop.account'));

        $this->assertAuthenticated('customer');
        $this->assertDatabaseHas('customers', ['email' => 'new@example.com']);
    }

    public function test_register_rejects_mismatched_passwords_and_duplicate_email(): void
    {
        $this->customer();
        $this->from('/register')->post('/register', [
            'name' => 'Dup',
            'email' => 'ayesha@example.com',
            'password' => 'secret1',
            'password_confirmation' => 'other1',
        ])->assertRedirect('/register')->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest('customer');
    }

    public function test_login_and_logout(): void
    {
        $this->customer();

        $this->post('/login', ['email' => 'ayesha@example.com', 'password' => 'wrong1'])
            ->assertRedirect()->assertSessionHasErrors('email');
        $this->assertGuest('customer');

        $this->post('/login', ['email' => 'ayesha@example.com', 'password' => 'secret1'])
            ->assertRedirect(route('shop.account'));
        $this->assertAuthenticated('customer');

        $this->post('/logout')->assertRedirect(route('shop.home'));
        $this->assertGuest('customer');
    }

    public function test_keep_me_signed_in_sets_a_remember_token(): void
    {
        $customer = $this->customer();

        $this->post('/login', ['email' => 'ayesha@example.com', 'password' => 'secret1', 'remember' => '1'])
            ->assertRedirect(route('shop.account'))
            ->assertCookie(auth('customer')->getRecallerName());

        $this->assertAuthenticated('customer');
        $this->assertNotEmpty($customer->fresh()->remember_token);
    }

    public function test_account_pages_require_a_signed_in_customer(): void
    {
        foreach (['/account', '/account/orders', '/account/profile', '/account/orders/BS-1'] as $path) {
            $this->get($path)->assertRedirect(route('shop.login'));
        }
    }

    public function test_signed_in_customer_is_sent_from_login_to_account(): void
    {
        $this->actingAs($this->customer(), 'customer')->get('/login')->assertRedirect(route('shop.account'));
    }

    public function test_account_pages_show_orders_and_profile(): void
    {
        $customer = $this->customer();
        $order = Order::create([
            'customer_name' => 'Ayesha Khan', 'customer_email' => 'ayesha@example.com', 'customer_phone' => '03001234567',
            'shipping_address' => 'House 12, Punjab', 'city' => 'Lahore', 'country' => 'Pakistan',
            'items' => [['name' => 'Ruby Winter Shawl', 'price' => 4500, 'quantity' => 1, 'size' => 'M', 'sku' => 'BS-SH-001']],
            'subtotal' => 4500, 'shipping_fee' => 250, 'total_amount' => 4750, 'payment_method' => 'Cash on Delivery', 'status' => 'Shipped',
        ]);

        $this->actingAs($customer, 'customer');
        $this->get('/account')->assertOk()->assertSee('Assalam o Alaikum, Ayesha')->assertSee('1 order');
        $this->get('/account/orders')->assertOk()->assertSee($order->reference)->assertSee('Ruby Winter Shawl');
        $this->get('/account/orders/'.$order->reference)->assertOk()->assertSee('Order '.$order->reference)->assertSee('Reorder items')->assertSee('Shipped');
        $this->get('/account/orders/BS-999999')->assertOk()->assertSee('Order not found');
        $this->get('/account/profile')->assertOk()->assertSee('ayesha@example.com');
    }

    public function test_profile_update(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer, 'customer')
            ->put('/account/profile', ['name' => 'Ayesha K.', 'phone' => '03009876543', 'address' => 'DHA Phase 5, Lahore'])
            ->assertRedirect(route('shop.account.profile'));

        $customer->refresh();
        $this->assertSame('Ayesha K.', $customer->name);
        $this->assertSame('03009876543', $customer->phone);
        $this->assertSame('DHA Phase 5, Lahore', $customer->address);

        $this->actingAs($customer, 'customer')->from('/account/profile')
            ->put('/account/profile', ['name' => 'Ayesha K.', 'phone' => '123'])
            ->assertSessionHasErrors('phone');
    }

    public function test_forgot_and_reset_password_flow(): void
    {
        Mail::fake();
        $customer = $this->customer();

        $this->post('/forgot-password', ['email' => 'nobody@example.com'])->assertRedirect(route('shop.password.request'));
        Mail::assertNothingSent();

        $this->post('/forgot-password', ['email' => 'ayesha@example.com'])->assertRedirect(route('shop.password.request'));

        $token = null;
        Mail::assertSent(CustomerPasswordResetMail::class, function (CustomerPasswordResetMail $mail) use (&$token) {
            parse_str((string) parse_url($mail->resetUrl, PHP_URL_QUERY), $query);
            $token = $query['token'] ?? null;

            return str_contains($mail->resetUrl, '/reset-password?') && $token !== null;
        });
        $this->assertDatabaseHas('customer_password_reset_tokens', ['email' => 'ayesha@example.com']);

        $this->post('/reset-password', ['email' => 'ayesha@example.com', 'token' => 'bogus', 'password' => 'newpass1', 'password_confirmation' => 'newpass1'])
            ->assertSessionHasErrors('token');

        $this->post('/reset-password', ['email' => 'ayesha@example.com', 'token' => $token, 'password' => 'newpass1', 'password_confirmation' => 'newpass1'])
            ->assertRedirect(route('shop.account'));

        $this->assertAuthenticated('customer');
        $this->assertTrue(Hash::check('newpass1', $customer->fresh()->password));
        $this->assertSame(0, DB::table('customer_password_reset_tokens')->where('email', 'ayesha@example.com')->count());
    }
}
