<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
