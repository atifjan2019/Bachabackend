<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets a shared cache/CDN absorb bursts on public storefront pages while
 * browsers keep revalidating. Only applied to successful GETs for guests with
 * nothing flashed in the session, since those responses depend on nobody.
 */
class StorefrontCacheHeaders
{
    public const VALUE = 'public, max-age=0, s-maxage=60, stale-while-revalidate=300';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethod('GET') || $response->getStatusCode() !== 200) {
            return $response;
        }

        if (auth('customer')->check()) {
            return $response;
        }

        $session = $request->hasSession() ? $request->session() : null;
        if ($session && ($session->has('status') || $session->has('toast') || $session->has('errors'))) {
            return $response;
        }

        $response->headers->set('Cache-Control', self::VALUE);

        return $response;
    }
}
