<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends the bare domain to the www host the storefront is canonical on,
 * keeping path and query (bachastylo.com/products?x → www.bachastylo.com/products?x).
 * Only the exact apex host is redirected; admin.* and any other host pass through.
 */
class RedirectToPrimaryHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $primary = (string) config('app.primary_host', '');
        $host = strtolower($request->getHost());

        if ($primary !== '' && $host === preg_replace('/^www\./', '', $primary) && $host !== $primary) {
            return redirect()->to('https://'.$primary.$request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
