<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Any change made in the admin panel shows on the website at once: after a
 * successful write request (POST/PUT/PATCH/DELETE) the whole application
 * cache is cleared — rendered storefront pages, catalogue queries, the
 * category tree, the media library list. Individual controllers no longer
 * have to remember to do it.
 */
class FlushStorefrontCacheAfterAdminWrite
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS'], true) && $response->getStatusCode() < 400) {
            Cache::flush();
        }

        return $response;
    }
}
