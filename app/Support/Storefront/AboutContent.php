<?php

namespace App\Support\Storefront;

use App\Models\Setting;
use App\Models\TeamMember;
use Illuminate\Support\Facades\Cache;

/**
 * About-page content: the same team/founder/story data the public
 * /api/v1/about endpoint exposes (see ContentController::about()), plus the
 * storefront's defaults for when the admin has not filled anything in.
 */
class AboutContent
{
    public const ICON_MAP = [
        'crown' => 'crown',
        'clipboard' => 'clipboard-list',
        'megaphone' => 'megaphone',
        'headphones' => 'headphones',
        'palette' => 'palette',
        'users' => 'users',
        'star' => 'star',
        'gem' => 'gem',
        'shield' => 'shield-check',
        'landmark' => 'landmark',
        'repeat' => 'repeat',
        'heart' => 'heart',
        'sparkles' => 'sparkles',
        'truck' => 'truck',
        'tag' => 'tag',
        'briefcase' => 'briefcase',
    ];

    public const DEFAULT_TEAM = [
        ['icon' => 'crown', 'title' => 'Founder / CEO', 'subtitle' => 'Vision & leadership', 'bio' => null, 'image' => null],
        ['icon' => 'clipboard', 'title' => 'Operations Manager', 'subtitle' => 'Fulfilment & logistics', 'bio' => null, 'image' => null],
        ['icon' => 'megaphone', 'title' => 'Marketing Manager', 'subtitle' => 'Growth & brand reach', 'bio' => null, 'image' => null],
        ['icon' => 'headphones', 'title' => 'Customer Support Lead', 'subtitle' => 'Care & after-sales', 'bio' => null, 'image' => null],
        ['icon' => 'palette', 'title' => 'Creative / Brand Director', 'subtitle' => 'Design & identity', 'bio' => null, 'image' => null],
    ];

    public const DEFAULT_STORY = [
        'heading' => 'Rooted in tradition,',
        'accent' => 'built on trust.',
        'body' => "Bacha Stylo started from Lower Dir, KPK with a simple vision — to create a trusted fashion identity rooted in tradition, honesty, and quality.\n\nWhat began with traditional wear has now grown into a broader lifestyle brand offering clothes, waistcoats, Chitrali pakols, caps, shawls, fragrances, footwear, and personal care products. Every product reflects our belief in authenticity, fair pricing, and customer satisfaction.",
        'location' => 'Lower Dir, KPK',
        'image' => null,
    ];

    public const DEFAULT_FOUNDER = [
        'name' => 'Muhammad Ali Shah Bacha',
        'role' => 'Founder & CEO',
        'bio' => "Muhammad Ali Shah Bacha is the founder of Bacha Stylo Fashion, a fashion and lifestyle brand built on years of practical experience and market understanding. With over 11 years of involvement in the fashion and lifestyle industry, he has focused on continuous research, product selection, and learning from real market conditions to develop a strong sense of customer needs and trends.\n\nHis journey began at a very small level with limited resources, but through consistency, honesty, and customer satisfaction, he gradually built trust in the market — which became the foundation of the brand's growth.",
        'image' => null,
        'initials' => 'MA',
    ];

    /** @return array{team: list<array>, founder: array, story: array} Raw admin data, as the API returns it. */
    public static function raw(): array
    {
        return Cache::remember('storefront.about', 300, function () {
            $team = TeamMember::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['title', 'subtitle', 'bio', 'icon', 'image_url'])
                ->map(fn ($m) => [
                    'title' => $m->title,
                    'subtitle' => $m->subtitle,
                    'bio' => $m->bio ?: null,
                    'icon' => $m->icon,
                    'image' => $m->image_url ?: null,
                ])->all();

            $s = Setting::whereIn('setting_key', [
                'about_founder_name', 'about_founder_role', 'about_founder_bio',
                'about_founder_image', 'about_founder_initials',
                'about_story_heading', 'about_story_accent', 'about_story_body',
                'about_story_location', 'about_story_image',
            ])->pluck('setting_value', 'setting_key');

            return [
                'team' => $team,
                'founder' => [
                    'name' => $s['about_founder_name'] ?? '',
                    'role' => $s['about_founder_role'] ?? '',
                    'bio' => $s['about_founder_bio'] ?? '',
                    'image' => ($s['about_founder_image'] ?? '') ?: null,
                    'initials' => $s['about_founder_initials'] ?? '',
                ],
                'story' => [
                    'heading' => $s['about_story_heading'] ?? '',
                    'accent' => $s['about_story_accent'] ?? '',
                    'body' => $s['about_story_body'] ?? '',
                    'location' => $s['about_story_location'] ?? '',
                    'image' => ($s['about_story_image'] ?? '') ?: null,
                ],
            ];
        });
    }

    /** Page-ready data with defaults applied and paragraphs split. */
    public static function resolved(): array
    {
        $raw = self::raw();

        $team = $raw['team'] !== [] ? $raw['team'] : self::DEFAULT_TEAM;
        $founder = trim((string) ($raw['founder']['name'] ?? '')) !== '' ? $raw['founder'] : self::DEFAULT_FOUNDER;
        $story = (trim((string) ($raw['story']['heading'] ?? '')) !== '' || trim((string) ($raw['story']['body'] ?? '')) !== '')
            ? $raw['story']
            : self::DEFAULT_STORY;

        $initials = trim((string) ($founder['initials'] ?? ''));
        if ($initials === '') {
            $words = preg_split('/\s+/', trim((string) $founder['name'])) ?: [];
            $initials = strtoupper(implode('', array_map(fn ($w) => mb_substr($w, 0, 1), array_slice($words, 0, 2))));
        }

        return [
            'team' => array_map(fn ($role) => $role + ['lucide' => self::ICON_MAP[$role['icon'] ?? ''] ?? 'users'], $team),
            'founder' => $founder + ['initials_resolved' => $initials, 'paragraphs' => self::paragraphs($founder['bio'] ?? '')],
            'story' => $story + ['paragraphs' => self::paragraphs($story['body'] ?? '')],
        ];
    }

    /** @return list<string> */
    public static function paragraphs(?string $text): array
    {
        $parts = preg_split('/\n\s*\n/', (string) $text) ?: [];

        return array_values(array_filter(array_map('trim', $parts), fn ($p) => $p !== ''));
    }
}
