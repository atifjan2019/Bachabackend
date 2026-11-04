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
        // $usedBy: a label such as "Category: Shawls" for images the catalogue
        // references; the library shows it and refuses to delete those files.
        $add = function (?string $url, ?string $path = null, ?string $usedBy = null) use (&$byUrl, $origin) {
            $url = trim((string) $url);
            if ($url === '' || str_starts_with($url, 'data:')) {
                return;
            }
            if (isset($byUrl[$url])) {
                if ($usedBy !== null && ! in_array($usedBy, $byUrl[$url]['used_by'], true)) {
                    $byUrl[$url]['used_by'][] = $usedBy;
                }

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
                'used_by' => $usedBy !== null ? [$usedBy] : [],
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
        $columns = array_values(array_filter(['id', 'name', 'sku', 'image', 'gallery', Product::hasColumn('colors') ? 'colors' : null]));
        Product::query()->select($columns)->orderByDesc('id')->chunk(500, function ($products) use ($add) {
            foreach ($products as $p) {
                $label = 'Product '.($p->sku ?: '#'.$p->id).' – '.\Illuminate\Support\Str::limit($p->name, 40);
                $add($p->image, null, $label);
                foreach ((array) ($p->gallery ?? []) as $g) {
                    $add(is_string($g) ? $g : null, null, $label.' (gallery)');
                }
                foreach ((array) ($p->colors ?? []) as $c) {
                    $add(is_array($c) ? ($c['image'] ?? null) : null, null, $label.' (colour '.($c['name'] ?? '').')');
                }
            }
        });
        foreach (Category::query()->orderByDesc('id')->get(['name', 'image']) as $cat) {
            $add($cat->image, null, 'Category: '.$cat->name);
        }
        foreach (BlogPost::query()->orderByDesc('id')->get(['title', 'image', 'og_image']) as $post) {
            $add($post->image, null, 'Blog: '.\Illuminate\Support\Str::limit($post->title, 40));
            $add($post->og_image, null, 'Blog: '.\Illuminate\Support\Str::limit($post->title, 40).' (share image)');
        }
        $keys = ['logo_url' => 'Logo', 'footer_logo_url' => 'Footer logo', 'favicon_url' => 'Favicon', 'og_image' => 'Share image',
            'home_highlight_image' => 'Home highlight', 'intro_image' => 'Intro banner', 'promo_image' => 'Promo popup'];
        foreach (Setting::query()->whereIn('setting_key', array_keys($keys))->get(['setting_key', 'setting_value']) as $row) {
            $add($row->setting_value, null, 'Setting: '.$keys[$row->setting_key]);
        }

        return array_values($byUrl);
    }

    /** Labels of everything that uses this URL (empty when nothing does). */
    public static function usages(string $url): array
    {
        foreach (self::files() as $file) {
            if ($file['url'] === $url) {
                return $file['used_by'];
            }
        }

        return [];
    }

    /** Drop the server's cached original and resized copies of a bucket object. */
    public static function forgetLocalCopies(string $path): void
    {
        @unlink(storage_path('app/media-originals/m/'.$path));
        foreach (glob(public_path('img/*/m/'.$path.'.*')) ?: [] as $file) {
            @unlink($file);
        }
    }

    public static function forget(): void
    {
        Cache::forget('admin.media.library');
    }
}
