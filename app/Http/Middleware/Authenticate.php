<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Where to send a visitor who isn't signed in.
     *
     * The framework's default points at a route named "login". This app names
     * its login route "admin.login", so the default threw
     * "Route [login] not defined" and every guest hitting /admin/* got a 500
     * error page instead of the sign-in form — including anyone whose session
     * had simply expired.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // Storefront customers have their own sign-in page.
        return $request->is('admin', 'admin/*') ? route('admin.login') : route('shop.login');
    }
}
