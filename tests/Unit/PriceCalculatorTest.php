<?php

use App\Services\PriceCalculator;

/*
 * These unit tests intentionally avoid the database — they construct
 * lightweight stdClass "option" rows and verify the pure math of
 * PriceCalculator::calculate() and applyModifiers().
 * The DB-aware calculateWithValidation() path is covered by a feature test.
 */

function fakeOption(int $id, float $delta, bool $active = true): object
{
    return (object) [
        'id' => $id,
        'price_delta' => $delta,
        'is_active' => $active,
    ];
}

test('base price with no options returns base price rounded', function () {
    $calc = new PriceCalculator;
    expect($calc->calculate(10, [], collect()))->toBe(10.00);
    expect($calc->calculate('9.999', [], collect()))->toBe(10.00);
});

test('positive deltas are added to base price', function () {
    $calc = new PriceCalculator;
    $options = collect([
        fakeOption(1, 2.00),
        fakeOption(2, 1.50),
    ]);
    expect($calc->calculate(10, [1, 2], $options))->toBe(13.50);
});

test('negative deltas discount the base price', function () {
    $calc = new PriceCalculator;
    $options = collect([
        fakeOption(10, -1.00), // Small pizza
    ]);
    expect($calc->calculate(8, [10], $options))->toBe(7.00);
});

test('inactive options are ignored', function () {
    $calc = new PriceCalculator;
    $options = collect([
        fakeOption(1, 2.00, active: true),
        fakeOption(2, 5.00, active: false),
    ]);
    expect($calc->calculate(10, [1, 2], $options))->toBe(12.00);
});

test('unknown selected ids are silently ignored', function () {
    $calc = new PriceCalculator;
    $options = collect([fakeOption(1, 2.00)]);
    expect($calc->calculate(10, [1, 999], $options))->toBe(12.00);
});

test('duplicates in the selection list count once', function () {
    $calc = new PriceCalculator;
    $options = collect([fakeOption(1, 2.00)]);
    expect($calc->calculate(10, [1, 1, 1], $options))->toBe(12.00);
});

test('price never goes below zero', function () {
    $calc = new PriceCalculator;
    $options = collect([fakeOption(1, -9999.00)]);
    expect($calc->calculate(5, [1], $options))->toBe(0.00);
});

test('modifiers apply discount before tax (percent)', function () {
    $calc = new PriceCalculator;
    // subtotal 100 → -10% = 90 → +16% tax = 104.40
    expect($calc->applyModifiers(100, ['discount_percent' => 10, 'tax_rate' => 16]))->toBe(104.40);
});

test('modifiers apply discount before tax (absolute)', function () {
    $calc = new PriceCalculator;
    // subtotal 100 → -5 = 95 → +10% = 104.50
    expect($calc->applyModifiers(100, ['discount_amount' => 5, 'tax_rate' => 10]))->toBe(104.50);
});

test('tax-only calculation', function () {
    $calc = new PriceCalculator;
    expect($calc->applyModifiers(50, ['tax_rate' => 16]))->toBe(58.00);
});

test('no modifiers returns the subtotal unchanged', function () {
    $calc = new PriceCalculator;
    expect($calc->applyModifiers(42.50, []))->toBe(42.50);
});

test('discount cannot push total below zero', function () {
    $calc = new PriceCalculator;
    expect($calc->applyModifiers(10, ['discount_amount' => 100]))->toBe(0.00);
});

test('full flow: small pizza with extra cheese, 10% off, 15% tax', function () {
    $calc = new PriceCalculator;
    $options = collect([
        fakeOption(1, -1.00), // Small
        fakeOption(2, 1.50),  // Extra cheese
    ]);
    $subtotal = $calc->calculate(8.00, [1, 2], $options);
    expect($subtotal)->toBe(8.50);

    $final = $calc->applyModifiers($subtotal, [
        'discount_percent' => 10,
        'tax_rate' => 15,
    ]);
    // 8.50 * 0.9 = 7.65  → * 1.15 = 8.7975 → 8.80
    expect($final)->toBe(8.80);
});
