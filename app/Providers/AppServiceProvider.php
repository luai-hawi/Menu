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
        // Set locale from cookie or query parameter
        $request = request();
        $locale = $request->cookie('app_locale');

        if (! $locale) {
            $locale = $request->query('lang');
        }

        if ($locale && in_array($locale, ['en', 'ar'])) {
            app()->setLocale($locale);
        }
    }
}
