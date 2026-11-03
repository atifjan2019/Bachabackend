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

    public function show(string $width, string $key): Response
    {
        $width = (int) $width;
        if (! in_array($width, ImageVariant::WIDTHS, true)
            || str_contains($key, '..')
            || ! preg_match('#^(m|l)/([A-Za-z0-9_\-./ ]+\.(?:jpe?g|png|webp|avif|gif|jfif))\.(webp|png|jpg)$#i', $key, $m)) {
            return self::notFound();
        }
        $sourceKey = $m[1].'/'.$m[2];
        $format = strtolower($m[3]);
        $target = public_path('img/'.$width.'/'.$key);

        if (is_file($target)) {
            return $this->file($target, $format);
        }

        $source = ImageVariant::source($sourceKey);
        if ($source === null) {
            return self::notFound();
        }

        try {
            $bytes = isset($source['path']) ? file_get_contents($source['path']) : $this->fetch($source['url']);
            $out = $this->render($bytes, $width, $format);
            $this->write($target, $out);
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
    private function fetch(string $url): string
    {
        // No image/avif in Accept: the CDN would otherwise hand back AVIF,
        // which not every image library on the server can decode.
        $res = Http::timeout(20)->withHeaders(['Accept' => 'image/webp,image/png,image/jpeg,image/*;q=0.8'])->get($url);
        if (! $res->successful() || ! str_starts_with((string) $res->header('Content-Type'), 'image/')) {
            throw new \RuntimeException('source responded '.$res->status().' '.$res->header('Content-Type'));
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
        if ($im->getImageWidth() > $width) {
            $im->resizeImage($width, 0, \Imagick::FILTER_LANCZOS, 1);
        }
        if ($format === 'jpg') {
            $im->setImageBackgroundColor('white');
            $im = $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        }
        $im->stripImage();
        $im->setImageFormat($format === 'jpg' ? 'jpeg' : $format);
        $im->setImageCompressionQuality(self::QUALITY);
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
        if ($w > $width) {
            $dst = imagescale($src, $width, (int) round($h * $width / $w), IMG_BICUBIC);
            imagedestroy($src);
            $src = $dst;
            imagealphablending($src, false);
            imagesavealpha($src, true);
        }
        ob_start();
        match ($format) {
            'webp' => imagewebp($src, null, self::QUALITY),
            'png' => imagepng($src, null, 6),
            default => imagejpeg($src, null, self::QUALITY + 2),
        };
        imagedestroy($src);

        return (string) ob_get_clean();
    }

    /** Atomic write so a concurrent request never serves a half-written file. */
    private function write(string $target, string $bytes): void
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
