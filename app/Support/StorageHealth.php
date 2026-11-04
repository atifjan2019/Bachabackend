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

    public const KEY = 'admin.storage.health';

    /** @return array{ok: bool, error: ?string, checked_at: string} */
    public static function status(): array
    {
        return Cache::remember(self::KEY, self::TTL, fn () => self::check());
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
        Cache::forget(self::KEY);
    }
}
