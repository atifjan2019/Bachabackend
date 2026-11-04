<?php

namespace App\Support;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Everything the admin's Media Library and "Select from Library" picker can
 * offer: the bucket listing when the storage key is allowed to list, plus
 * every image the catalogue already uses (products, categories, blog posts,
 * branding settings, uploads recorded in the media table). The bucket's API
 * key currently has no list permission, so without the second source the
 * library came up empty.
 *
 * @return list<array{path:string,url:string,name:string,folder:string}>
 */
class MediaLibrary
{
    public const TTL = 120;

    public const IMAGE_EXT = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg', 'jfif'];

    public static function files(): array
    {
        return Cache::remember('admin.media.library', self::TTL, fn () => self::build());
    }

    public static function build(): array
    {
        $disk = Storage::disk(config('filesystems.default'));
        $origin = rtrim((string) config('filesystems.disks.s3.url', ''), '/');

        $byUrl = [];
        $add = function (?string $url, ?string $path = null) use (&$byUrl, $origin) {
            $url = trim((string) $url);
            if ($url === '' || isset($byUrl[$url]) || str_starts_with($url, 'data:')) {
                return;
            }
            if ($path === null) {
                $path = $origin !== '' && str_starts_with($url, $origin.'/')
                    ? substr($url, strlen($origin) + 1)
                    : ltrim((string) parse_url($url, PHP_URL_PATH), '/');
            }
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (! in_array($ext, self::IMAGE_EXT, true)) {
                return;
            }
            $folder = dirname($path);
            $byUrl[$url] = [
                'path' => $path,
                'url' => $url,
                // Grid thumbnail: a 240px copy served by this site, so the
                // admin never pulls 1,000 full-size files from the bucket.
                'thumb' => \App\Support\Storefront\ImageVariant::url($url, 240) ?? $url,
                'name' => basename($path),
                'folder' => $folder === '.' ? '' : $folder,
            ];
        };

        // 1. Uploads recorded by the Media Library page, newest first.
        foreach (Media::query()->orderByDesc('id')->pluck('file_path') as $url) {
            $add($url);
        }

        // 2. The bucket itself, when the key may list it.
        try {
            foreach ($disk->allFiles() as $path) {
                $add($disk->url($path), $path);
            }
        } catch (\Throwable) {
            // No list permission: fall through to what the catalogue references.
        }

        // 3. Images the catalogue and site settings use.
        $columns = array_values(array_filter(['image', 'gallery', Product::hasColumn('colors') ? 'colors' : null]));
        Product::query()->select($columns)->orderByDesc('id')->chunk(500, function ($products) use ($add) {
            foreach ($products as $p) {
                $add($p->image);
                foreach ((array) ($p->gallery ?? []) as $g) {
                    $add(is_string($g) ? $g : null);
                }
                foreach ((array) ($p->colors ?? []) as $c) {
                    $add(is_array($c) ? ($c['image'] ?? null) : null);
                }
            }
        });
        foreach (Category::query()->orderByDesc('id')->pluck('image') as $url) {
            $add($url);
        }
        foreach (BlogPost::query()->orderByDesc('id')->get(['image', 'og_image']) as $post) {
            $add($post->image);
            $add($post->og_image);
        }
        $keys = ['logo_url', 'footer_logo_url', 'favicon_url', 'og_image', 'home_highlight_image', 'intro_image', 'promo_image'];
        foreach (Setting::query()->whereIn('setting_key', $keys)->pluck('setting_value') as $url) {
            $add($url);
        }

        return array_values($byUrl);
    }

    public static function forget(): void
    {
        Cache::forget('admin.media.library');
    }
}
