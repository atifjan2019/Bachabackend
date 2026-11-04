<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Support\Storefront\ImageVariant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * GET /img/{width}/{key}.{format}: resize a catalogue image once, write the
 * result under public/img so the web server serves every later request as a
 * static file, and return it with a one-year cache header. If anything goes
 * wrong the browser is sent to the original image, so a card never breaks.
 */
class ImageController extends Controller
{
    public const MAX_SOURCE_BYTES = 25 * 1024 * 1024;

    public const QUALITY = 74;

    /** Quality for full-size copies, which stand in for the original. */
    public const FULL_QUALITY = 88;

    public function show(string $width, string $key): Response
    {
        // "full" keeps the original dimensions (width 0 = no resize).
        $width = $width === ImageVariant::FULL ? 0 : (int) $width;
        if (($width !== 0 && ! in_array($width, ImageVariant::WIDTHS, true))
            || str_contains($key, '..')
            || ! preg_match('#^(m|l)/([A-Za-z0-9_\-./ ]+\.(?:jpe?g|png|webp|avif|gif|jfif))\.(webp|png|jpg)$#i', $key, $m)) {
            return self::notFound();
        }
        $sourceKey = $m[1].'/'.$m[2];
        $format = strtolower($m[3]);
        $target = self::targetPath($width, $sourceKey, $format);

        if (is_file($target)) {
            return $this->file($target, $format);
        }

        $source = ImageVariant::source($sourceKey);
        if ($source === null) {
            return self::notFound();
        }

        try {
            $out = self::generate($sourceKey, $width, $format);
        } catch (\Throwable $e) {
            Log::warning('[img] variant failed: '.$e->getMessage(), ['key' => $key, 'width' => $width]);
            if (isset($source['url'])) {
                return redirect()->to($source['url'], 302, ['Cache-Control' => 'public, max-age=300']);
            }

            return self::notFound();
        }

        return response($out, 200, [
            'Content-Type' => self::mime($format),
            'Cache-Control' => ImageVariant::CACHE_CONTROL,
        ]);
    }

    /** Where a finished variant lives (served statically by the web server afterwards). */
    public static function targetPath(int $width, string $sourceKey, string $format): string
    {
        return public_path('img/'.($width === 0 ? ImageVariant::FULL : $width).'/'.$sourceKey.'.'.$format);
    }

    /**
     * Make one variant and write it to public/img. The original is fetched
     * once and kept under storage/app/media-originals, so every further
     * width (and any later regeneration) works without the media CDN.
     *
     * @throws \RuntimeException when the source can't be fetched or decoded
     */
    public static function generate(string $sourceKey, int $width, string $format): string
    {
        $source = ImageVariant::source($sourceKey);
        if ($source === null) {
            throw new \RuntimeException('unknown source');
        }
        $bytes = isset($source['path']) ? (string) file_get_contents($source['path']) : self::original($sourceKey, $source['url']);
        $out = self::render($bytes, $width, $format);
        self::write(self::targetPath($width, $sourceKey, $format), $out);

        return $out;
    }

    /** The original bytes of a media-bucket image: from the local copy, else fetched and kept. */
    private static function original(string $sourceKey, string $url): string
    {
        $cache = storage_path('app/media-originals/'.$sourceKey);
        if (is_file($cache) && filesize($cache) > 0) {
            return (string) file_get_contents($cache);
        }
        $bytes = self::fromBucket($sourceKey) ?? self::fetch($url);
        // Keep the original only once it is known to decode, so a CDN error
        // page or a corrupt download never poisons the cache.
        if (@getimagesizefromstring($bytes) === false && ! self::decodes($bytes)) {
            throw new \RuntimeException('source is not a decodable image');
        }
        self::write($cache, $bytes);

        return $bytes;
    }

    /**
     * Read the object straight from the bucket with the storage key (no
     * Cloudflare in between). Null when the key lacks read permission or the
     * object is missing, in which case the public URL is tried.
     */
    private static function fromBucket(string $sourceKey): ?string
    {
        if (! str_starts_with($sourceKey, 'm/') || config('filesystems.default') !== 's3') {
            return null;
        }
        try {
            $bytes = \Illuminate\Support\Facades\Storage::disk('s3')->get(substr($sourceKey, 2));

            return is_string($bytes) && $bytes !== '' ? $bytes : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function decodes(string $bytes): bool
    {
        try {
            if (class_exists(\Imagick::class)) {
                (new \Imagick)->readImageBlob($bytes);

                return true;
            }
        } catch (\Throwable) {
        }

        return @imagecreatefromstring($bytes) !== false;
    }

    /** A plain 404 (the storefront's HTML error page would be wasted on an <img>). */
    private static function notFound(): Response
    {
        return response('Not found', 404, ['Content-Type' => 'text/plain', 'Cache-Control' => 'public, max-age=300']);
    }

    private function file(string $path, string $format): Response
    {
        return response()->file($path, [
            'Content-Type' => self::mime($format),
            'Cache-Control' => ImageVariant::CACHE_CONTROL,
        ]);
    }

    public static function mime(string $format): string
    {
        return ['webp' => 'image/webp', 'png' => 'image/png', 'jpg' => 'image/jpeg'][$format];
    }

    /** Download the original from the media bucket (never from anywhere else). */
    private static function fetch(string $url): string
    {
        // No image/avif in Accept: the CDN would otherwise hand back AVIF,
        // which not every image library on the server can decode.
        $res = Http::timeout(20)->withHeaders([
            'Accept' => 'image/webp,image/png,image/jpeg,image/*;q=0.8,*/*;q=0.5',
            'User-Agent' => 'BachaStylo-Images/1.0 (+https://www.bachastylo.com)',
        ])->get($url);
        $type = (string) $res->header('Content-Type');
        if (! $res->successful() || str_starts_with($type, 'text/')) {
            throw new \RuntimeException('source responded '.$res->status().' '.$type);
        }
        $body = $res->body();
        if (strlen($body) > self::MAX_SOURCE_BYTES) {
            throw new \RuntimeException('source too large');
        }

        return $body;
    }

    /** Resize to at most $width (never upscaled) and encode in $format. */
    public static function render(string $bytes, int $width, string $format): string
    {
        // Some uploads are AVIF behind a .png/.jpg name; whichever library
        // on this machine can decode the bytes wins.
        // ImageMagick's HEIF decoder has dropped AVIF alpha channels (a logo
        // came out on black), so AVIF goes to GD first; everything else to
        // ImageMagick first, which resizes with better quality.
        $isAvif = substr($bytes, 4, 8) === 'ftypavif' || substr($bytes, 4, 4) === 'ftyp';
        $order = $isAvif ? ['gd', 'imagick'] : ['imagick', 'gd'];
        $errors = [];
        foreach ($order as $engine) {
            try {
                if ($engine === 'imagick') {
                    if (! class_exists(\Imagick::class)) {
                        continue;
                    }

                    return self::renderImagick($bytes, $width, $format);
                }

                return self::renderGd($bytes, $width, $format);
            } catch (\Throwable $e) {
                $errors[] = $engine.': '.$e->getMessage();
            }
        }

        throw new \RuntimeException(implode(' | ', $errors));
    }

    private static function renderImagick(string $bytes, int $width, string $format): string
    {
        $im = new \Imagick;
        $im->readImageBlob($bytes);
        if ($im->getNumberImages() > 1) {
            $im = $im->coalesceImages();
            $im->setIteratorIndex(0);
            $im = $im->getImage();
        }
        if (method_exists($im, 'autoOrient')) {
            $im->autoOrient();
        }
        if ($width > 0 && $im->getImageWidth() > $width) {
            $im->resizeImage($width, 0, \Imagick::FILTER_LANCZOS, 1);
        }
        if ($format === 'jpg') {
            $im->setImageBackgroundColor('white');
            $im = $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        }
        $im->stripImage();
        $im->setImageFormat($format === 'jpg' ? 'jpeg' : $format);
        $im->setImageCompressionQuality($width === 0 ? self::FULL_QUALITY : self::QUALITY);
        if ($format === 'webp') {
            $im->setOption('webp:method', '4');
        }
        $out = $im->getImageBlob();
        $im->clear();

        return $out;
    }

    private static function renderGd(string $bytes, int $width, string $format): string
    {
        $src = @imagecreatefromstring($bytes);
        if ($src === false) {
            throw new \RuntimeException('unreadable image');
        }
        $w = imagesx($src);
        $h = imagesy($src);
        imagepalettetotruecolor($src);
        imagealphablending($src, false);
        imagesavealpha($src, true);
        if ($width > 0 && $w > $width) {
            $dst = imagescale($src, $width, (int) round($h * $width / $w), IMG_BICUBIC);
            imagedestroy($src);
            $src = $dst;
            imagealphablending($src, false);
            imagesavealpha($src, true);
        }
        ob_start();
        match ($format) {
            'webp' => imagewebp($src, null, $width === 0 ? self::FULL_QUALITY : self::QUALITY),
            'png' => imagepng($src, null, 6),
            default => imagejpeg($src, null, ($width === 0 ? self::FULL_QUALITY : self::QUALITY) + 2),
        };
        imagedestroy($src);

        return (string) ob_get_clean();
    }

    /** Atomic write so a concurrent request never serves a half-written file. */
    private static function write(string $target, string $bytes): void
    {
        $dir = dirname($target);
        if (! is_dir($dir) && ! @mkdir($dir, 0755, true) && ! is_dir($dir)) {
            throw new \RuntimeException('cannot create '.$dir);
        }
        $tmp = $target.'.'.bin2hex(random_bytes(4)).'.tmp';
        if (file_put_contents($tmp, $bytes) === false || ! rename($tmp, $target)) {
            @unlink($tmp);
            throw new \RuntimeException('cannot write '.$target);
        }
    }
}
