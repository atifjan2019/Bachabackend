<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\ContactMessageMail;
use App\Models\BlogPost;
use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use App\Models\TeamMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

    public function submitContact(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $contact = ContactMessage::create($validated);

        // Notify the admin. Failures must never block the submission.
        try {
            $adminEmail = config('mail.admin_address')
                ?: Setting::where('setting_key', 'order_notification_email')->value('setting_value')
                ?: Setting::where('setting_key', 'business_email')->value('setting_value')
                ?: config('mail.from.address');

            if ($adminEmail) {
                Mail::to($adminEmail)->send(new ContactMessageMail($contact));
            }
        } catch (\Throwable $e) {
            Log::warning('Contact email failed for #' . $contact->id . ': ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Message sent successfully.',
            'data' => ['id' => $contact->id],
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

        $settings = Setting::query()
            ->whereIn('setting_key', $whitelist)
            ->pluck('setting_value', 'setting_key')
            ->map(function ($value, $key) {
                return $value;
            });

        return response()->json(['data' => $settings]);
    }

    /**
     * Dynamic content for the storefront About page: the team structure cards
     * and the founder section.
     */
    public function about(): JsonResponse
    {
        $team = TeamMember::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['title', 'subtitle', 'bio', 'icon', 'image_url'])
            ->map(fn ($m) => [
                'title'    => $m->title,
                'subtitle' => $m->subtitle,
                'bio'      => $m->bio ?: null,
                'icon'     => $m->icon,
                'image'    => $m->image_url ?: null,
            ]);

        $s = Setting::whereIn('setting_key', [
            'about_founder_name', 'about_founder_role', 'about_founder_bio',
            'about_founder_image', 'about_founder_initials',
            'about_story_heading', 'about_story_accent', 'about_story_body',
            'about_story_location', 'about_story_image',
        ])->pluck('setting_value', 'setting_key');

        $founder = [
            'name'     => $s['about_founder_name'] ?? '',
            'role'     => $s['about_founder_role'] ?? '',
            'bio'      => $s['about_founder_bio'] ?? '',
            'image'    => ($s['about_founder_image'] ?? '') ?: null,
            'initials' => $s['about_founder_initials'] ?? '',
        ];

        $story = [
            'heading'  => $s['about_story_heading'] ?? '',
            'accent'   => $s['about_story_accent'] ?? '',
            'body'     => $s['about_story_body'] ?? '',
            'location' => $s['about_story_location'] ?? '',
            'image'    => ($s['about_story_image'] ?? '') ?: null,
        ];

        return response()->json(['data' => [
            'team'    => $team,
            'founder' => $founder,
            'story'   => $story,
        ]]);
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
