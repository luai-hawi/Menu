<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\DeleteRestaurantRequest;
use App\Http\Requests\Admin\DeleteUnusedAccountRequest;
use App\Http\Requests\Admin\StoreRestaurantRequest;
use App\Http\Requests\Admin\UpdateRestaurantNotesRequest;
use App\Http\Requests\Admin\UpdateRestaurantRequest;
use App\Http\Requests\Admin\UpdateSubscriptionRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Admin\AccountCleanupService;
use App\Services\Admin\AdminActionException;
use App\Services\Admin\RestaurantProvisioningService;
use App\Services\Admin\SubscriptionState;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AdminController extends Controller
{
    public function __construct(
        private readonly RestaurantProvisioningService $provisioning,
        private readonly AccountCleanupService $cleanup,
    ) {
    }

    public function createRestaurant()
    {
        $owners = User::query()
            ->where('role', '!=', 'admin')
            ->where(fn ($q) => $q->where('role', 'restaurant_owner')->orWhereHas('restaurants'))
            ->withCount('restaurants')
            ->orderBy('name')
            ->get();

        return view('admin.create-restaurant', compact('owners'));
    }

    public function storeRestaurant(StoreRestaurantRequest $request): RedirectResponse
    {
        try {
            $result = $this->provisioning->create($request->validated(), $request->file('logo'));
        } catch (AdminActionException $e) {
            return back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['owner' => $e->getMessage()]);
        } catch (UniqueConstraintViolationException $e) {
            // Lost a race with another request between validation and insert.
            return back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['slug' => __('admin.validation.create_conflict')]);
        } catch (Throwable $e) {
            Log::error('Admin restaurant creation failed.', ['exception' => $e]);

            return back()->withInput($request->except('password', 'password_confirmation'))
                ->with('error', __('admin.errors.create_failed'));
        }

        $key = $result['owner_created'] ? 'admin.flash.restaurant_created_new_owner' : 'admin.flash.restaurant_created';

        return redirect()->route('dashboard')->with('success', __($key, [
            'name' => $result['restaurant']->name,
            'email' => $result['owner']->email,
        ]));
    }

    public function editRestaurant(Restaurant $restaurant)
    {
        $restaurant->load('user');

        return view('admin.edit-restaurant', compact('restaurant'));
    }

    public function updateRestaurant(UpdateRestaurantRequest $request, Restaurant $restaurant): RedirectResponse
    {
        $data = $request->validated();

        $restaurant->fill([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
        ]);
        $restaurant->admin_notes = $data['admin_notes'] ?? null;

        try {
            $restaurant->save();
        } catch (UniqueConstraintViolationException $e) {
            return back()->withInput()->withErrors(['slug' => __('admin.validation.create_conflict')]);
        }

        return redirect()->route('dashboard')->with('success', __('admin.flash.restaurant_updated', ['name' => $restaurant->name]));
    }

    public function updateRestaurantNotes(UpdateRestaurantNotesRequest $request, Restaurant $restaurant): RedirectResponse
    {
        $restaurant->admin_notes = $request->validated('admin_notes');
        $restaurant->save();

        return $this->backToDashboard()->with('success', __('admin.flash.notes_saved', ['name' => $restaurant->name]));
    }

    public function toggleRestaurant(Restaurant $restaurant): RedirectResponse
    {
        $restaurant->is_active = ! $restaurant->is_active;
        $restaurant->save();

        return $this->backToDashboard()->with('success', __(
            $restaurant->is_active ? 'admin.flash.restaurant_activated' : 'admin.flash.restaurant_deactivated',
            ['name' => $restaurant->name]
        ));
    }

    public function deleteRestaurant(DeleteRestaurantRequest $request, Restaurant $restaurant): RedirectResponse
    {
        $name = $restaurant->name;
        $ownerEmail = $restaurant->user?->email;

        try {
            $result = $this->cleanup->deleteRestaurant($restaurant, $request->wantsOwnerDeleted(), $request->user());
        } catch (Throwable $e) {
            Log::error('Admin restaurant deletion failed.', ['restaurant_id' => $restaurant->id, 'exception' => $e]);

            return $this->backToDashboard()->with('error', __('admin.errors.delete_failed', ['name' => $name]));
        }

        $messages = [__('admin.flash.restaurant_deleted', ['name' => $name])];

        if ($result['owner_deleted']) {
            $messages[] = __('admin.flash.owner_deleted', ['email' => $ownerEmail]);
        } elseif ($request->wantsOwnerDeleted() && $result['owner_status'] !== AccountCleanupService::OWNER_DELETABLE) {
            $messages[] = __('admin.delete_restaurant.owner_kept.'.$result['owner_status'], ['email' => $ownerEmail]);
        }

        $redirect = $this->backToDashboard()->with('success', implode(' ', $messages));

        if ($result['media_failures'] !== []) {
            $redirect->with('error', __('admin.errors.media_cleanup_failed', [
                'count' => count($result['media_failures']),
                'paths' => implode(', ', $result['media_failures']),
            ]));
        }

        return $redirect;
    }

    public function destroyUser(DeleteUnusedAccountRequest $request, User $user): RedirectResponse
    {
        try {
            $this->cleanup->deleteUnusedAccount($user, $request->user());
        } catch (AdminActionException $e) {
            return $this->backToDashboard()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            Log::error('Admin account deletion failed.', ['user_id' => $user->id, 'exception' => $e]);

            return $this->backToDashboard()->with('error', __('admin.errors.account_delete_failed'));
        }

        return $this->backToDashboard()->with('success', __('admin.flash.account_deleted', ['email' => $user->email]));
    }

    public function editSubscription(Subscription $subscription)
    {
        $subscription->load('user');

        return view('admin.edit-subscription', compact('subscription'));
    }

    public function updateSubscription(UpdateSubscriptionRequest $request, Subscription $subscription): RedirectResponse
    {
        $data = $request->validated();

        $subscription->amount = $data['amount'];
        $subscription->expires_at = filled($data['next_payment_date'] ?? null)
            ? Carbon::parse($data['next_payment_date'])->startOfDay()
            : null;
        $subscription->save();

        return redirect()->route('dashboard')->with('success', __('admin.flash.subscription_updated'));
    }

    public function markPaid(Subscription $subscription): RedirectResponse
    {
        $updated = DB::transaction(function () use ($subscription) {
            $subscription = Subscription::query()->lockForUpdate()->findOrFail($subscription->id);

            // Guards against double submits extending the paid period twice.
            if (! SubscriptionState::isDue($subscription)) {
                return null;
            }

            $base = $subscription->expires_at && $subscription->expires_at->isFuture()
                ? $subscription->expires_at->copy()
                : now()->startOfDay();

            $subscription->paid_at = now();
            $subscription->expires_at = $base->addYear();
            $subscription->save();

            return $subscription;
        });

        if (! $updated) {
            return $this->backToDashboard()->with('error', __('admin.errors.subscription_not_due'));
        }

        return $this->backToDashboard()->with('success', __('admin.flash.subscription_paid', [
            'date' => $updated->expires_at->translatedFormat('j M Y'),
        ]));
    }

    public function editUser(User $user)
    {
        $subscription = $user->subscriptions()->orderBy('id')->first();
        $user->loadCount('restaurants');

        return view('admin.edit-user', compact('user', 'subscription'));
    }

    public function updateUser(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($user, $data) {
            $user->fill([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ]);

            if (filled($data['password'] ?? null)) {
                $user->password = $data['password'];
            }

            $user->save();

            if ($user->isAdmin()) {
                return;
            }

            $subscription = $user->subscriptions()->orderBy('id')->first();

            if (filled($data['expires_at'] ?? null)) {
                if (! $subscription) {
                    $subscription = new Subscription(['amount' => SubscriptionState::DEFAULT_AMOUNT]);
                    $subscription->user_id = $user->id;
                }
                $subscription->expires_at = Carbon::parse($data['expires_at'])->startOfDay();
                $subscription->save();
            } elseif ($subscription && array_key_exists('expires_at', $data)) {
                $subscription->expires_at = null;
                $subscription->save();
            }
        });

        return redirect()->route('dashboard')->with('success', __('admin.flash.user_updated', ['name' => $user->name]));
    }

    /**
     * Return to the dashboard, keeping its current search/filter/page query when
     * the action was triggered from there.
     */
    private function backToDashboard(): RedirectResponse
    {
        $previous = url()->previous();
        $dashboard = route('dashboard');

        return redirect()->to(str_starts_with($previous, $dashboard) ? $previous : $dashboard);
    }
}