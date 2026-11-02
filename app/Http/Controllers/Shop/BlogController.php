<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Support\Storefront\SiteSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = Cache::remember('storefront.blog.index', 300, function () {
            return BlogPost::query()
                ->where('status', true)
                ->orderByDesc('id')
                ->limit(50)
                ->get(['id', 'slug', 'title', 'excerpt', 'content', 'category', 'image', 'created_at'])
                ->map(fn (BlogPost $p) => self::card($p))
                ->values()
                ->all();
        });

        return view('shop.blog.index', [
            'featured' => $posts[0] ?? null,
            'rest' => array_slice($posts, 1),
        ]);
    }

    public function show(string $slug, SiteSettings $settings): View
    {
        $post = BlogPost::query()->where('slug', $slug)->where('status', true)->firstOrFail();

        $text = $post->content ? strip_html_excerpt($post->content, 100000) : '';
        $title = trim((string) $post->meta_title) ?: $post->title;
        $description = trim((string) $post->meta_description)
            ?: trim((string) $post->excerpt)
            ?: ($post->content ? strip_html_excerpt($post->content, 160) : 'Read the latest from the Bacha Stylo Journal.');
        $ogImage = $post->og_image ?: $post->image;
        $canonical = trim((string) $post->canonical_url) ?: $settings->canonical('/blogs/'.$post->slug);

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->title,
            'description' => $description,
            'image' => $ogImage ? [$ogImage] : null,
            'datePublished' => optional($post->created_at)->toIso8601String(),
            'dateModified' => optional($post->updated_at)->toIso8601String(),
            'mainEntityOfPage' => $canonical,
            'author' => ['@type' => 'Organization', 'name' => $settings->businessName()],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $settings->businessName(),
                'logo' => ['@type' => 'ImageObject', 'url' => $settings->logoUrl() ?: asset('images/BachaStylo Logo for web.png')],
            ],
            'articleSection' => trim((string) $post->category) ?: 'Journal',
            'wordCount' => str_word_count($text),
        ];

        return view('shop.blog.show', [
            'post' => $post,
            'category' => trim((string) $post->category) ?: 'Journal',
            'date' => optional($post->created_at)->format('F j, Y'),
            'metaTitle' => $title,
            'metaDescription' => $description,
            'ogImage' => $ogImage,
            'canonical' => $canonical,
            'jsonLd' => array_filter($jsonLd, fn ($v) => $v !== null),
        ]);
    }

    /** Card data for the journal index (blogs/page.tsx → toPost). */
    private static function card(BlogPost $p): array
    {
        $text = $p->content ? trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($p->content), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '') : '';
        $excerpt = trim((string) $p->excerpt)
            ?: (mb_strlen($text) > 140 ? mb_substr($text, 0, 140).'…' : $text)
            ?: 'Read the full story.';
        $words = count(array_filter(preg_split('/\s+/u', trim($text !== '' ? $text : $p->title)) ?: []));

        return [
            'slug' => $p->slug,
            'title' => $p->title,
            'excerpt' => $excerpt,
            'image' => $p->image ?: null,
            'date' => optional($p->created_at)->format('M j, Y'),
            'category' => trim((string) $p->category) ?: 'Journal',
            'readTime' => max(1, (int) ceil($words / 200)).' min read',
        ];
    }
}
