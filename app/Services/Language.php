<?php

namespace App\Services;

/**
 * Language — the single source of truth for menu languages.
 *
 * A customer opening a menu for the first time has no cookie and no `?lang=`
 * query, so the restaurant owner's choice decides what they see. Once the
 * customer switches language the cookie is set and their own choice wins from
 * then on. See resolve() for the precedence.
 */
class Language
{
    /**
     * Locales a menu may be published in, in menu order.
     *
     * @var array<int, string>
     */
    public const SUPPORTED = ['ar', 'en'];

    public function isSupported(mixed $locale): bool
    {
        return is_string($locale) && in_array($locale, self::SUPPORTED, true);
    }

    /**
     * Resolve the locale to render with, taking the first supported candidate.
     *
     * Callers pass candidates in descending priority, so the precedence is
     * visible at the call site:
     *
     *     $language->resolve(
     *         $request->query('lang'),          // explicit ?lang= wins
     *         $request->cookie('app_locale'),   // the customer's own choice
     *         $restaurant->defaultLanguage(),   // the owner's first-visit default
     *     );
     *
     * Anything unsupported or missing falls back to the app locale.
     */
    public function resolve(mixed ...$candidates): string
    {
        foreach ($candidates as $candidate) {
            if ($this->isSupported($candidate)) {
                return $candidate;
            }
        }

        return $this->fallback();
    }

    /**
     * @return array<int, string>
     */
    public function supported(): array
    {
        return self::SUPPORTED;
    }

    /**
     * Display label for a locale, in both the owner's and the customer's language.
     *
     * @return array<string, string> locale => endonym
     */
    public function labels(): array
    {
        return [
            'ar' => (string) __('messages.arabic'),
            'en' => (string) __('messages.english'),
        ];
    }

    /**
     * Direction for a locale, used by the menu shell to set the `dir` attribute.
     */
    public function direction(?string $locale): string
    {
        return $locale === 'ar' ? 'rtl' : 'ltr';
    }

    private function fallback(): string
    {
        $locale = config('app.locale');

        return $this->isSupported($locale) ? $locale : (string) self::SUPPORTED[0];
    }
}
