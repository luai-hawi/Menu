<?php

namespace App\Services\Admin;

use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdminDashboardService
{
    public const PER_PAGE = 15;

    public const STATUS_FILTERS = ['active', 'inactive'];

    public const BILLING_FILTERS = ['due', 'ok', 'none'];

    public function __construct(private readonly AccountCleanupService $cleanup)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(Request $request, User $admin): array
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => in_array($request->query('status'), self::STATUS_FILTERS, true) ? $request->query('status') : '',
            'billing' => in_array($request->query('billing'), self::BILLING_FILTERS, true) ? $request->query('billing') : '',
        ];

        $restaurants = $this->restaurantQuery($filters)
            ->with([
                'user' => fn ($q) => $q->withCount('restaurants')
                    ->with(['subscriptions' => fn ($s) => $s->orderBy('id')]),
            ])
            ->latest()
            ->paginate(self::PER_PAGE, ['*'], 'page')
            ->withQueryString();

        $restaurants->getCollection()->each(function (Restaurant $restaurant) use ($admin) {
            $restaurant->setAttribute('owner_deletion_status', $this->cleanup->ownerDeletionStatus(
                $restaurant->user,
                $admin,
                (int) ($restaurant->user?->restaurants_count ?? 0),
            ));
        });

        $dueSubscriptions = SubscriptionState::scopeDue(Subscription::query())
            ->whereHas('user', fn ($q) => $q->where('role', '!=', 'admin'))
            ->with(['user' => fn ($q) => $q->withCount('restaurants')])
            ->orderByRaw('expires_at is null')
            ->orderBy('expires_at')
            ->orderBy('id')
            ->paginate(10, ['*'], 'subscriptions_page')
            ->withQueryString();

        $unusedAccounts = $this->unusedAccountsQuery()
            ->with(['subscriptions' => fn ($s) => $s->orderBy('id')])
            ->orderBy('name')
            ->paginate(10, ['*'], 'accounts_page')
            ->withQueryString();

        return [
            'restaurants' => $restaurants,
            'dueSubscriptions' => $dueSubscriptions,
            'unusedAccounts' => $unusedAccounts,
            'filters' => $filters,
            'metrics' => $this->metrics(),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function metrics(): array
    {
        return [
            'restaurants' => Restaurant::query()->count(),
            'active_restaurants' => Restaurant::query()->where('is_active', true)->count(),
            'owners' => User::query()->whereHas('restaurants')->count(),
            'due_subscriptions' => SubscriptionState::scopeDue(Subscription::query())
                ->whereHas('user', fn ($q) => $q->where('role', '!=', 'admin'))
                ->count(),
            'unused_accounts' => $this->unusedAccountsQuery()->count(),
        ];
    }

    public function unusedAccountsQuery(): Builder
    {
        return User::query()
            ->where('role', '!=', 'admin')
            ->whereDoesntHave('restaurants');
    }

    /**
     * @param  array{q: string, status: string, billing: string}  $filters
     */
    private function restaurantQuery(array $filters): Builder
    {
        $query = Restaurant::query();

        if ($filters['q'] !== '') {
            $term = '%'.$filters['q'].'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('slug', 'like', $term)
                    ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term));
            });
        }

        if ($filters['status'] !== '') {
            $query->where('is_active', $filters['status'] === 'active');
        }

        match ($filters['billing']) {
            'due' => $query->whereHas('user.subscriptions', fn (Builder $s) => SubscriptionState::scopeDue($s)),
            'ok' => $query->whereHas('user.subscriptions')
                ->whereDoesntHave('user.subscriptions', fn (Builder $s) => SubscriptionState::scopeDue($s)),
            'none' => $query->whereDoesntHave('user.subscriptions'),
            default => null,
        };

        return $query;
    }
}