<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Can the app write to the media bucket right now? Checked at most every
 * ten minutes, by writing a one-line health file. The admin layout shows a
 * red banner when it fails, because a rejected storage key otherwise fails
 * silently: uploads "succeed" in the form but the file never lands.
 */
class StorageHealth
{
    public const TTL = 600;

    public const FAILURE_TTL = 60;

    public const KEY = 'admin.storage.health';

    /** @return array{ok: bool, error: ?string, checked_at: string} */
    public static function status(): array
    {
        // Kept in its own file rather than the application cache, which is
        // flushed after every admin write; otherwise each write would cost a
        // bucket round-trip on the next page.
        // A good result is kept for TTL; a failure only for FAILURE_TTL, so
        // a blip (a deploy rebuilding the config cache, a network hiccup)
        // does not alarm the admin for ten minutes.
        $file = storage_path('framework/storage-health.json');
        if (is_file($file)) {
            $cached = json_decode((string) file_get_contents($file), true);
            $age = time() - filemtime($file);
            if (is_array($cached) && array_key_exists('ok', $cached) && $age < ($cached['ok'] ? self::TTL : self::FAILURE_TTL)) {
                return $cached;
            }
        }
        $status = self::check();
        if (! $status['ok']) {
            // One retry before reporting: the first attempt may have hit a
            // transient error rather than a bad key.
            sleep(1);
            $status = self::check();
        }
        @file_put_contents($file, json_encode($status));

        return $status;
    }

    public static function check(): array
    {
        $disk = config('filesystems.default');
        if (! in_array($disk, ['s3'], true)) {
            return ['ok' => true, 'error' => null, 'checked_at' => now()->toDateTimeString()];
        }
        try {
            $ok = Storage::disk($disk)->put('media/_healthcheck.txt', 'ok '.now()->toIso8601String(), ['CacheControl' => 'no-store']);
            $error = $ok ? null : 'the bucket rejected the write (check AWS_ACCESS_KEY_ID / AWS_SECRET_ACCESS_KEY)';
        } catch (\Throwable $e) {
            $ok = false;
            $error = substr($e->getMessage(), 0, 160);
        }

        return ['ok' => (bool) $ok, 'error' => $error, 'checked_at' => now()->toDateTimeString()];
    }

    public static function forget(): void
    {
        @unlink(storage_path('framework/storage-health.json'));
    }
}
