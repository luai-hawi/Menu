<?php

namespace App\Services\Admin;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;

/**
 * Single source of truth for subscription semantics:
 *  - expires_at = date the next payment is due (paid-through date).
 *  - paid_at    = when the most recent payment was recorded.
 * A subscription "needs attention" (is due) when it was never paid or its due date has passed.
 */
class SubscriptionState
{
    public const DEFAULT_AMOUNT = 100.00;

    public const EXPIRED = 'expired';

    public const UNPAID = 'unpaid';

    public const ACTIVE = 'active';

    public const NO_DUE_DATE = 'no_due_date';

    public static function for(?Subscription $subscription): ?string
    {
        if (! $subscription) {
            return null;
        }

        if ($subscription->expires_at && $subscription->expires_at->isPast()) {
            return self::EXPIRED;
        }

        if (! $subscription->paid_at) {
            return self::UNPAID;
        }

        return $subscription->expires_at ? self::ACTIVE : self::NO_DUE_DATE;
    }

    public static function isDue(Subscription $subscription): bool
    {
        return in_array(self::for($subscription), [self::EXPIRED, self::UNPAID], true);
    }

    public static function scopeDue(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('paid_at')->orWhere('expires_at', '<', now());
        });
    }
}