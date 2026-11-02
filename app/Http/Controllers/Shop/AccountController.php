<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /** Status → badge tone, as in the Next account pages. */
    public const STATUS_TONE = [
        'pending' => 'gold',
        'paid' => 'green',
        'processing' => 'amber',
        'shipped' => 'amber',
        'delivered' => 'green',
        'cancelled' => 'red',
        'canceled' => 'red',
    ];

    public const STEPS = ['pending', 'paid', 'processing', 'shipped', 'delivered'];

    public function index(Request $request): View
    {
        $customer = $request->user('customer');

        return view('shop.account.index', [
            'orderCount' => $this->ordersFor($customer->email)->count(),
        ]);
    }

    public function orders(Request $request): View
    {
        return view('shop.account.orders', [
            'orders' => $this->ordersFor($request->user('customer')->email)->orderByDesc('created_at')->get(),
        ]);
    }

    public function order(Request $request, string $reference): View
    {
        $order = $this->ordersFor($request->user('customer')->email)
            ->where(fn ($q) => $q->where('reference', $reference)->orWhere('id', $reference))
            ->first();

        return view('shop.account.order', ['order' => $order]);
    }

    public function profile(): View
    {
        return view('shop.account.profile');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:'.CheckoutController::PK_PHONE],
            'address' => ['nullable', 'string', 'max:1000'],
        ], [
            'phone.regex' => 'Enter a valid Pakistan phone number',
        ]);

        $request->user('customer')->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        return redirect()->route('shop.account.profile')
            ->with('toast', ['title' => 'Profile updated', 'tone' => 'success']);
    }

    private function ordersFor(string $email)
    {
        return Order::query()->where('customer_email', $email);
    }
}
