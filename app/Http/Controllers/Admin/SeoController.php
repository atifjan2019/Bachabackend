<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    private const ALLOWED_KEYS = [
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'google_analytics_id',
        'facebook_pixel_id',
        'google_site_verification',
        'robots_meta',
        'canonical_base_url',
    ];

    public function index()
    {
        $settings = Setting::pluck('setting_value', 'setting_key')->toArray();
        return view('admin.seo.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:2000',
            'meta_keywords' => 'nullable|string|max:1000',
            'og_image' => 'nullable|string|max:2000',
            'google_analytics_id' => 'nullable|string|max:100',
            'facebook_pixel_id' => 'nullable|string|max:100',
            'google_site_verification' => 'nullable|string|max:500',
            'robots_meta' => 'nullable|string|max:100',
            'canonical_base_url' => 'nullable|string|max:2000',
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

        return redirect()->route('admin.seo.index')->with('success', 'SEO settings saved.');
    }
}
