<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;

class AdminLoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        
        $logo_url = Setting::where('setting_key', 'logo_url')->value('setting_value') 
            ?? 'https://storage.googleapis.com/msgsndr/1a90x6oFSkLT95cM2HEE/media/698b19b767d7491d79450638.png';

        return view('admin.login', compact('logo_url'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $credentials = [
            'username' => $request->username,
            'password' => $request->password,
        ];

        // Also allow email login implicitly if username matches an email
        $user = \App\Models\AdminUser::where('username', $request->username)
                    ->orWhere('email', $request->username)
                    ->first();

        if ($user) {
            // Check lockout
            if ($user->locked_until && strtotime($user->locked_until) > time()) {
                $remaining = ceil((strtotime($user->locked_until) - time()) / 60);
                return back()->withErrors(['error' => "Account locked. Try again in {$remaining} minute(s)."]);
            }

            if (Auth::guard('admin')->attempt(['id' => $user->id, 'password' => $request->password])) {
                $user->update([
                    'login_attempts' => 0,
                    'locked_until' => null,
                    'last_login' => now()
                ]);
                
                $request->session()->regenerate();
                return redirect()->intended(route('admin.dashboard'));
            } else {
                $max_attempts = 5;
                $lockout_minutes = 15;
                
                $attempts = $user->login_attempts + 1;
                $lock = null;
                if ($attempts >= $max_attempts) {
                    $lock = date('Y-m-d H:i:s', strtotime("+{$lockout_minutes} minutes"));
                }
                
                $user->update([
                    'login_attempts' => $attempts,
                    'locked_until' => $lock
                ]);

                $remaining = $max_attempts - $attempts;
                if ($remaining > 0) {
                    return back()->withErrors(['error' => "Invalid credentials. {$remaining} attempt(s) remaining."]);
                } else {
                    return back()->withErrors(['error' => "Account locked for {$lockout_minutes} minutes due to too many failed attempts."]);
                }
            }
        }

        return back()->withErrors([
            'error' => 'Invalid username or password.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
