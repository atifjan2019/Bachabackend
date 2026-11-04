<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

/**
 * Every image an admin uploads is re-encoded before it is stored: decoded,
 * rotated upright, capped at MAX_SIDE pixels on the long edge and written
 * as WebP. The bucket then only ever holds images the server can read and
 * resize, so the storefront never meets a file it cannot display (the old
 * uploads were AVIF data behind .png names, some with no content type).
 *
 * Files that are not raster images (PDF receipts, video) pass through
 * untouched. A file that claims to be an image but cannot be decoded is
 * rejected with a validation error instead of being stored.
 */
class UploadNormalizer
{
    public const MAX_SIDE = 2000;

    public const QUALITY = 85;

    public const IMAGE_EXT = ['jpg', 'jpeg', 'jfif', 'png', 'gif', 'webp', 'avif', 'heic', 'heif', 'bmp', 'tif', 'tiff'];

    /**
     * @return array{bytes: string, extension: string, mime: string}|null  null = not an image, store as-is
     *
     * @throws ValidationException
     */
    public static function normalize(UploadedFile $file): ?array
    {
        $ext = strtolower((string) ($file->getClientOriginalExtension() ?: $file->guessExtension()));
        $mime = (string) $file->getMimeType();
        if (! in_array($ext, self::IMAGE_EXT, true) && ! str_starts_with($mime, 'image/')) {
            return null;
        }
        if (in_array($ext, ['svg', 'ico'], true) || in_array($mime, ['image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon'], true)) {
            return null;
        }

        $bytes = (string) file_get_contents($file->getRealPath());
        try {
            $out = self::encode($bytes);
        } catch (\Throwable $e) {
            report($e);
            throw ValidationException::withMessages([
                'upload' => 'That file could not be read as an image ('.$file->getClientOriginalName().'). Please upload a JPG, PNG or WebP.',
            ]);
        }

        return ['bytes' => $out, 'extension' => 'webp', 'mime' => 'image/webp'];
    }

    /** Decode with whichever library can read the bytes, then re-encode as WebP. */
    public static function encode(string $bytes): string
    {
        $isAvif = substr($bytes, 4, 4) === 'ftyp';
        $engines = $isAvif ? ['gd', 'imagick'] : ['imagick', 'gd'];
        $errors = [];
        foreach ($engines as $engine) {
            try {
                if ($engine === 'imagick') {
                    if (! class_exists(\Imagick::class)) {
                        continue;
                    }

                    return self::withImagick($bytes);
                }

                return self::withGd($bytes);
            } catch (\Throwable $e) {
                $errors[] = $engine.': '.$e->getMessage();
            }
        }

        throw new \RuntimeException(implode(' | ', $errors) ?: 'no image library available');
    }

    private static function withImagick(string $bytes): string
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
        $w = $im->getImageWidth();
        $h = $im->getImageHeight();
        if ($w < 1 || $h < 1) {
            throw new \RuntimeException('empty image');
        }
        if (max($w, $h) > self::MAX_SIDE) {
            $w > $h ? $im->resizeImage(self::MAX_SIDE, 0, \Imagick::FILTER_LANCZOS, 1) : $im->resizeImage(0, self::MAX_SIDE, \Imagick::FILTER_LANCZOS, 1);
        }
        $im->stripImage();
        $im->setImageFormat('webp');
        $im->setImageCompressionQuality(self::QUALITY);
        $im->setOption('webp:method', '4');
        $out = $im->getImageBlob();
        $im->clear();

        return $out;
    }

    private static function withGd(string $bytes): string
    {
        $src = @imagecreatefromstring($bytes);
        if ($src === false) {
            throw new \RuntimeException('unreadable image');
        }
        if (function_exists('exif_read_data')) {
            // Rotate camera photos upright before the orientation tag is lost.
            $exif = @exif_read_data('data://image/jpeg;base64,'.base64_encode($bytes));
            $orientation = (int) ($exif['Orientation'] ?? 1);
            if (in_array($orientation, [3, 6, 8], true)) {
                $src = imagerotate($src, [3 => 180, 6 => -90, 8 => 90][$orientation], 0) ?: $src;
            }
        }
        imagepalettetotruecolor($src);
        imagealphablending($src, false);
        imagesavealpha($src, true);
        $w = imagesx($src);
        $h = imagesy($src);
        if (max($w, $h) > self::MAX_SIDE) {
            $scale = self::MAX_SIDE / max($w, $h);
            $dst = imagescale($src, (int) round($w * $scale), (int) round($h * $scale), IMG_BICUBIC);
            imagedestroy($src);
            $src = $dst;
            imagealphablending($src, false);
            imagesavealpha($src, true);
        }
        ob_start();
        imagewebp($src, null, self::QUALITY);
        imagedestroy($src);

        return (string) ob_get_clean();
    }
}
