<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Give every object in the media bucket a one-year Cache-Control header.
 * Files are named with UUIDs and never change, so browsers and the bucket's
 * CDN can keep them that long (the CDN default was four hours). Objects that
 * already carry the header are skipped, so re-running is cheap.
 *
 *   php artisan media:cache-headers            # all objects
 *   php artisan media:cache-headers --prefix=products --limit=100
 */
class MediaCacheHeadersCommand extends Command
{
    protected $signature = 'media:cache-headers {--prefix= : Only keys under this folder} {--limit=0 : Stop after this many updates (0 = all)}';

    protected $description = 'Set a one-year Cache-Control header on every object in the media bucket';

    public const CACHE_CONTROL = 'public, max-age=31536000, immutable';

    public function handle(): int
    {
        $disk = Storage::disk('s3');
        $client = $disk->getClient();
        $bucket = (string) config('filesystems.disks.s3.bucket');
        $limit = (int) $this->option('limit');

        $seen = $updated = $skipped = $failed = 0;
        $token = null;
        do {
            $page = $client->listObjectsV2(array_filter(['Bucket' => $bucket, 'Prefix' => (string) $this->option('prefix'), 'ContinuationToken' => $token, 'MaxKeys' => 1000]));
            foreach ($page['Contents'] ?? [] as $object) {
                $key = $object['Key'];
                $seen++;
                try {
                    $head = $client->headObject(['Bucket' => $bucket, 'Key' => $key]);
                    if (($head['CacheControl'] ?? '') === self::CACHE_CONTROL) {
                        $skipped++;

                        continue;
                    }
                    $client->copyObject([
                        'Bucket' => $bucket,
                        'Key' => $key,
                        'CopySource' => rawurlencode($bucket).'/'.str_replace('%2F', '/', rawurlencode($key)),
                        'MetadataDirective' => 'REPLACE',
                        'ContentType' => $head['ContentType'] ?? 'application/octet-stream',
                        'CacheControl' => self::CACHE_CONTROL,
                    ]);
                    $updated++;
                } catch (\Throwable $e) {
                    $failed++;
                    $this->warn("  ✗ $key: ".substr($e->getMessage(), 0, 100));
                }
                if ($limit > 0 && $updated >= $limit) {
                    break 2;
                }
            }
            $token = $page['NextContinuationToken'] ?? null;
        } while ($token);

        $this->info("Objects seen $seen: updated $updated, already set $skipped, failed $failed.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
