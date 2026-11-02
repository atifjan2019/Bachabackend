<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Mail\CustomerPasswordResetMail;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Customer sign-in for the storefront on the `customer` session guard. The
 * API token flow in Api\V1\AuthController is untouched; both share the
 * customers table and the customer_password_reset_tokens table.
 */
class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('shop.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'email.email' => 'Enter a valid email',
            'password.min' => 'Password must be at least 6 characters',
        ]);

        if (! Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Check your email and password'])
                ->with('toast', ['title' => 'Login failed', 'description' => 'Check your email and password', 'tone' => 'error']);
        }

        $request->session()->regenerate();
        $customer = Auth::guard('customer')->user();

        return redirect()->intended(route('shop.account'))
            ->with('toast', ['title' => 'Welcome back', 'description' => $customer->name, 'tone' => 'success']);
    }

    public function showRegister(): View
    {
        return view('shop.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.min' => 'Please enter your name',
            'email.email' => 'Enter a valid email',
            'email.unique' => 'An account with this email already exists',
            'password.min' => 'Password must be at least 6 characters',
            'password.confirmed' => 'Passwords do not match',
        ]);

        $customer = Customer::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->route('shop.account')
            ->with('toast', ['title' => 'Account created', 'description' => 'Welcome, '.$customer->name, 'tone' => 'success']);
    }

    public function showForgot(): View
    {
        return view('shop.auth.forgot-password');
    }

    /** Always answers the same way so registered emails can't be enumerated. */
    public function forgot(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']], ['email.email' => 'Enter a valid email']);

        $customer = Customer::where('email', $request->input('email'))->first();
        if ($customer) {
            $token = Str::random(64);
            DB::table('customer_password_reset_tokens')->updateOrInsert(
                ['email' => $customer->email],
                ['token' => Hash::make($token), 'created_at' => now()]
            );
            $resetUrl = route('shop.password.reset', ['token' => $token, 'email' => $customer->email]);
            try {
                Mail::to($customer->email)->send(new CustomerPasswordResetMail($customer, $resetUrl));
            } catch (\Throwable $e) {
                Log::warning('Password reset email failed for '.$customer->email.': '.$e->getMessage());
            }
        }

        return redirect()->route('shop.password.request')->with('sent', true);
    }

    public function showReset(Request $request): View
    {
        return view('shop.auth.reset-password', [
            'token' => (string) $request->query('token', ''),
            'email' => (string) $request->query('email', ''),
        ]);
    }

    /** Tokens are single-use and expire after 60 minutes, exactly like the API. */
    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password.min' => 'Password must be at least 6 characters',
            'password.confirmed' => 'Passwords do not match',
        ]);

        $invalid = fn () => back()->withErrors(['token' => 'This password reset link is invalid or has expired.'])
            ->with('toast', ['title' => 'Could not reset password', 'description' => 'This reset link is invalid or has expired', 'tone' => 'error']);

        $record = DB::table('customer_password_reset_tokens')->where('email', $data['email'])->first();
        if (! $record || ! Hash::check($data['token'], $record->token)) {
            return $invalid();
        }
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('customer_password_reset_tokens')->where('email', $data['email'])->delete();

            return $invalid();
        }

        $customer = Customer::where('email', $data['email'])->first();
        if (! $customer) {
            return $invalid();
        }

        $customer->update(['password' => Hash::make($data['password'])]);
        DB::table('customer_password_reset_tokens')->where('email', $data['email'])->delete();

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->route('shop.account')
            ->with('toast', ['title' => 'Password reset', 'description' => 'You are signed in with your new password', 'tone' => 'success']);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('shop.home');
    }
}
