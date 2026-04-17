<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            $site_settings = \App\Models\Setting::pluck('setting_value', 'setting_key')->toArray();
            \Illuminate\Support\Facades\View::share('site_settings', $site_settings);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\View::share('site_settings', []);
        }
    }
}
