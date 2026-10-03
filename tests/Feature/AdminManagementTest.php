<?php

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/*
 * Regression coverage for the admin area: restaurant provisioning, private notes,
 * restaurant/owner deletion, unused-account cleanup, subscriptions, user edits,
 * the admin dashboard and the owner empty state.
 */

function adminMgmtAdmin(array $attributes = []): User
{
    return User::factory()->create(array_merge(['role' => 'admin'], $attributes));
}

function adminMgmtOwner(array $attributes = []): User
{
    return User::factory()->create(array_merge(['role' => 'restaurant_owner'], $attributes));
}

function adminMgmtRestaurant(User $owner, array $attributes = []): Restaurant
{
    $restaurant = new Restaurant;
    $restaurant->forceFill(array_merge([
        'name' => 'Cafe '.uniqid(),
        'slug' => 'cafe-'.strtolower(uniqid()),
        'user_id' => $owner->id,
        'is_active' => true,
    ], $attributes))->save();

    return $restaurant;
}

function adminMgmtNewOwnerPayload(array $overrides = []): array
{
    return array_merge([
        'owner_method' => 'new',
        'owner_name' => 'New Owner',
        'owner_email' => 'new.owner@example.com',
        'phone' => '0599000000',
        'password' => 'secret-pass-123',
        'password_confirmation' => 'secret-pass-123',
        'name' => 'Brand New Place',
        'slug' => 'brand-new-place',
        'description' => 'Tasty',
        'subscription_amount' => 150,
        'admin_notes' => 'Paid in cash',
    ], $overrides);
}

beforeEach(function () {
    Storage::fake('public');
});

// ---------------------------------------------------------------------------
// Access
// ---------------------------------------------------------------------------

test('non-admin users cannot reach admin routes', function () {
    $owner = adminMgmtOwner();
    $restaurant = adminMgmtRestaurant($owner);

    $this->actingAs($owner)->get(route('admin.restaurant.create'))->assertForbidden();
    $this->actingAs($owner)->post(route('admin.restaurant.store'), adminMgmtNewOwnerPayload())->assertForbidden();
    $this->actingAs($owner)->delete(route('admin.restaurant.delete', $restaurant), ['confirm_name' => $restaurant->name])->assertForbidden();
    $this->actingAs($owner)->delete(route('admin.user.destroy', $owner), ['confirm_email' => $owner->email])->assertForbidden();

    expect(Restaurant::whereKey($restaurant->id)->exists())->toBeTrue();
});

test('admin pages render', function () {
    $admin = adminMgmtAdmin();
    $owner = adminMgmtOwner();
    $restaurant = adminMgmtRestaurant($owner);
    $subscription = Subscription::create(['user_id' => $owner->id, 'amount' => 100]);

    $this->actingAs($admin)->get(route('admin.restaurant.create'))->assertOk()->assertSee($owner->email);
    $this->actingAs($admin)->get(route('admin.restaurant.edit', $restaurant))->assertOk();
    $this->actingAs($admin)->get(route('admin.user.edit', $owner))->assertOk();
    $this->actingAs($admin)->get(route('admin.subscription.edit', $subscription))->assertOk();
    $this->actingAs($admin)->get(route('admin.index'))->assertRedirect(route('dashboard'));
});

// ---------------------------------------------------------------------------
// Restaurant creation
// ---------------------------------------------------------------------------

test('creating a restaurant with a new owner sets role and verification despite mass assignment guards', function () {
    $admin = adminMgmtAdmin();

    $this->actingAs($admin)
        ->post(route('admin.restaurant.store'), adminMgmtNewOwnerPayload([
            'owner_email' => 'New.Owner@Example.com',
            'logo' => UploadedFile::fake()->image('logo.png'),
        ]))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('success');

    $owner = User::where('email', 'new.owner@example.com')->firstOrFail();
    expect($owner->role)->toBe('restaurant_owner')
        ->and($owner->email_verified_at)->not->toBeNull()
        ->and(Hash::check('secret-pass-123', $owner->password))->toBeTrue();

    $restaurant = Restaurant::where('slug', 'brand-new-place')->firstOrFail();
    expect($restaurant->user_id)->toBe($owner->id)
        ->and($restaurant->admin_notes)->toBe('Paid in cash')
        ->and($restaurant->logo)->not->toBeNull()
        ->and($restaurant->toArray())->not->toHaveKey('admin_notes');
    Storage::disk('public')->assertExists($restaurant->logo);

    $subscription = $owner->subscriptions()->sole();
    expect((float) $subscription->amount)->toBe(150.0)
        ->and($subscription->paid_at)->toBeNull();
});

test('creating a restaurant for an existing owner does not duplicate the subscription', function () {
    $admin = adminMgmtAdmin();
    $owner = adminMgmtOwner();
    adminMgmtRestaurant($owner);
    Subscription::create(['user_id' => $owner->id, 'amount' => 80]);

    $this->actingAs($admin)
        ->post(route('admin.restaurant.store'), [
            'owner_method' => 'existing',
            'user_id' => $owner->id,
            'name' => 'Second Branch',
            'slug' => 'second-branch',
        ])
        ->assertRedirect(route('dashboard'));

    expect($owner->restaurants()->count())->toBe(2)
        ->and($owner->subscriptions()->count())->toBe(1);
});

test('restaurant creation rejects reserved and malformed slugs', function (string $slug) {
    $admin = adminMgmtAdmin();

    $this->actingAs($admin)
        ->from(route('admin.restaurant.create'))
        ->post(route('admin.restaurant.store'), adminMgmtNewOwnerPayload(['slug' => $slug]))
        ->assertRedirect(route('admin.restaurant.create'))
        ->assertSessionHasErrors('slug');

    expect(Restaurant::count())->toBe(0)
        ->and(User::where('email', 'new.owner@example.com')->exists())->toBeFalse();
})->with(['dashboard', 'admin', 'login', 'storage', 'has space', 'bad--dash', '-leading', 'ÅÄÖ']);

test('restaurant creation rejects a duplicate slug and an existing email (case-insensitive)', function () {
    $admin = adminMgmtAdmin();
    $existing = adminMgmtOwner(['email' => 'taken@example.com']);
    adminMgmtRestaurant($existing, ['slug' => 'taken-slug']);

    $this->actingAs($admin)
        ->post(route('admin.restaurant.store'), adminMgmtNewOwnerPayload([
            'slug' => 'Taken-Slug',
            'owner_email' => 'TAKEN@example.com',
        ]))
        ->assertSessionHasErrors(['slug', 'owner_email']);

    expect(Restaurant::count())->toBe(1);
});

test('admins cannot be chosen as restaurant owners', function () {
    $admin = adminMgmtAdmin();
    $otherAdmin = adminMgmtAdmin();

    $this->actingAs($admin)
        ->post(route('admin.restaurant.store'), [
            'owner_method' => 'existing',
            'user_id' => $otherAdmin->id,
            'name' => 'Nope',
            'slug' => 'nope',
        ])
        ->assertSessionHasErrors('user_id');

    expect(Restaurant::count())->toBe(0);
});

test('failed restaurant creation rolls back the new owner and removes the uploaded logo', function () {
    $admin = adminMgmtAdmin();
    // Force the restaurant insert to fail after the owner row was written.
    DB::statement('CREATE TRIGGER adm_fail_restaurant BEFORE INSERT ON restaurants BEGIN SELECT RAISE(ABORT, \'boom\'); END;');

    $this->actingAs($admin)
        ->post(route('admin.restaurant.store'), adminMgmtNewOwnerPayload([
            'logo' => UploadedFile::fake()->image('logo.png'),
        ]))
        ->assertSessionHas('error');

    expect(User::where('email', 'new.owner@example.com')->exists())->toBeFalse()
        ->and(Subscription::count())->toBe(0)
        ->and(Storage::disk('public')->allFiles())->toBe([]);
});

test('admin logo uploads are compressed through the image service', function (string $file, string $extension) {
    $admin = adminMgmtAdmin();

    $this->actingAs($admin)
        ->post(route('admin.restaurant.store'), adminMgmtNewOwnerPayload([
            'logo' => UploadedFile::fake()->image($file, 1600, 1200),
        ]))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHasNoErrors();

    $logo = Restaurant::where('slug', 'brand-new-place')->value('logo');
    expect($logo)->toStartWith('logos/')->toEndWith('.'.$extension);
    Storage::disk('public')->assertExists($logo);

    [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($logo));
    expect($width)->toBeLessThanOrEqual(400)
        ->and($height)->toBeLessThanOrEqual(400)
        ->and($width)->toBe(400);
})->with([
    'jpeg' => ['logo.jpg', 'jpg'],
    'png' => ['logo.png', 'png'],
    'webp' => ['logo.webp', 'webp'],
    'gif' => ['logo.gif', 'gif'],
]);

test('admin logo upload rejects non-image and oversized files without creating anything', function (callable $makeFile) {
    $admin = adminMgmtAdmin();

    $this->actingAs($admin)
        ->post(route('admin.restaurant.store'), adminMgmtNewOwnerPayload(['logo' => $makeFile()]))
        ->assertSessionHasErrors('logo');

    expect(Restaurant::count())->toBe(0)
        ->and(User::where('email', 'new.owner@example.com')->exists())->toBeFalse()
        ->and(Storage::disk('public')->allFiles())->toBe([]);
})->with([
    'pdf' => [fn () => UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf')],
    'too large' => [fn () => UploadedFile::fake()->image('logo.png')->size(2049)],
]);

// ---------------------------------------------------------------------------
// Editing restaurants & notes
// ---------------------------------------------------------------------------

test('admin can update restaurant details and private notes', function () {
    $admin = adminMgmtAdmin();
    $restaurant = adminMgmtRestaurant(adminMgmtOwner(), ['slug' => 'old-slug']);

    $this->actingAs($admin)
        ->put(route('admin.restaurant.update', $restaurant), [
            'name' => 'Renamed',
            'slug' => 'new-slug',
            'description' => 'Desc',
            'admin_notes' => 'Call before renewal',
        ])
        ->assertRedirect(route('dashboard'));

    $restaurant->refresh();
    expect($restaurant->name)->toBe('Renamed')
        ->and($restaurant->slug)->toBe('new-slug')
        ->and($restaurant->admin_notes)->toBe('Call before renewal');

    $this->actingAs($admin)
        ->put(route('admin.restaurant.update', $restaurant), ['name' => 'Renamed', 'slug' => 'menu'])
        ->assertSessionHasErrors('slug');
});

test('legacy slugs that do not match the new format can be kept on edit', function () {
    $admin = adminMgmtAdmin();
    $restaurant = adminMgmtRestaurant(adminMgmtOwner(), ['slug' => 'Legacy_Slug']);

    $this->actingAs($admin)
        ->put(route('admin.restaurant.update', $restaurant), ['name' => 'Kept', 'slug' => 'Legacy_Slug'])
        ->assertSessionHasNoErrors();

    expect($restaurant->refresh()->name)->toBe('Kept');
});

test('notes can be edited inline and are shown on the admin dashboard only', function () {
    $admin = adminMgmtAdmin();
    $owner = adminMgmtOwner();
    $restaurant = adminMgmtRestaurant($owner);

    $this->actingAs($admin)
        ->from(route('dashboard', ['q' => 'cafe']))
        ->put(route('admin.restaurant.notes', $restaurant), ['admin_notes' => 'VIP <b>client</b>'])
        ->assertRedirect(route('dashboard', ['q' => 'cafe']));

    expect($restaurant->refresh()->admin_notes)->toBe('VIP <b>client</b>');

    $this->actingAs($admin)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('VIP &lt;b&gt;client&lt;/b&gt;', false);

    $this->get(route('menu.show', $restaurant->slug))->assertDontSee('VIP');
});
// ---------------------------------------------------------------------------
// Restaurant deletion
// ---------------------------------------------------------------------------

function adminMgmtRestaurantWithMenu(User $owner): array
{
    $disk = Storage::disk('public');
    $disk->put('logos/logo.png', 'logo');
    $disk->put('backgrounds/bg.png', 'bg');
    $disk->put('menu-items/item.png', 'item');

    $attributes = ['logo' => 'logos/logo.png', 'background_image' => 'backgrounds/bg.png'];
    if (Schema::hasColumn('restaurants', 'welcome_video')) {
        $disk->put('welcome-videos/intro.mp4', 'video');
        $attributes['welcome_video'] = 'welcome-videos/intro.mp4';
    }

    $restaurant = adminMgmtRestaurant($owner, $attributes);
    $category = MenuCategory::create(['name' => 'Mains', 'restaurant_id' => $restaurant->id, 'sort_order' => 1, 'is_active' => true]);
    $item = new MenuItem;
    $item->forceFill(['name' => 'Burger', 'price' => 10, 'image' => 'menu-items/item.png', 'menu_category_id' => $category->id])->save();
    $groupId = DB::table('menu_item_option_groups')->insertGetId([
        'menu_item_id' => $item->id, 'group_type' => 'SINGLE', 'group_name_ar' => 'Size',
        'min_choices' => 0, 'max_choices' => 1, 'is_required' => false, 'position' => 0,
    ]);
    DB::table('menu_item_options')->insert([
        'option_group_id' => $groupId, 'option_name_ar' => 'Large', 'price_delta' => 1, 'position' => 0, 'is_active' => true,
    ]);

    return [$restaurant, $category, $item, $groupId, array_values($attributes + ['item' => 'menu-items/item.png'])];
}

test('deleting a restaurant removes menu data, media, and the last-restaurant owner with subscription by default', function () {
    $admin = adminMgmtAdmin();
    $owner = adminMgmtOwner();
    Subscription::create(['user_id' => $owner->id, 'amount' => 100]);
    DB::table('password_reset_tokens')->insert(['email' => $owner->email, 'token' => 'x', 'created_at' => now()]);
    [$restaurant, $category, $item, $groupId, $media] = adminMgmtRestaurantWithMenu($owner);

    $this->actingAs($admin)
        ->from(route('dashboard'))
        ->delete(route('admin.restaurant.delete', $restaurant), ['confirm_name' => $restaurant->name])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('success')
        ->assertSessionMissing('error');

    expect(Restaurant::whereKey($restaurant->id)->exists())->toBeFalse()
        ->and(MenuCategory::whereKey($category->id)->exists())->toBeFalse()
        ->and(MenuItem::whereKey($item->id)->exists())->toBeFalse()
        ->and(DB::table('menu_item_option_groups')->where('id', $groupId)->exists())->toBeFalse()
        ->and(DB::table('menu_item_options')->where('option_group_id', $groupId)->exists())->toBeFalse()
        ->and(User::whereKey($owner->id)->exists())->toBeFalse()
        ->and(Subscription::where('user_id', $owner->id)->exists())->toBeFalse()
        ->and(DB::table('password_reset_tokens')->where('email', $owner->email)->exists())->toBeFalse();

    foreach ($media as $path) {
        Storage::disk('public')->assertMissing($path);
    }
});

test('owner account is kept when the admin unchecks the delete-owner option', function () {
    $admin = adminMgmtAdmin();
    $owner = adminMgmtOwner();
    Subscription::create(['user_id' => $owner->id, 'amount' => 100]);
    $restaurant = adminMgmtRestaurant($owner);

    $this->actingAs($admin)
        ->delete(route('admin.restaurant.delete', $restaurant), ['confirm_name' => $restaurant->name, 'delete_owner' => '0'])
        ->assertSessionHas('success');

    expect(Restaurant::whereKey($restaurant->id)->exists())->toBeFalse()
        ->and(User::whereKey($owner->id)->exists())->toBeTrue()
        ->and($owner->subscriptions()->count())->toBe(1);
});

test('owners of other restaurants and admin owners are never deleted with a restaurant', function () {
    $admin = adminMgmtAdmin();
    $shared = adminMgmtOwner();
    $first = adminMgmtRestaurant($shared);
    $second = adminMgmtRestaurant($shared);
    $adminOwner = adminMgmtAdmin();
    $adminRestaurant = adminMgmtRestaurant($adminOwner);
    $selfRestaurant = adminMgmtRestaurant($admin);

    $this->actingAs($admin)
        ->delete(route('admin.restaurant.delete', $first), ['confirm_name' => $first->name, 'delete_owner' => '1'])
        ->assertSessionHas('success', fn ($m) => str_contains($m, __('admin.delete_restaurant.owner_kept.shared', ['email' => $shared->email])));
    $this->actingAs($admin)
        ->delete(route('admin.restaurant.delete', $adminRestaurant), ['confirm_name' => $adminRestaurant->name, 'delete_owner' => '1']);
    $this->actingAs($admin)
        ->delete(route('admin.restaurant.delete', $selfRestaurant), ['confirm_name' => $selfRestaurant->name, 'delete_owner' => '1']);

    expect(User::whereKey($shared->id)->exists())->toBeTrue()
        ->and(Restaurant::whereKey($second->id)->exists())->toBeTrue()
        ->and(User::whereKey($adminOwner->id)->exists())->toBeTrue()
        ->and(User::whereKey($admin->id)->exists())->toBeTrue()
        ->and(Restaurant::whereIn('id', [$first->id, $adminRestaurant->id, $selfRestaurant->id])->count())->toBe(0);
});

test('restaurant deletion requires the exact restaurant name and preserves the owner choice on error', function () {
    $admin = adminMgmtAdmin();
    $owner = adminMgmtOwner();
    $restaurant = adminMgmtRestaurant($owner, ['name' => 'Exact Name']);

    $this->actingAs($admin)
        ->from(route('dashboard'))
        ->delete(route('admin.restaurant.delete', $restaurant), [
            'confirm_name' => 'exact name',
            'delete_owner' => '0',
            'delete_target' => $restaurant->slug,
            'delete_target_name' => 'Exact Name',
        ])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHasErrorsIn('restaurantDeletion', 'confirm_name');

    expect(Restaurant::whereKey($restaurant->id)->exists())->toBeTrue()
        ->and(User::whereKey($owner->id)->exists())->toBeTrue();

    // The dashboard re-opens the modal with the previous choice.
    $this->actingAs($admin)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('deleteOwner: false', false)
        ->assertSee(route('admin.restaurant.delete', $restaurant), false);
});

test('media still referenced by another restaurant is not deleted', function () {
    $admin = adminMgmtAdmin();
    Storage::disk('public')->put('logos/shared.png', 'x');
    $a = adminMgmtRestaurant(adminMgmtOwner(), ['logo' => 'logos/shared.png']);
    adminMgmtRestaurant(adminMgmtOwner(), ['logo' => 'logos/shared.png']);

    $this->actingAs($admin)->delete(route('admin.restaurant.delete', $a), ['confirm_name' => $a->name]);

    Storage::disk('public')->assertExists('logos/shared.png');
});

// ---------------------------------------------------------------------------
// Unused account cleanup
// ---------------------------------------------------------------------------

test('admin can delete an orphan account after confirming its email', function () {
    $admin = adminMgmtAdmin();
    $orphan = adminMgmtOwner(['email' => 'orphan@example.com']);
    Subscription::create(['user_id' => $orphan->id, 'amount' => 100]);

    $this->actingAs($admin)
        ->delete(route('admin.user.destroy', $orphan), ['confirm_email' => 'wrong@example.com'])
        ->assertSessionHasErrorsIn('accountDeletion', 'confirm_email');
    expect(User::whereKey($orphan->id)->exists())->toBeTrue();

    $this->actingAs($admin)
        ->delete(route('admin.user.destroy', $orphan), ['confirm_email' => 'ORPHAN@example.com'])
        ->assertSessionHas('success');

    expect(User::whereKey($orphan->id)->exists())->toBeFalse()
        ->and(Subscription::where('user_id', $orphan->id)->exists())->toBeFalse();
});

test('self, admins and owners with restaurants cannot be deleted as unused accounts', function () {
    $admin = adminMgmtAdmin();
    $otherAdmin = adminMgmtAdmin();
    $owner = adminMgmtOwner();
    adminMgmtRestaurant($owner);

    foreach ([$admin, $otherAdmin, $owner] as $user) {
        $this->actingAs($admin)
            ->delete(route('admin.user.destroy', $user), ['confirm_email' => $user->email])
            ->assertSessionHas('error');
        expect(User::whereKey($user->id)->exists())->toBeTrue();
    }
});

// ---------------------------------------------------------------------------
// Subscriptions & users
// ---------------------------------------------------------------------------

test('mark paid extends from a future due date or from today, and refuses paid-up subscriptions', function () {
    $this->travelTo(now()->setDate(2026, 3, 10)->setTime(12, 0));
    $admin = adminMgmtAdmin();

    $future = Subscription::create(['user_id' => adminMgmtOwner()->id, 'amount' => 100, 'expires_at' => '2026-05-01']);
    $expired = Subscription::create(['user_id' => adminMgmtOwner()->id, 'amount' => 100, 'expires_at' => '2025-01-01', 'paid_at' => '2024-01-01']);
    $paid = Subscription::create(['user_id' => adminMgmtOwner()->id, 'amount' => 100, 'expires_at' => '2026-12-01', 'paid_at' => '2026-01-01']);

    $this->actingAs($admin)->post(route('admin.subscription.mark-paid', $future))->assertSessionHas('success');
    $this->actingAs($admin)->post(route('admin.subscription.mark-paid', $expired))->assertSessionHas('success');
    $this->actingAs($admin)->post(route('admin.subscription.mark-paid', $paid))->assertSessionHas('error');

    expect($future->refresh()->expires_at->toDateString())->toBe('2027-05-01')
        ->and($future->paid_at)->not->toBeNull()
        ->and($expired->refresh()->expires_at->toDateString())->toBe('2027-03-10')
        ->and($paid->refresh()->expires_at->toDateString())->toBe('2026-12-01');
});

test('subscription edit validates amount and clears the date when emptied', function () {
    $admin = adminMgmtAdmin();
    $subscription = Subscription::create(['user_id' => adminMgmtOwner()->id, 'amount' => 100, 'expires_at' => '2026-05-01']);

    $this->actingAs($admin)
        ->put(route('admin.subscription.update', $subscription), ['amount' => -5])
        ->assertSessionHasErrors('amount');

    $this->actingAs($admin)
        ->put(route('admin.subscription.update', $subscription), ['amount' => 120, 'next_payment_date' => ''])
        ->assertRedirect(route('dashboard'));

    expect($subscription->refresh()->expires_at)->toBeNull()
        ->and((float) $subscription->amount)->toBe(120.0);
});

test('user edit enforces case-insensitive unique email, hashes passwords and manages the subscription date', function () {
    $admin = adminMgmtAdmin();
    adminMgmtOwner(['email' => 'taken@example.com']);
    $owner = adminMgmtOwner(['email' => 'me@example.com']);

    $this->actingAs($admin)
        ->put(route('admin.user.update', $owner), ['name' => 'Me', 'email' => 'TAKEN@example.com'])
        ->assertSessionHasErrors('email');

    $this->actingAs($admin)
        ->put(route('admin.user.update', $owner), [
            'name' => 'Me Updated',
            'email' => 'Me@Example.com',
            'password' => 'another-pass-1',
            'password_confirmation' => 'another-pass-1',
            'expires_at' => '2026-08-15',
        ])
        ->assertSessionHasNoErrors();

    $owner->refresh();
    expect($owner->name)->toBe('Me Updated')
        ->and($owner->email)->toBe('me@example.com')
        ->and($owner->role)->toBe('restaurant_owner')
        ->and(Hash::check('another-pass-1', $owner->password))->toBeTrue()
        ->and($owner->subscriptions()->sole()->expires_at->toDateString())->toBe('2026-08-15');

    // Role cannot be escalated through the form.
    $this->actingAs($admin)
        ->put(route('admin.user.update', $owner), ['name' => 'Me', 'email' => 'me@example.com', 'role' => 'admin']);
    expect($owner->refresh()->role)->toBe('restaurant_owner');
});

// ---------------------------------------------------------------------------
// Dashboards
// ---------------------------------------------------------------------------

test('admin dashboard shows correct metrics, search, filters and pagination', function () {
    $admin = adminMgmtAdmin();
    $alice = adminMgmtOwner(['name' => 'Alice', 'email' => 'alice@example.com']);
    $bob = adminMgmtOwner(['name' => 'Bob', 'email' => 'bob@example.com']);
    adminMgmtOwner(['email' => 'orphan@example.com']);
    Subscription::create(['user_id' => $alice->id, 'amount' => 100, 'paid_at' => now(), 'expires_at' => now()->addMonth()]);
    Subscription::create(['user_id' => $bob->id, 'amount' => 100, 'expires_at' => now()->subDay()]);

    adminMgmtRestaurant($alice, ['name' => 'Alpha Grill', 'slug' => 'alpha-grill']);
    adminMgmtRestaurant($bob, ['name' => 'Beta Bistro', 'slug' => 'beta-bistro', 'is_active' => false]);
    foreach (range(1, 15) as $i) {
        adminMgmtRestaurant($alice, ['name' => "Filler $i", 'slug' => "filler-$i"]);
    }

    $response = $this->actingAs($admin)->get(route('dashboard'))->assertOk();
    expect($response->viewData('metrics'))->toBe([
        'restaurants' => 17,
        'active_restaurants' => 16,
        'owners' => 2,
        'due_subscriptions' => 1,
        'unused_accounts' => 1,
    ])
        ->and($response->viewData('restaurants')->count())->toBe(15)
        ->and($response->viewData('restaurants')->total())->toBe(17);

    $this->actingAs($admin)->get(route('dashboard', ['page' => 2]))->assertOk()
        ->assertViewHas('restaurants', fn ($p) => $p->count() === 2);

    $this->actingAs($admin)->get(route('dashboard', ['q' => 'bob@example']))
        ->assertViewHas('restaurants', fn ($p) => $p->pluck('slug')->all() === ['beta-bistro']);
    $this->actingAs($admin)->get(route('dashboard', ['status' => 'inactive']))
        ->assertViewHas('restaurants', fn ($p) => $p->pluck('slug')->all() === ['beta-bistro']);
    $this->actingAs($admin)->get(route('dashboard', ['billing' => 'due']))
        ->assertViewHas('restaurants', fn ($p) => $p->pluck('slug')->all() === ['beta-bistro']);
    $this->actingAs($admin)->get(route('dashboard', ['billing' => 'ok']))
        ->assertViewHas('restaurants', fn ($p) => $p->total() === 16);
    $this->actingAs($admin)->get(route('dashboard', ['billing' => 'none']))
        ->assertViewHas('restaurants', fn ($p) => $p->total() === 0);
});

test('admin dashboard renders in Arabic with RTL direction', function () {
    $admin = adminMgmtAdmin();
    adminMgmtRestaurant(adminMgmtOwner());

    $this->actingAs($admin)
        ->get(route('dashboard', ['lang' => 'ar']))
        ->assertOk()
        ->assertSee('dir="rtl"', false)
        ->assertSee(trans('admin.dashboard.title', [], 'ar'))
        ->assertSee(trans('admin.dashboard.filters.search', [], 'ar'));
});

test('owner without restaurants sees an empty state instead of a forbidden redirect', function () {
    $owner = adminMgmtOwner();

    $this->actingAs($owner)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('data-owner-empty-state', false)
        ->assertSee(__('admin.owner_empty.title'));
});

test('owner dashboard eager loads option groups and options', function () {
    $owner = adminMgmtOwner();
    adminMgmtRestaurantWithMenu($owner);

    $response = $this->actingAs($owner)->get(route('dashboard'))->assertOk();
    $item = $response->viewData('categories')->first()->menuItems->first();

    expect($item->relationLoaded('optionGroups'))->toBeTrue()
        ->and($item->optionGroups->first()->relationLoaded('options'))->toBeTrue();
});

test('english and arabic admin translations have the same keys', function () {
    $en = array_keys(Arr::dot(require lang_path('en/admin.php')));
    $ar = array_keys(Arr::dot(require lang_path('ar/admin.php')));

    expect(array_values(array_diff($en, $ar)))->toBe([])
        ->and(array_values(array_diff($ar, $en)))->toBe([]);
});