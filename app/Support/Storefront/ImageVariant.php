<?php

namespace App\Support\Storefront;

/**
 * Resized copies of catalogue images, made on first request and then served
 * as plain files from public/img (see Shop\ImageController).
 *
 * Admins upload originals of 1000–2000px to the media bucket, and the bucket's
 * CDN caches them for only four hours. A card on a phone needs ~300px, so the
 * storefront asks for /img/{width}/{key}.webp instead: a fraction of the
 * bytes, in a modern format, with a one-year cache header. Only two sources
 * are allowed — the media bucket's public URL and the packaged public/images
 * folder — so the resizer can never be pointed at arbitrary hosts.
 */
class ImageVariant
{
    /** Widths a variant may be requested at (anything else is a 404). */
    public const WIDTHS = [96, 160, 240, 320, 360, 480, 640, 768, 960, 1280, 1600];

    public const FORMATS = ['webp', 'png', 'jpg'];

    /** Default srcset candidates for a fluid image. */
    public const FLUID = [320, 360, 480, 640, 960, 1280];

    public const CACHE_CONTROL = 'public, max-age=31536000, immutable';

    /**
     * Appended to every variant URL (?v=N). Copies are cached by browsers for
     * a year as immutable, so when the way they are made changes (e.g. the
     * AVIF transparency fix of 3 Nov 2026) this is bumped and every browser
     * fetches fresh files. Keep ImageVariant.VERSION in helpers.js and the
     * admin layout in step.
     */
    public const VERSION = 2;

    /** Public origin of the media bucket, e.g. https://media.bachastylo.com (no trailing slash). */
    public static function mediaOrigin(): string
    {
        return rtrim((string) config('filesystems.disks.s3.url', ''), '/');
    }

    /**
     * The variant key for a source URL: "m/<bucket path>" for media-bucket
     * images, "l/images/<file>" for packaged images, null for anything else
     * (foreign hosts, data URIs, query strings, unsupported types).
     */
    public static function key(?string $src): ?string
    {
        $src = trim((string) $src);
        if ($src === '' || str_contains($src, '?') || str_contains($src, '#') || str_contains($src, '..')) {
            return null;
        }

        $origin = self::mediaOrigin();
        $path = null;
        $prefix = null;
        if ($origin !== '' && str_starts_with($src, $origin.'/')) {
            $prefix = 'm';
            $path = substr($src, strlen($origin) + 1);
        } else {
            // Packaged images: asset('images/…') on the app URL or the current host.
            $bases = array_unique(array_filter([
                rtrim((string) config('app.url'), '/'),
                app()->runningInConsole() ? null : rtrim(request()->root(), '/'),
            ]));
            if (str_starts_with($src, '/images/')) {
                $prefix = 'l';
                $path = ltrim($src, '/');
            }
            foreach ($bases as $base) {
                if (str_starts_with($src, $base.'/images/')) {
                    $prefix = 'l';
                    $path = substr($src, strlen($base) + 1);
                    break;
                }
            }
        }

        $path = $path === null ? null : rawurldecode($path);
        if ($path === null || ! preg_match('#^[A-Za-z0-9_\-./ ]+\.(jpe?g|png|webp|avif|gif|jfif)$#i', $path)) {
            return null;
        }

        return $prefix.'/'.$path;
    }

    /** Public URL of one variant, or null when the source can't be resized. */
    /** Width token for a copy at the original dimensions (re-encoded, never resized). */
    public const FULL = 'full';

    public static function url(?string $src, int|string $width, string $format = 'webp'): ?string
    {
        $key = self::key($src);
        $validWidth = $width === self::FULL || in_array($width, self::WIDTHS, true);
        if ($key === null || ! $validWidth || ! in_array($format, self::FORMATS, true)) {
            return null;
        }

        // Packaged logos have spaces in their names; encode per segment.
        $encoded = implode('/', array_map('rawurlencode', explode('/', $key)));

        return '/img/'.$width.'/'.$encoded.'.'.$format.'?v='.self::VERSION;
    }

    /**
     * A srcset attribute value for the given widths, or null when the source
     * can't be resized (the caller then renders the plain src).
     *
     * @param  list<int>  $widths
     */
    public static function srcset(?string $src, array $widths = self::FLUID, string $format = 'webp'): ?string
    {
        $parts = [];
        foreach ($widths as $w) {
            $url = self::url($src, (int) $w, $format);
            if ($url !== null) {
                $parts[] = $url.' '.$w.'w';
            }
        }

        return $parts ? implode(', ', $parts) : null;
    }

    /** Where the original bytes of a variant key live: a URL or a local file path. */
    public static function source(string $key): ?array
    {
        if (str_starts_with($key, 'm/')) {
            $origin = self::mediaOrigin();

            return $origin !== '' ? ['url' => $origin.'/'.substr($key, 2)] : null;
        }
        if (str_starts_with($key, 'l/images/')) {
            $path = public_path(substr($key, 2));

            return is_file($path) ? ['path' => $path] : null;
        }

        return null;
    }
}
