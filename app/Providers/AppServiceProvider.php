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
        // Set locale from cookie
        if (isset($_COOKIE['app_locale'])) {
            app()->setLocale($_COOKIE['app_locale']);
        }
    }
}
