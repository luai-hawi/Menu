<?php

namespace App\Http\Middleware;

use App\Services\Language;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Applies the visitor's own choice only. Restaurant menus resolve their
     * locale in MenuController instead, because only there is the owner's
     * default language known.
     */
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale(app(Language::class)->resolve(
            $request->query('lang'),
            $request->cookie('app_locale'),
        ));

        return $next($request);
    }
}
