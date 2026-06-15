<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    private const ALLOWED_KEYS = [
        'business_name',
        'business_email',
        'business_phone',
        'business_address',
        'logo_url',
        'footer_logo_url',
        'favicon_url',
        'shipping_fee',
        'free_shipping_threshold',
        'order_notification_email',
        'email_from_name',
        'facebook_url',
        'instagram_url',
        'tiktok_url',
        'whatsapp_number',
        'home_highlight_title',
        'home_highlight_description',
        'home_highlight_image',
        'home_highlight_button',
        'home_highlight_link',
        'footer_about',
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
            'footer_logo_url' => 'nullable|string|max:2000',
            'favicon_url' => 'nullable|string|max:2000',
            'shipping_fee' => 'nullable|numeric|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'order_notification_email' => 'nullable|email|max:255',
            'email_from_name' => 'nullable|string|max:255',
            'facebook_url' => 'nullable|string|max:2000',
            'instagram_url' => 'nullable|string|max:2000',
            'tiktok_url' => 'nullable|string|max:2000',
            'whatsapp_number' => 'nullable|string|max:100',
            'home_highlight_title' => 'nullable|string|max:255',
            'home_highlight_description' => 'nullable|string|max:2000',
            'home_highlight_image' => 'nullable|string|max:2000',
            'home_highlight_button' => 'nullable|string|max:100',
            'home_highlight_link' => 'nullable|string|max:2000',
            'footer_about' => 'nullable|string|max:2000',
            'logo_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,svg|max:5120',
            'footer_logo_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,svg|max:5120',
            'favicon_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,svg,ico|max:2048',
            'home_highlight_image_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
        ]);

        // Handle logo file upload
        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $filename = 'logo-' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $disk = env('FILESYSTEM_DISK', 'public');
            $file->storeAs('branding', $filename, $disk);
            $validated['logo_url'] = Storage::disk($disk)->url('branding/' . $filename);
        }

        // Handle footer logo file upload
        if ($request->hasFile('footer_logo_file')) {
            $file = $request->file('footer_logo_file');
            $filename = 'footer-logo-' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $disk = env('FILESYSTEM_DISK', 'public');
            $file->storeAs('branding', $filename, $disk);
            $validated['footer_logo_url'] = Storage::disk($disk)->url('branding/' . $filename);
        }

        // Handle favicon file upload
        if ($request->hasFile('favicon_file')) {
            $file = $request->file('favicon_file');
            $filename = 'favicon-' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $disk = env('FILESYSTEM_DISK', 'public');
            $file->storeAs('branding', $filename, $disk);
            $validated['favicon_url'] = Storage::disk($disk)->url('branding/' . $filename);
        }

        // Handle homepage highlight image upload
        if ($request->hasFile('home_highlight_image_file')) {
            $file = $request->file('home_highlight_image_file');
            $filename = 'highlight-' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $disk = env('FILESYSTEM_DISK', 'public');
            $file->storeAs('branding', $filename, $disk);
            $validated['home_highlight_image'] = Storage::disk($disk)->url('branding/' . $filename);
        }

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
