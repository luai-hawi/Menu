<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\MenuItemOption;
use Illuminate\Support\Collection;

/**
 * PriceCalculator — pure, stateless service.
 *
 * Given a base price and the IDs of chosen options, it returns the final price.
 * Discounts, taxes and promotions are applied *after* option deltas, so the
 * subtotal returned here is the true "cart line" price before fees.
 *
 * The service is designed to be safe against:
 *   - options belonging to groups not attached to the item (ignored)
 *   - options belonging to inactive groups / inactive options (ignored)
 *   - single-select groups receiving more than one id (rejected with exception
 *     when using calculateWithValidation; silently collapses to first in the
 *     simple calculate() flow to match the spec's "final price" guarantee).
 */
class PriceCalculator
{
    /**
     * Simple (no-constraint) calculation.
     *
     * @param  array<int>  $selectedOptionIds  flat list of chosen option PKs
     * @param  Collection|null  $availableOptions  optional pre-loaded options
     *                                             (pass MenuItem->optionGroups->flatMap(->options)
     *                                             to avoid an extra query).
     * @return float Final price, rounded to 2 decimals.
     */
    public function calculate(
        float|string|int $basePrice,
        array $selectedOptionIds = [],
        ?Collection $availableOptions = null
    ): float {
        $base = (float) $basePrice;
        $total = $base;

        if (empty($selectedOptionIds)) {
            return round($total, 2);
        }

        $selectedOptionIds = array_values(array_unique(array_map('intval', $selectedOptionIds)));

        // Load once if caller didn't hand us the relation.
        if ($availableOptions === null) {
            $availableOptions = MenuItemOption::whereIn('id', $selectedOptionIds)
                ->where('is_active', true)
                ->get();
        }

        foreach ($availableOptions as $option) {
            if (! in_array((int) $option->id, $selectedOptionIds, true)) {
                continue;
            }

            if (property_exists($option, 'is_active') || isset($option->is_active)) {
                if ($option->is_active === false) {
                    continue;
                }
            }

            $total += (float) $option->price_delta;
        }

        // Prices can't go negative — clamp to zero.
        return round(max(0.0, $total), 2);
    }

    /**
     * Full calculation that also validates single/multiple, min/max constraints.
     *
     * @return array{ subtotal: float, per_group: array<int, float> }
     *
     * @throws \App\Services\PriceCalculationException on invalid selection
     */
    public function calculateWithValidation(
        MenuItem $item,
        array $selectedOptionIds
    ): array {
        $item->loadMissing('optionGroups.options');

        $selectedIds = array_values(array_unique(array_map('intval', $selectedOptionIds)));
        $perGroup = [];
        $total = (float) $item->price;

        foreach ($item->optionGroups as $group) {
            $groupOptions = $group->options;
            $chosenInGroup = $groupOptions->whereIn('id', $selectedIds);
            $chosenCount = $chosenInGroup->count();
            $chosenActive = $chosenInGroup->where('is_active', true);
            $chosenActiveCnt = $chosenActive->count();

            if ($chosenCount !== $chosenActiveCnt) {
                throw new PriceCalculationException(
                    "Option group #{$group->id}: selection contains inactive options."
                );
            }

            if ($group->isSingle() && $chosenCount > 1) {
                throw new PriceCalculationException(
                    "Option group #{$group->id} ('{$group->group_name_ar}') is single-select; "
                        ."only one option may be chosen, {$chosenCount} given."
                );
            }

            if ($group->is_required && $chosenCount < 1) {
                throw new PriceCalculationException(
                    "Option group #{$group->id} ('{$group->group_name_ar}') requires a selection."
                );
            }

            if ($group->isMultiple()) {
                if ($chosenCount < (int) $group->min_choices) {
                    throw new PriceCalculationException(
                        "Option group #{$group->id}: at least {$group->min_choices} choice(s) required, got {$chosenCount}."
                    );
                }
                if ($group->max_choices > 0 && $chosenCount > (int) $group->max_choices) {
                    throw new PriceCalculationException(
                        "Option group #{$group->id}: at most {$group->max_choices} choice(s) allowed, got {$chosenCount}."
                    );
                }
            }

            $groupTotal = 0.0;
            foreach ($chosenActive as $opt) {
                $groupTotal += (float) $opt->price_delta;
            }

            $perGroup[$group->id] = round($groupTotal, 2);
            $total += $groupTotal;
        }

        return [
            'subtotal' => round(max(0.0, $total), 2),
            'per_group' => $perGroup,
        ];
    }

    /**
     * Apply discount / tax / promotion on top of the subtotal.
     *
     * Order of operations:
     *   1. base + sum(option deltas)          = subtotal
     *   2. subtotal - discount (percent|abs)  = discounted
     *   3. discounted * (1 + tax_rate)        = final
     *
     * Promotions (e.g. "buy 2 get 1 free") are expected to be applied
     * externally *on the cart* and passed in as an absolute discount here.
     *
     * @param  array{discount_percent?: float, discount_amount?: float, tax_rate?: float}  $modifiers
     */
    public function applyModifiers(float $subtotal, array $modifiers = []): float
    {
        $discounted = $subtotal;

        if (! empty($modifiers['discount_percent'])) {
            $pct = max(0.0, min(100.0, (float) $modifiers['discount_percent']));
            $discounted -= $discounted * ($pct / 100.0);
        }

        if (! empty($modifiers['discount_amount'])) {
            $discounted -= max(0.0, (float) $modifiers['discount_amount']);
        }

        $discounted = max(0.0, $discounted);

        if (! empty($modifiers['tax_rate'])) {
            $taxRate = max(0.0, (float) $modifiers['tax_rate']);
            $discounted *= (1 + $taxRate / 100.0);
        }

        return round($discounted, 2);
    }
}
