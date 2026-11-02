<?php

namespace App\Providers;

use App\Support\Storefront\SiteSettings;
use App\View\Composers\StorefrontComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One settings read per request for every storefront view and helper.
        $this->app->singleton(SiteSettings::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Storefront views (and their components) get $settings, $categories
        // and $customer without each controller passing them along.
        View::composer(['shop.*', 'components.shop.*'], StorefrontComposer::class);

        try {
            $site_settings = \App\Models\Setting::pluck('setting_value', 'setting_key')->toArray();
            \Illuminate\Support\Facades\View::share('site_settings', $site_settings);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\View::share('site_settings', []);
        }
    }
}
