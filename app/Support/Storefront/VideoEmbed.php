<?php

namespace App\Support\Storefront;

/**
 * Embed URLs for admin-entered YouTube/Vimeo links (hero background and promo
 * popup). Anything else is treated by the caller as a direct video file.
 */
class VideoEmbed
{
    /** Muted, looping, autoplaying background embed, or null. */
    public static function background(?string $url): ?string
    {
        [$yt, $vimeo] = self::ids($url);
        if ($yt) {
            return "https://www.youtube.com/embed/{$yt}?autoplay=1&mute=1&loop=1&playlist={$yt}&controls=0&showinfo=0&modestbranding=1&rel=0&playsinline=1";
        }
        if ($vimeo) {
            return "https://player.vimeo.com/video/{$vimeo}?autoplay=1&muted=1&loop=1&background=1";
        }

        return null;
    }

    /** Playable (controls, sound) embed, or null. */
    public static function playable(?string $url): ?string
    {
        [$yt, $vimeo] = self::ids($url);
        if ($yt) {
            return "https://www.youtube.com/embed/{$yt}?autoplay=1&rel=0&playsinline=1";
        }
        if ($vimeo) {
            return "https://player.vimeo.com/video/{$vimeo}?autoplay=1";
        }

        return null;
    }

    /** @return array{0: ?string, 1: ?string} [youtubeId, vimeoId] */
    private static function ids(?string $url): array
    {
        $url = trim((string) $url);
        if ($url === '') {
            return [null, null];
        }
        $parts = parse_url($url);
        if (! is_array($parts) || empty($parts['host'])) {
            return [null, null];
        }
        $host = preg_replace('/^www\./', '', strtolower($parts['host']));
        $path = $parts['path'] ?? '';
        parse_str($parts['query'] ?? '', $query);

        $yt = null;
        if ($host === 'youtu.be') {
            $yt = ltrim($path, '/') ?: null;
        } elseif (in_array($host, ['youtube.com', 'm.youtube.com'], true)) {
            $yt = $query['v'] ?? null;
            if (! $yt && preg_match('#/(?:embed|shorts)/([^/?]+)#', $path, $m)) {
                $yt = $m[1];
            }
        }
        if ($yt && ! preg_match('/^[A-Za-z0-9_-]+$/', $yt)) {
            $yt = null;
        }

        $vimeo = null;
        if ($host === 'vimeo.com') {
            $segments = array_values(array_filter(explode('/', $path)));
            $vimeo = isset($segments[0]) && preg_match('/^\d+$/', $segments[0]) ? $segments[0] : null;
        }

        return [$yt, $vimeo];
    }
}
