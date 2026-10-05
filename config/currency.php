<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | ISO 4217 code assigned to newly created restaurants that never picked
    | one. Existing rows keep whatever the owner selected.
    |
    */

    'default' => 'ILS',

    /*
    |--------------------------------------------------------------------------
    | Supported Currencies
    |--------------------------------------------------------------------------
    |
    | Each entry carries the symbol shown beside prices and the position the
    | symbol defaults to. The owner can override the position per restaurant,
    | because conventions differ even between speakers of the same language.
    |
    | position: "before" -> $12.00   |   "after" -> 12.00 د.إ
    |
    */

    'position_before' => 'before',

    'position_after' => 'after',

    'currencies' => [

        // Middle East
        'ILS' => ['symbol' => '₪', 'position' => 'before'],
        'AED' => ['symbol' => 'د.إ', 'position' => 'after'],
        'SAR' => ['symbol' => 'ر.س', 'position' => 'after'],
        'QAR' => ['symbol' => 'ر.ق', 'position' => 'after'],
        'KWD' => ['symbol' => 'د.ك', 'position' => 'after'],
        'BHD' => ['symbol' => 'د.ب', 'position' => 'after'],
        'OMR' => ['symbol' => 'ر.ع', 'position' => 'after'],
        'JOD' => ['symbol' => 'د.أ', 'position' => 'after'],
        'EGP' => ['symbol' => 'ج.م', 'position' => 'after'],
        'LBP' => ['symbol' => 'ل.ل', 'position' => 'before'],
        'SYP' => ['symbol' => 'ل.س', 'position' => 'before'],
        'YER' => ['symbol' => 'ر.ي', 'position' => 'after'],
        'IQD' => ['symbol' => 'ع.د', 'position' => 'after'],
        'MAD' => ['symbol' => 'د.م.', 'position' => 'after'],

        // Global
        'USD' => ['symbol' => '$', 'position' => 'before'],
        'EUR' => ['symbol' => '€', 'position' => 'after'],
        'GBP' => ['symbol' => '£', 'position' => 'before'],
        'CAD' => ['symbol' => '$', 'position' => 'before'],
        'AUD' => ['symbol' => '$', 'position' => 'before'],
        'CHF' => ['symbol' => 'CHF', 'position' => 'before'],
        'TRY' => ['symbol' => '₺', 'position' => 'before'],
        'RUB' => ['symbol' => '₽', 'position' => 'after'],
        'JPY' => ['symbol' => '¥', 'position' => 'before'],
        'CNY' => ['symbol' => '¥', 'position' => 'before'],
        'INR' => ['symbol' => '₹', 'position' => 'before'],
        'ZAR' => ['symbol' => 'R', 'position' => 'before'],

    ],

];
