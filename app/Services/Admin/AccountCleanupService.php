<?php

namespace App\Services\Admin;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Deletes restaurants and unused owner accounts. Database rows are removed in a
 * transaction; media files are only removed after the commit succeeded so a
 * rollback never leaves rows pointing at deleted files.
 */
class AccountCleanupService
{
    public const OWNER_DELETABLE = 'deletable';

    public const OWNER_KEEP_ADMIN = 'admin';

    public const OWNER_KEEP_SELF = 'self';

    public const OWNER_KEEP_SHARED = 'shared';

    public const OWNER_KEEP_NOT_OWNER = 'not_owner';

    /**
     * Why (or whether) an owner account may be removed together with one of its restaurants.
     * $restaurantsOwned is the owner's restaurant count *including* the one being deleted.
     */
    public function ownerDeletionStatus(?User $owner, User $actor, int $restaurantsOwned): string
    {
        return match (true) {
            ! $owner => self::OWNER_KEEP_NOT_OWNER,
            $owner->id === $actor->id => self::OWNER_KEEP_SELF,
            $owner->isAdmin() => self::OWNER_KEEP_ADMIN,
            ! $owner->isRestaurantOwner() => self::OWNER_KEEP_NOT_OWNER,
            $restaurantsOwned > 1 => self::OWNER_KEEP_SHARED,
            default => self::OWNER_DELETABLE,
        };
    }

    /**
     * @return array{owner_deleted: bool, owner_status: string, media_failures: list<string>}
     */
    public function deleteRestaurant(Restaurant $restaurant, bool $deleteOwner, User $actor): array
    {
        $result = DB::transaction(function () use ($restaurant, $deleteOwner, $actor) {
            $restaurant = Restaurant::query()->lockForUpdate()->findOrFail($restaurant->id);
            $owner = $restaurant->user_id ? User::query()->lockForUpdate()->find($restaurant->user_id) : null;
            $ownerStatus = $this->ownerDeletionStatus(
                $owner,
                $actor,
                $owner ? $owner->restaurants()->count() : 0,
            );

            $media = $this->restaurantMedia($restaurant);
            $this->deleteRestaurantRows($restaurant);

            $ownerDeleted = false;
            if ($deleteOwner && $ownerStatus === self::OWNER_DELETABLE && ! $owner->restaurants()->exists()) {
                $this->deleteUserRows($owner);
                $ownerDeleted = true;
            }

            return ['owner_deleted' => $ownerDeleted, 'owner_status' => $ownerStatus, 'media' => $media];
        });

        Log::info('Admin deleted restaurant.', [
            'restaurant_id' => $restaurant->id,
            'slug' => $restaurant->slug,
            'actor_id' => $actor->id,
            'owner_id' => $restaurant->user_id,
            'owner_deleted' => $result['owner_deleted'],
        ]);

        return [
            'owner_deleted' => $result['owner_deleted'],
            'owner_status' => $result['owner_status'],
            'media_failures' => $this->deleteMedia($result['media']),
        ];
    }

    /**
     * Remove an account that owns no restaurants (e.g. left over from older deletions).
     */
    public function deleteUnusedAccount(User $user, User $actor): void
    {
        DB::transaction(function () use ($user, $actor) {
            $user = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($user->id === $actor->id) {
                throw new AdminActionException(__('admin.errors.cannot_delete_self'));
            }

            if ($user->isAdmin()) {
                throw new AdminActionException(__('admin.errors.cannot_delete_admin'));
            }

            if ($user->restaurants()->exists()) {
                throw new AdminActionException(__('admin.errors.account_has_restaurants'));
            }

            $this->deleteUserRows($user);
        });

        Log::info('Admin deleted unused account.', ['user_id' => $user->id, 'actor_id' => $actor->id]);
    }

    /**
     * @return list<string>
     */
    private function restaurantMedia(Restaurant $restaurant): array
    {
        $paths = [$restaurant->logo, $restaurant->background_image];

        if ($this->restaurantsHaveColumn('welcome_video')) {
            $paths[] = $restaurant->getAttribute('welcome_video');
        }

        $itemImages = MenuItem::query()
            ->whereIn('menu_category_id', MenuCategory::query()->select('id')->where('restaurant_id', $restaurant->id))
            ->whereNotNull('image')
            ->pluck('image')
            ->all();

        return array_values(array_unique(array_filter(
            array_merge($paths, $itemImages),
            fn ($path) => is_string($path) && $path !== ''
        )));
    }

    /**
     * Explicit deletes so cleanup does not depend on FK cascades (option tables
     * were created without real foreign keys).
     */
    private function deleteRestaurantRows(Restaurant $restaurant): void
    {
        $categoryIds = MenuCategory::query()->where('restaurant_id', $restaurant->id)->pluck('id');
        $itemIds = MenuItem::query()->whereIn('menu_category_id', $categoryIds)->pluck('id');
        $groupIds = DB::table('menu_item_option_groups')->whereIn('menu_item_id', $itemIds)->pluck('id');

        DB::table('menu_item_options')->whereIn('option_group_id', $groupIds)->delete();
        DB::table('menu_item_option_groups')->whereIn('id', $groupIds)->delete();
        MenuItem::query()->whereIn('id', $itemIds)->delete();
        MenuCategory::query()->whereIn('id', $categoryIds)->delete();
        $restaurant->delete();
    }

    private function deleteUserRows(User $user): void
    {
        $user->subscriptions()->delete();
        DB::table('sessions')->where('user_id', $user->id)->delete();
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        $user->delete();
    }

    /**
     * @param  list<string>  $paths
     * @return list<string> paths that could not be deleted
     */
    private function deleteMedia(array $paths): array
    {
        $disk = Storage::disk('public');
        $failures = [];

        foreach ($paths as $path) {
            if ($this->isStillReferenced($path)) {
                Log::notice('Skipped deleting media still referenced by another record.', ['path' => $path]);

                continue;
            }

            try {
                if (! $disk->exists($path)) {
                    Log::warning('Media file already missing during restaurant cleanup.', ['path' => $path]);

                    continue;
                }

                if (! $disk->delete($path)) {
                    $failures[] = $path;
                    Log::error('Failed to delete media file after restaurant deletion.', ['path' => $path]);
                }
            } catch (Throwable $e) {
                $failures[] = $path;
                Log::error('Failed to delete media file after restaurant deletion.', [
                    'path' => $path,
                    'exception' => $e,
                ]);
            }
        }

        return $failures;
    }

    private function isStillReferenced(string $path): bool
    {
        $restaurantQuery = Restaurant::query()->where(function ($q) use ($path) {
            $q->where('logo', $path)->orWhere('background_image', $path);

            if ($this->restaurantsHaveColumn('welcome_video')) {
                $q->orWhere('welcome_video', $path);
            }
        });

        return $restaurantQuery->exists() || MenuItem::query()->where('image', $path)->exists();
    }

    private function restaurantsHaveColumn(string $column): bool
    {
        static $cache = [];

        return $cache[$column] ??= Schema::hasColumn('restaurants', $column);
    }
}