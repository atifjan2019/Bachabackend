<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    private const ALLOWED_KEYS = [
        'business_name',
        'business_email',
        'business_phone',
        'business_address',
        'logo_url',
        'favicon_url',
        'shipping_fee',
        'free_shipping_threshold',
        'order_notification_email',
        'email_from_name',
        'facebook_url',
        'instagram_url',
        'whatsapp_number',
    ];

    public function index()
    {
        $settings = Setting::pluck('setting_value', 'setting_key')->toArray();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'nullable|string|max:255',
            'business_email' => 'nullable|email|max:255',
            'business_phone' => 'nullable|string|max:100',
            'business_address' => 'nullable|string|max:2000',
            'logo_url' => 'nullable|string|max:2000',
            'favicon_url' => 'nullable|string|max:2000',
            'shipping_fee' => 'nullable|numeric|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'order_notification_email' => 'nullable|email|max:255',
            'email_from_name' => 'nullable|string|max:255',
            'facebook_url' => 'nullable|string|max:2000',
            'instagram_url' => 'nullable|string|max:2000',
            'whatsapp_number' => 'nullable|string|max:100',
        ]);

        foreach (self::ALLOWED_KEYS as $key) {
            if (!array_key_exists($key, $validated)) {
                continue;
            }

            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $validated[$key]]
            );
        }

        return redirect()->route('admin.settings.index')->with('success', 'Settings saved successfully.');
    }
}
