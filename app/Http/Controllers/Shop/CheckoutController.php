<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Support\Storefront\SiteSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    /** Same list and order as the Next.js checkout. */
    public const PROVINCES = [
        'Sindh', 'Punjab', 'Islamabad Capital Territory', 'Khyber Pakhtunkhwa',
        'Balochistan', 'Azad Kashmir', 'Gilgit-Baltistan',
    ];

    public const PK_PHONE = '/^(\+92|0)?3[0-9]{9}$/';

    public function __construct(private readonly OrderService $orders, private readonly SiteSettings $settings)
    {
    }

    public function index(): View
    {
        $methods = $this->settings->enabledPaymentMethods();

        return view('shop.checkout.index', [
            'provinces' => self::PROVINCES,
            'methods' => $methods,
            'paymentRows' => $this->paymentRows(),
            'defaultMethod' => in_array('cod', $methods, true) ? 'cod' : $methods[0],
        ]);
    }

    /**
     * Normal multipart POST. The browser cart arrives serialised in `cart_json`
     * (the only part of checkout that needs JavaScript); everything else is a
     * plain form so validation errors come back inline with old input.
     */
    public function store(Request $request): RedirectResponse
    {
        $customer = auth('customer')->user();
        $methods = $this->settings->enabledPaymentMethods();
        $method = (string) $request->input('payment_method', 'cod');
        $requiresReceipt = $method !== 'cod';

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['required', 'string', 'regex:'.self::PK_PHONE],
            'email' => [$customer ? 'nullable' : 'required', 'email', 'max:255'],
            'full_address' => ['required', 'string', 'min:8', 'max:1500'],
            'city' => ['required', 'string', 'min:2', 'max:255'],
            'province' => ['required', Rule::in(self::PROVINCES)],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', Rule::in($methods)],
            'payment_receipt' => [$requiresReceipt ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,avif,pdf', 'max:10240'],
            'cart_json' => ['required', 'string'],
        ], [
            'name.required' => 'Please enter your full name',
            'name.min' => 'Please enter your full name',
            'phone.required' => 'Enter a valid Pakistan phone number',
            'phone.regex' => 'Enter a valid Pakistan phone number',
            'email.required' => 'Enter your email so we can send your order confirmation',
            'email.email' => 'Enter a valid email',
            'full_address.required' => 'Please share the full address',
            'full_address.min' => 'Please share the full address',
            'city.required' => 'City is required',
            'city.min' => 'City is required',
            'province.required' => 'Province is required',
            'province.in' => 'Province is required',
            'payment_method.in' => 'Please choose an available payment method',
            'payment_receipt.required' => 'Please upload your payment receipt to continue.',
            'payment_receipt.mimes' => 'Upload an image or PDF of your payment receipt.',
            'payment_receipt.max' => 'The receipt must be 10 MB or smaller.',
            'cart_json.required' => 'Your cart could not be read. Please try again.',
        ]);

        $items = $this->decodeCart($validated['cart_json']);

        $receiptUrl = null;
        if ($requiresReceipt && $request->hasFile('payment_receipt')) {
            // media_store() verifies the write (and re-encodes images); a
            // storage failure must not produce an order with a dead receipt link.
            try {
                $receiptUrl = media_store($request->file('payment_receipt'), 'receipts');
            } catch (ValidationException) {
                throw ValidationException::withMessages([
                    'payment_receipt' => 'We could not save your payment screenshot. Please try again, or choose Cash on Delivery and send the receipt on WhatsApp.',
                ]);
            }
        }

        // The Order model has no province/postal/notes columns: fold them into the address.
        $address = trim($validated['full_address']).', '.$validated['province'];
        if (! empty($validated['postal_code'])) {
            $address .= ' '.trim($validated['postal_code']);
        }
        if (! empty($validated['notes'])) {
            $address .= "\nNotes: ".trim($validated['notes']);
        }

        $order = $this->orders->create([
            'customer_name' => $validated['name'],
            'customer_email' => $validated['email'] ?: $customer->email,
            'customer_phone' => $validated['phone'],
            'shipping_address' => $address,
            'city' => $validated['city'],
            'country' => 'Pakistan',
            'items' => $items,
            'payment_method' => SiteSettings::PAYMENT_LABELS[$method] ?? $method,
            'payment_receipt' => $receiptUrl,
        ], $customer);

        return redirect()
            ->route('shop.checkout.success', ['id' => $order->reference])
            ->with('toast', ['title' => 'Order placed', 'description' => 'Order '.$order->reference.' confirmed', 'tone' => 'success']);
    }

    public function success(Request $request): View
    {
        $reference = trim((string) $request->query('id', ''));
        $order = $reference !== '' ? Order::where('reference', $reference)->first() : null;
        abort_if(! $order, 404);

        return view('shop.checkout.success', ['order' => $order]);
    }

    /**
     * The browser cart as order lines, validated with the API's item rules.
     *
     * @return list<array>
     */
    private function decodeCart(string $json): array
    {
        $items = json_decode($json, true);
        if (! is_array($items) || $items === []) {
            throw ValidationException::withMessages(['cart_json' => 'Your cart is empty. Add something before checking out.']);
        }

        $itemRules = array_filter(OrderService::rules(), fn ($key) => str_starts_with($key, 'items'), ARRAY_FILTER_USE_KEY);
        $validator = Validator::make(['items' => array_values($items)], $itemRules);
        if ($validator->fails()) {
            throw ValidationException::withMessages(['cart_json' => 'Your cart could not be read. Please refresh and try again.']);
        }

        return $validator->validated()['items'];
    }

    /** Admin-configured account details per payment method (components/checkout/paymentDetails.tsx). */
    private function paymentRows(): array
    {
        $s = $this->settings;

        return [
            'bank_transfer' => [
                ['label' => 'Bank', 'value' => $s->get('bank_name')],
                ['label' => 'Account Title', 'value' => $s->get('bank_account_title')],
                ['label' => 'Account Number', 'value' => $s->get('bank_account_number'), 'copy' => true],
                ['label' => 'IBAN', 'value' => $s->get('bank_iban'), 'copy' => true],
            ],
            'easypaisa' => [
                ['label' => 'Account Name', 'value' => $s->get('easypaisa_account_name')],
                ['label' => 'EasyPaisa Number', 'value' => $s->get('easypaisa_number'), 'copy' => true],
            ],
            'jazzcash' => [
                ['label' => 'Account Name', 'value' => $s->get('jazzcash_account_name')],
                ['label' => 'JazzCash Number', 'value' => $s->get('jazzcash_number'), 'copy' => true],
            ],
        ];
    }
}
