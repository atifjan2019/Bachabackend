<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function subscribeNewsletter(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $subscriber = NewsletterSubscriber::updateOrCreate(
            ['email' => strtolower(trim($validated['email']))],
        );

        return response()->json([
            'message' => 'Subscribed successfully.',
            'data' => ['email' => $subscriber->email],
        ], 201);
    }

    public function settings(): JsonResponse
    {
        $whitelist = [
            'business_name',
            'business_email',
            'business_phone',
            'business_address',
            'logo_url',
            'footer_logo_url',
            'favicon_url',
            'facebook_url',
            'instagram_url',
            'tiktok_url',
            'whatsapp_number',
            'shipping_fee',
            'free_shipping_threshold',
            'meta_title',
            'meta_description',
            'meta_keywords',
            'og_image',
            'canonical_base_url',
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

        $settings = Setting::query()
            ->whereIn('setting_key', $whitelist)
            ->pluck('setting_value', 'setting_key')
            ->map(function ($value, $key) {
                return $value;
            });

        return response()->json(['data' => $settings]);
    }

    public function blogPosts(Request $request): JsonResponse
    {
        $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $perPage = (int) ($request->integer('per_page') ?: 20);

        $posts = BlogPost::query()
            ->where('status', true)
            ->orderByDesc('id')
            ->paginate($perPage)
            ->appends($request->query());

        return response()->json($posts);
    }

    public function blogPost(string $slug): JsonResponse
    {
        $post = BlogPost::query()
            ->where('slug', $slug)
            ->where('status', true)
            ->firstOrFail();

        return response()->json(['data' => $post]);
    }
}
