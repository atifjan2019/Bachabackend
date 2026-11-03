<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Two layers of caching for public storefront pages, both only for successful
 * GETs by guests with nothing flashed in the session — responses that depend
 * on nobody in particular:
 *
 *  1. Cache-Control lets a shared cache/CDN absorb bursts while browsers keep
 *     revalidating.
 *  2. The rendered HTML is kept in the application cache for a few minutes,
 *     so a repeat request skips the controller and views entirely. The CSRF
 *     token is the one thing in the page that is per visitor, so it is
 *     swapped for a placeholder when stored and filled in on every hit.
 *     Admin saves call Cache::flush(), which drops these pages too.
 */
class StorefrontCacheHeaders
{
    public const VALUE = 'public, max-age=0, s-maxage=60, stale-while-revalidate=300';

    public const PAGE_TTL = 300;

    public const MAX_PAGE_BYTES = 1_048_576;

    private const TOKEN_PLACEHOLDER = '__BSF_CSRF_TOKEN__';

    public function handle(Request $request, Closure $next): Response
    {
        $cacheable = $this->cacheableRequest($request);
        $key = $cacheable ? 'storefront.page.'.md5($request->getHost().'|'.$request->fullUrl()) : null;

        if ($key && ($page = Cache::get($key)) && is_array($page)) {
            $html = str_replace(self::TOKEN_PLACEHOLDER, $request->session()->token(), $page['html']);

            return response($html, 200, [
                'Content-Type' => $page['type'],
                'Cache-Control' => self::VALUE,
                'X-Storefront-Cache' => 'hit',
            ]);
        }

        $response = $next($request);

        if (! $cacheable || $response->getStatusCode() !== 200) {
            return $response;
        }

        $session = $request->session();
        if ($session->has('status') || $session->has('toast') || $session->has('errors')) {
            return $response;
        }

        $response->headers->set('Cache-Control', self::VALUE);

        $type = (string) $response->headers->get('Content-Type', '');
        $html = $response->getContent();
        if ($key && str_starts_with($type, 'text/html') && is_string($html) && strlen($html) <= self::MAX_PAGE_BYTES) {
            $token = $session->token();
            Cache::put($key, [
                'html' => $token !== '' ? str_replace($token, self::TOKEN_PLACEHOLDER, $html) : $html,
                'type' => $type,
            ], self::PAGE_TTL);
            $response->headers->set('X-Storefront-Cache', 'miss');
        }

        return $response;
    }

    private function cacheableRequest(Request $request): bool
    {
        return $request->isMethod('GET')
            && $request->hasSession()
            && ! $request->ajax()
            && ! $request->wantsJson()
            && ! auth('customer')->check();
    }
}
