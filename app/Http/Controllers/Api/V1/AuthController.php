<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\CustomerPasswordResetMail;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers',
            'password' => 'required|string|min:6',
        ]);

        $token = Str::random(60);

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'api_token' => hash('sha256', $token),
        ]);

        return response()->json([
            'data' => [
                'user' => $customer,
                'token' => $token,
            ]
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $customer = Customer::where('email', $request->email)->first();

        if (! $customer || ! Hash::check($request->password, $customer->password)) {
            return response()->json([
                'message' => 'Invalid matching credentials.'
            ], 401);
        }

        $token = Str::random(60);
        $customer->update([
            'api_token' => hash('sha256', $token),
        ]);

        return response()->json([
            'data' => [
                'user' => $customer,
                'token' => $token,
            ]
        ]);
    }

    /**
     * Send a password reset link to the customer's email. Always returns a
     * generic success response so we never reveal which emails are registered.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $generic = response()->json([
            'message' => 'If an account exists for that email, a reset link has been sent.',
        ]);

        $customer = Customer::where('email', $request->email)->first();
        if (! $customer) {
            return $generic;
        }

        $token = Str::random(64);

        DB::table('customer_password_reset_tokens')->updateOrInsert(
            ['email' => $customer->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        $frontend = config('app.frontend_url')
            ?: (Setting::where('setting_key', 'canonical_base_url')->value('setting_value')
                ?: config('app.url'));
        // FRONTEND_URL may hold a comma-separated CORS allow-list; use the first origin.
        $base = rtrim(trim(explode(',', (string) $frontend)[0]), '/');
        $resetUrl = $base . '/reset-password?token=' . $token . '&email=' . urlencode($customer->email);

        try {
            Mail::to($customer->email)->send(new CustomerPasswordResetMail($customer, $resetUrl));
        } catch (\Throwable $e) {
            Log::warning('Password reset email failed for ' . $customer->email . ': ' . $e->getMessage());
        }

        return $generic;
    }

    /**
     * Verify a reset token and set a new password. Tokens are single-use and
     * expire after 60 minutes. On success the customer is issued a fresh
     * api_token so they're signed in immediately.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $invalid = response()->json([
            'message' => 'This password reset link is invalid or has expired.',
        ], 422);

        $record = DB::table('customer_password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $record || ! Hash::check($request->token, $record->token)) {
            return $invalid;
        }

        // Expire links after 60 minutes.
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('customer_password_reset_tokens')->where('email', $request->email)->delete();
            return $invalid;
        }

        $customer = Customer::where('email', $request->email)->first();
        if (! $customer) {
            return $invalid;
        }

        $apiToken = Str::random(60);
        $customer->update([
            'password' => Hash::make($request->password),
            'api_token' => hash('sha256', $apiToken),
        ]);

        DB::table('customer_password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Your password has been reset.',
            'data' => [
                'user' => $customer,
                'token' => $apiToken,
            ],
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        $customer = $request->user('api');
        
        if (!$customer) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'data' => $customer
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $customer = $request->user('api');

        if (!$customer) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
        ]);

        $customer->update($request->only('name', 'phone', 'address'));

        return response()->json([
            'data' => $customer
        ]);
    }

    public function orders(Request $request): JsonResponse
    {
        $customer = $request->user('api');

        if (!$customer) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $orders = Order::where('customer_email', $customer->email)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $orders
        ]);
    }

    public function order(Request $request, $id): JsonResponse
    {
        $customer = $request->user('api');

        if (!$customer) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $order = Order::where('customer_email', $customer->email)->where('id', $id)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json([
            'data' => $order
        ]);
    }
}
