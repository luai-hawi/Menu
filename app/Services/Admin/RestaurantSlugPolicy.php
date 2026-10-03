<?php

namespace App\Services\Admin;

use Illuminate\Routing\Router;

/**
 * Public menus live at "/{slug}", so a slug must never shadow an application
 * route (dashboard, admin, login...) or a public asset directory.
 */
class RestaurantSlugPolicy
{
    public const PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public const MAX_LENGTH = 100;

    private const STATIC_RESERVED = [
        'admin', 'api', 'build', 'css', 'js', 'images', 'img', 'storage', 'vendor',
        'favicon.ico', 'robots.txt', 'index.php', 'up', 'lang', 'locale', 'menu',
        'login', 'logout', 'register', 'dashboard', 'profile', 'restaurant',
    ];

    public function __construct(private readonly Router $router)
    {
    }

    /**
     * @return list<string>
     */
    public function reserved(): array
    {
        $fromRoutes = collect($this->router->getRoutes()->getRoutes())
            ->map(fn ($route) => strtolower(explode('/', trim($route->uri(), '/'))[0] ?? ''))
            ->filter(fn (string $segment) => $segment !== '' && ! str_starts_with($segment, '{'));

        return $fromRoutes
            ->merge(self::STATIC_RESERVED)
            ->unique()
            ->values()
            ->all();
    }

    public function isReserved(string $slug): bool
    {
        return in_array(strtolower($slug), $this->reserved(), true);
    }

    public static function normalize(?string $slug): ?string
    {
        if ($slug === null) {
            return null;
        }

        return strtolower(trim($slug));
    }
}