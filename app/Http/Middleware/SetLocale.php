<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('lang', $request->cookie('app_locale'));

        app()->setLocale(in_array($locale, ['en', 'ar'], true) ? $locale : config('app.locale'));

        return $next($request);
    }
}
