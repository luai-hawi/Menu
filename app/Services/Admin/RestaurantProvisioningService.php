<?php

namespace App\Services\Admin;

use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class RestaurantProvisioningService
{
    public function __construct(private ImageService $images)
    {
    }

    /**
     * Create a restaurant (and, when requested, its owner account + subscription)
     * atomically. The uploaded logo is removed again if the transaction fails.
     *
     * @param  array<string, mixed>  $data  validated StoreRestaurantRequest data
     * @return array{restaurant: Restaurant, owner: User, owner_created: bool}
     */
    public function create(array $data, ?UploadedFile $logo = null): array
    {
        $logoPath = null;

        if ($logo) {
            try {
                $logoPath = $this->images->uploadAndCompressImage($logo, 'logos', 400, 85);
            } catch (\RuntimeException $exception) {
                Log::error('Admin restaurant logo could not be stored.', ['exception' => $exception]);

                throw new AdminActionException(__('admin.errors.logo_upload_failed'));
            }
        }

        try {
            return DB::transaction(function () use ($data, $logoPath) {
                [$owner, $ownerCreated] = $data['owner_method'] === 'new'
                    ? [$this->createOwner($data), true]
                    : [$this->resolveExistingOwner((int) $data['user_id']), false];

                $amount = isset($data['subscription_amount']) && $data['subscription_amount'] !== null
                    ? $data['subscription_amount']
                    : SubscriptionState::DEFAULT_AMOUNT;

                if (! $owner->subscriptions()->exists()) {
                    $subscription = new Subscription(['amount' => $amount]);
                    $subscription->user_id = $owner->id;
                    $subscription->save();
                }

                $restaurant = new Restaurant([
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'description' => $data['description'] ?? null,
                ]);
                $restaurant->user_id = $owner->id;
                $restaurant->logo = $logoPath;
                $restaurant->admin_notes = $data['admin_notes'] ?? null;
                $restaurant->save();

                return ['restaurant' => $restaurant, 'owner' => $owner, 'owner_created' => $ownerCreated];
            });
        } catch (Throwable $e) {
            if ($logoPath && ! Storage::disk('public')->delete($logoPath)) {
                Log::error('Admin restaurant creation failed and the uploaded logo could not be removed.', [
                    'path' => $logoPath,
                ]);
            }

            throw $e;
        }
    }

    private function createOwner(array $data): User
    {
        $email = Str::lower($data['owner_email']);

        $owner = new User([
            'name' => filled($data['owner_name'] ?? null) ? $data['owner_name'] : Str::before($email, '@'),
            'email' => $email,
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
        ]);

        // role / email_verified_at are intentionally not mass assignable.
        $owner->forceFill([
            'role' => 'restaurant_owner',
            'email_verified_at' => now(),
        ])->save();

        return $owner;
    }

    private function resolveExistingOwner(int $userId): User
    {
        $owner = User::query()->lockForUpdate()->findOrFail($userId);

        if ($owner->isAdmin()) {
            throw new AdminActionException(__('admin.errors.admin_cannot_own'));
        }

        if ($owner->role !== 'restaurant_owner') {
            $owner->forceFill(['role' => 'restaurant_owner'])->save();
        }

        return $owner;
    }
}