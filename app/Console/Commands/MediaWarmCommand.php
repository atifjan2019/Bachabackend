<?php

namespace App\Console\Commands;

use App\Http\Controllers\Shop\ImageController;
use App\Support\MediaLibrary;
use App\Support\Storefront\ImageVariant;
use Illuminate\Console\Command;

/**
 * Pre-generate the resized copies of every catalogue image, so the site
 * serves them as static files without ever needing the media CDN at
 * request time. Safe to re-run: existing files are skipped.
 *
 *   php artisan media:warm                 # standard widths, only missing files
 *   php artisan media:warm --widths=320,480 --limit=50
 */
class MediaWarmCommand extends Command
{
    protected $signature = 'media:warm
                            {--widths= : Comma-separated widths (default: the card and page sizes)}
                            {--limit=0 : Stop after this many images (0 = all)}';

    protected $description = 'Pre-generate resized copies of every catalogue image under public/img';

    public const WIDTHS = [160, 240, 320, 360, 480, 640, 768, 960, 1280];

    public function handle(): int
    {
        $widths = array_values(array_filter(array_map('intval', explode(',', (string) ($this->option('widths') ?: implode(',', self::WIDTHS)))),
            fn ($w) => in_array($w, ImageVariant::WIDTHS, true)));
        $limit = (int) $this->option('limit');

        $keys = [];
        foreach (MediaLibrary::build() as $file) {
            $key = ImageVariant::key($file['url']);
            if ($key !== null && str_starts_with($key, 'm/')) {
                $keys[$key] = true;
            }
        }
        $keys = array_keys($keys);
        if ($limit > 0) {
            $keys = array_slice($keys, 0, $limit);
        }
        $this->info(count($keys).' images × '.count($widths).' widths');

        $made = $skipped = $failed = 0;
        $failures = [];
        $started = microtime(true);
        foreach ($keys as $i => $key) {
            foreach ($widths as $w) {
                if (is_file(ImageController::targetPath($w, $key, 'webp'))) {
                    $skipped++;

                    continue;
                }
                try {
                    ImageController::generate($key, $w, 'webp');
                    $made++;
                } catch (\Throwable $e) {
                    $failed++;
                    $failures[$key] = substr($e->getMessage(), 0, 80);
                    break; // the source itself is the problem; other widths will fail too
                }
            }
            if (($i + 1) % 50 === 0) {
                $this->line(sprintf('  %d/%d  made=%d skipped=%d failed=%d  %ds', $i + 1, count($keys), $made, $skipped, $failed, (int) (microtime(true) - $started)));
            }
        }

        foreach ($failures as $key => $why) {
            $this->warn("  ✗ $key: $why");
        }
        $this->info("Done: made $made, skipped $skipped (already there), failed $failed sources, ".(int) (microtime(true) - $started).'s');

        return self::SUCCESS;
    }
}
