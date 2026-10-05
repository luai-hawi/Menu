<?php

namespace App\Services;

/**
 * Currency — resolves the currency an owner picked for their restaurant and
 * renders amounts with its symbol on either side of the number.
 *
 * Codes and symbols live in config/currency.php so an owner never has to type
 * a symbol by hand. Unknown or missing codes fall back to the configured
 * default instead of throwing, so a bad row can never break the menu.
 */
class Currency
{
    /**
     * Supported codes with their symbol, default position and a label that
     * reads "USD — US Dollar" / "ILS — الشيكل".
     *
     * @return array<string, array{symbol: string, position: string, label: string}>
     */
    public function options(): array
    {
        $options = [];

        foreach ($this->currencies() as $code => $currency) {
            $options[$code] = [
                'symbol' => $currency['symbol'],
                'position' => $this->normalizePosition($currency['position'] ?? null),
                'label' => $code.' — '.$this->name($code),
            ];
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    public function codes(): array
    {
        return array_keys($this->currencies());
    }

    public function isSupported(?string $code): bool
    {
        return $code !== null && array_key_exists($code, $this->currencies());
    }

    public function symbol(?string $code): string
    {
        $currencies = $this->currencies();

        return $currencies[$this->resolve($code)]['symbol'] ?? $currencies[config('currency.default')]['symbol'];
    }

    /**
     * The restaurant's own override when valid, otherwise the currency default.
     */
    public function position(?string $code, ?string $position = null): string
    {
        $currencies = $this->currencies();
        $resolved = $this->resolve($code);

        if ($this->isPosition($position)) {
            return $position;
        }

        return $this->normalizePosition($currencies[$resolved]['position'] ?? null);
    }

    /**
     * Format an amount for display, e.g. "$12.00" or "12.00 د.إ".
     */
    public function format(mixed $amount, ?string $code = null, ?string $position = null): string
    {
        $number = number_format((float) $amount, 2);
        $symbol = $this->symbol($code);

        return $this->position($code, $position) === 'after'
            ? $number.' '.$symbol
            : $symbol.$number;
    }

    /**
     * Display name for a code, falling back to the code when unnamed.
     */
    public function name(string $code): string
    {
        $key = 'messages.currency.names.'.$code;
        $name = __($key);

        return is_string($name) && $name !== $key ? $name : $code;
    }

    /**
     * @return array<string, array{symbol: string, position?: string}>
     */
    private function currencies(): array
    {
        return (array) config('currency.currencies', []);
    }

    private function resolve(?string $code): string
    {
        $code = strtoupper(trim((string) $code));

        return $this->isSupported($code) ? $code : (string) config('currency.default');
    }

    private function normalizePosition(?string $position): string
    {
        return $this->isPosition($position) ? (string) $position : (string) config('currency.position_before');
    }

    private function isPosition(mixed $position): bool
    {
        return in_array($position, ['before', 'after'], true);
    }
}
