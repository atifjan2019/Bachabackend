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
        'intro_enabled',
        'intro_bg_type',
        'intro_title',
        'intro_subtitle',
        'intro_image',
        'intro_video_url',
        'intro_social_url',
        'intro_button_text',
        'promo_enabled',
        'promo_media_type',
        'promo_title',
        'promo_subtitle',
        'promo_image',
        'promo_video_url',
        'promo_link',
        'promo_button_text',
        'cod_enabled',
        'bank_transfer_enabled',
        'easypaisa_enabled',
        'jazzcash_enabled',
        'bank_name',
        'bank_account_title',
        'bank_account_number',
        'bank_iban',
        'easypaisa_account_name',
        'easypaisa_number',
        'jazzcash_account_name',
        'jazzcash_number',
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
            'intro_bg_type' => 'nullable|in:image,video',
            'intro_title' => 'nullable|string|max:255',
            'intro_subtitle' => 'nullable|string|max:2000',
            'intro_image' => 'nullable|string|max:2000',
            'intro_video_url' => 'nullable|string|max:2000',
            'intro_social_url' => 'nullable|string|max:2000',
            'intro_button_text' => 'nullable|string|max:100',
            'promo_media_type' => 'nullable|in:image,video',
            'promo_title' => 'nullable|string|max:255',
            'promo_subtitle' => 'nullable|string|max:2000',
            'promo_image' => 'nullable|string|max:2000',
            'promo_video_url' => 'nullable|string|max:2000',
            'promo_link' => 'nullable|string|max:2000',
            'promo_button_text' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_title' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_iban' => 'nullable|string|max:100',
            'easypaisa_account_name' => 'nullable|string|max:255',
            'easypaisa_number' => 'nullable|string|max:50',
            'jazzcash_account_name' => 'nullable|string|max:255',
            'jazzcash_number' => 'nullable|string|max:50',
            'footer_about' => 'nullable|string|max:2000',
            'logo_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,svg|max:5120',
            'footer_logo_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,svg|max:5120',
            'favicon_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,svg,ico|max:2048',
            'home_highlight_image_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
            'intro_image_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
            'intro_video_file' => 'nullable|file|mimes:mp4,webm,ogg,mov,m4v|max:51200',
            'promo_image_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
            'promo_video_file' => 'nullable|file|mimes:mp4,webm,ogg,mov,m4v|max:51200',
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

        // Handle intro banner image upload
        if ($request->hasFile('intro_image_file')) {
            $file = $request->file('intro_image_file');
            $filename = 'intro-' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $disk = env('FILESYSTEM_DISK', 'public');
            $file->storeAs('branding', $filename, $disk);
            $validated['intro_image'] = Storage::disk($disk)->url('branding/' . $filename);
        }

        // Handle intro video upload (falls back to a pasted URL otherwise)
        if ($request->hasFile('intro_video_file')) {
            $file = $request->file('intro_video_file');
            $filename = 'intro-video-' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $disk = env('FILESYSTEM_DISK', 'public');
            $file->storeAs('branding', $filename, $disk);
            $validated['intro_video_url'] = Storage::disk($disk)->url('branding/' . $filename);
        }

        // Handle promo popup image upload
        if ($request->hasFile('promo_image_file')) {
            $file = $request->file('promo_image_file');
            $filename = 'promo-' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $disk = env('FILESYSTEM_DISK', 'public');
            $file->storeAs('branding', $filename, $disk);
            $validated['promo_image'] = Storage::disk($disk)->url('branding/' . $filename);
        }

        // Handle promo popup video upload (falls back to a pasted URL otherwise)
        if ($request->hasFile('promo_video_file')) {
            $file = $request->file('promo_video_file');
            $filename = 'promo-video-' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $disk = env('FILESYSTEM_DISK', 'public');
            $file->storeAs('branding', $filename, $disk);
            $validated['promo_video_url'] = Storage::disk($disk)->url('branding/' . $filename);
        }

        // Remove buttons: clear the stored value unless a replacement was just
        // uploaded in the same request (upload wins).
        if ($request->boolean('remove_intro_image') && !$request->hasFile('intro_image_file')) {
            $validated['intro_image'] = '';
        }
        if ($request->boolean('remove_intro_video') && !$request->hasFile('intro_video_file')) {
            $validated['intro_video_url'] = '';
        }
        if ($request->boolean('remove_promo_image') && !$request->hasFile('promo_image_file')) {
            $validated['promo_image'] = '';
        }
        if ($request->boolean('remove_promo_video') && !$request->hasFile('promo_video_file')) {
            $validated['promo_video_url'] = '';
        }

        // Checkbox: absent from the request means "disabled". Normalise to "1"/"0"
        // so the feature can be toggled off (unchecked inputs are never submitted).
        $validated['intro_enabled'] = $request->boolean('intro_enabled') ? '1' : '0';
        $validated['promo_enabled'] = $request->boolean('promo_enabled') ? '1' : '0';

        // Payment method enable/disable switches (same unchecked-checkbox handling).
        foreach (['cod_enabled', 'bank_transfer_enabled', 'easypaisa_enabled', 'jazzcash_enabled'] as $flag) {
            $validated[$flag] = $request->boolean($flag) ? '1' : '0';
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
