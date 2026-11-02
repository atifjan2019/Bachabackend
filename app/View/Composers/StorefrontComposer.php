<?php

namespace App\View\Composers;

use App\Support\Storefront\Catalog;
use App\Support\Storefront\SiteSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Shares what every storefront view needs: the admin settings helper, the
 * category tree (mega menu, filters, tiles) and the signed-in customer.
 */
class StorefrontComposer
{
    public function __construct(private readonly SiteSettings $settings)
    {
    }

    public function compose(View $view): void
    {
        $view->with([
            'settings' => $this->settings,
            'categories' => Catalog::categoryTree(),
            'customer' => Auth::guard('customer')->user(),
        ]);
    }
}
