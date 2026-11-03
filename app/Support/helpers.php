<?php

use App\Support\Storefront\SiteSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (! function_exists('pkr')) {
    /**
     * Format rupees the way the storefront always has: "Rs. 12,500".
     * Rounded to whole rupees with en-PK thousands grouping.
     */
    function pkr(float|int|string|null $amount): string
    {
        $value = is_numeric($amount) ? (float) $amount : 0.0;

        return 'Rs. '.number_format(round($value), 0, '.', ',');
    }
}

if (! function_exists('strip_html_excerpt')) {
    /**
     * Plain text from an HTML fragment, collapsed and trimmed, cut at a word
     * boundary with an ellipsis. Used for meta descriptions and card excerpts.
     */
    function strip_html_excerpt(?string $html, int $max = 160): string
    {
        $text = preg_replace('/<[^>]*>?/m', ' ', (string) $html) ?? '';
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\u{a0}", ' ', $text);
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');

        if (mb_strlen($text) <= $max) {
            return $text;
        }

        $cut = mb_substr($text, 0, $max);
        $space = mb_strrpos($cut, ' ');
        $cut = $space !== false && $space > 80 ? mb_substr($cut, 0, $space) : $cut;

        return rtrim($cut).'…';
    }
}

if (! function_exists('site')) {
    /** The storefront settings helper (cached admin settings + shipping rule). */
    function site(): SiteSettings
    {
        return app(SiteSettings::class);
    }
}

if (! function_exists('media_store')) {
    /**
     * Store an admin upload on the media disk and return its public URL.
     *
     * Files get a UUID name, so they never change once written: the one-year
     * cache header lets browsers and the bucket's CDN keep them that long
     * instead of re-fetching every few hours.
     */
    function media_store(UploadedFile $file, string $dir, ?string $filename = null): string
    {
        $disk = config('filesystems.default');
        $filename = $filename ?: Str::uuid().'.'.$file->getClientOriginalExtension();
        $file->storeAs($dir, $filename, ['disk' => $disk, 'CacheControl' => 'public, max-age=31536000, immutable']);

        return Storage::disk($disk)->url(trim($dir, '/').'/'.$filename);
    }
}
