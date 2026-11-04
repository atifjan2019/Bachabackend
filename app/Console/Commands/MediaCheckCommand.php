<?php

namespace App\Console\Commands;

use App\Support\MediaLibrary;
use App\Support\Storefront\ImageVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Lists catalogue images whose file is missing from the media bucket, and
 * what uses them, so the owner knows exactly which records to re-upload.
 *
 *   php artisan media:check
 */
class MediaCheckCommand extends Command
{
    protected $signature = 'media:check';

    protected $description = 'List catalogue images whose file is missing from the media bucket';

    public function handle(): int
    {
        $disk = Storage::disk('s3');
        $missing = 0;
        $checked = 0;
        foreach (MediaLibrary::build() as $file) {
            $key = ImageVariant::key($file['url']);
            if ($key === null || ! str_starts_with($key, 'm/')) {
                continue;
            }
            $checked++;
            try {
                $exists = $disk->exists(substr($key, 2));
            } catch (\Throwable $e) {
                $this->error('Cannot read the bucket: '.$e->getMessage());

                return self::FAILURE;
            }
            if (! $exists) {
                $missing++;
                $this->warn('  ✗ '.$file['path'].'  ←  '.($file['used_by'] ? implode('; ', $file['used_by']) : 'media library only'));
            }
        }
        $this->info("Checked $checked bucket images: $missing missing.");

        return self::SUCCESS;
    }
}
