<?php

use App\Models\MenuCategory;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\Language;
use App\Services\VideoService;

function languageRestaurant(?string $defaultLanguage = null): Restaurant
{
    $owner = User::factory()->create(['role' => 'restaurant_owner']);
    $restaurant = Restaurant::create([
        'name' => 'مطعم اللغة', 'name_en' => 'Language kitchen',
        'slug' => 'lang-'.uniqid(), 'user_id' => $owner->id,
        'is_active' => true, 'default_language' => $defaultLanguage,
    ]);
    $category = MenuCategory::create([
        'name' => 'الأطباق', 'name_en' => 'Meals', 'restaurant_id' => $restaurant->id, 'is_active' => true,
    ]);
    $category->menuItems()->create([
        'name' => 'طبق', 'name_en' => 'Plate', 'price' => 12, 'is_active' => true,
    ]);

    return $restaurant->refresh();
}

test('language service validates locales and resolves them in priority order', function () {
    $language = app(Language::class);

    expect($language->supported())->toBe(['ar', 'en'])
        ->and($language->isSupported('ar'))->toBeTrue()
        ->and($language->isSupported('en'))->toBeTrue()
        ->and($language->isSupported('fr'))->toBeFalse()
        ->and($language->isSupported(null))->toBeFalse()
        ->and($language->resolve('en', 'ar'))->toBe('en')
        ->and($language->resolve(null, 'ar'))->toBe('ar')
        ->and($language->resolve('fr', 'ar'))->toBe('ar')
        ->and($language->resolve('fr', 'de'))->toBe(config('app.locale'))
        ->and($language->direction('ar'))->toBe('rtl')
        ->and($language->direction('en'))->toBe('ltr');
});

test('restaurant default language falls back to the app locale when unset or invalid', function () {
    $unset = languageRestaurant(null);
    expect($unset->defaultLanguage())->toBe(config('app.locale'))
        ->and($unset->default_language)->toBeNull();

    expect(languageRestaurant('ar')->defaultLanguage())->toBe('ar');
    expect(languageRestaurant('en')->defaultLanguage())->toBe('en');
    expect(languageRestaurant('fr')->defaultLanguage())->toBe(config('app.locale'));
});

test('first time customers get the default language the owner chose', function () {
    $arabic = languageRestaurant('ar');
    $english = languageRestaurant('en');

    // No ?lang= and no app_locale cookie: a genuine first visit.
    $this->get('/'.$arabic->slug)->assertOk()
        ->assertSee('lang="ar"', false)->assertSee('dir="rtl"', false);
    $this->get('/'.$english->slug)->assertOk()
        ->assertSee('lang="en"', false)->assertSee('dir="ltr"', false);
});

test('an explicit language query outranks the owners default', function () {
    $restaurant = languageRestaurant('ar');

    $this->get('/'.$restaurant->slug.'?lang=en')->assertOk()
        ->assertSee('lang="en"', false)->assertSee('dir="ltr"', false);
    $this->get('/'.$restaurant->slug.'?lang=ar')->assertOk()
        ->assertSee('lang="ar"', false);
});

test('a saved customer preference outranks the owners default', function () {
    $restaurant = languageRestaurant('ar');

    $this->withUnencryptedCookie('app_locale', 'en')->get('/'.$restaurant->slug)->assertOk()
        ->assertSee('lang="en"', false)->assertSee('dir="ltr"', false);
});

test('unsupported language input still falls back to the owners default', function () {
    $restaurant = languageRestaurant('ar');

    $this->get('/'.$restaurant->slug.'?lang=fr')->assertOk()
        ->assertSee('lang="ar"', false);
    $this->withUnencryptedCookie('app_locale', 'bogus')->get('/'.$restaurant->slug)->assertOk()
        ->assertSee('lang="ar"', false);
});

test('the owners default only affects first visits and never the dashboard language', function () {
    $restaurant = languageRestaurant('ar');
    $this->mock(VideoService::class)->shouldReceive('available')->andReturn(false);

    // The dashboard keeps following the visitor's own preference / app locale.
    $this->actingAs($restaurant->user)->get(route('dashboard'))->assertOk()
        ->assertSee('lang="'.config('app.locale').'"', false);
    $this->actingAs($restaurant->user)->get(route('dashboard').'?lang=en')->assertOk()
        ->assertSee('lang="en"', false);
});

test('owner saves the default menu language from the dashboard', function () {
    $restaurant = languageRestaurant(null);
    $owner = $restaurant->user;

    $this->actingAs($owner)->postJson(route('restaurant.update.settings'), ['default_language' => 'ar'])
        ->assertOk();
    expect($restaurant->fresh()->default_language)->toBe('ar');

    $this->postJson(route('restaurant.update.settings'), ['default_language' => 'en'])->assertOk();
    expect($restaurant->fresh()->default_language)->toBe('en');
});

test('unsupported default languages are rejected', function () {
    $restaurant = languageRestaurant(null);
    $owner = $restaurant->user;

    $this->actingAs($owner)->postJson(route('restaurant.update.settings'), ['default_language' => 'fr'])
        ->assertUnprocessable()->assertJsonValidationErrors('default_language');

    expect($restaurant->fresh()->default_language)->toBeNull();
});

test('owner dashboard offers the default language selector with a preview', function () {
    $restaurant = languageRestaurant('ar');
    $this->mock(VideoService::class)->shouldReceive('available')->andReturn(false);

    $this->actingAs($restaurant->user)->get(route('dashboard'))->assertOk()
        ->assertSee('name="default_language"', false)
        ->assertSee('function languagePicker(', false)
        ->assertSee('<option value="ar">', false)
        ->assertSee('value="en"', false)
        ->assertSee('Default Menu Language');

    $this->actingAs($restaurant->user)->get(route('dashboard').'?lang=ar')->assertOk()
        ->assertSee('لغة القائمة الافتراضية');
});

test('the public menu language selector reflects the resolved first visit locale', function () {
    $restaurant = languageRestaurant('ar');

    $this->get('/'.$restaurant->slug)->assertOk()
        ->assertSee('<option value="ar" selected>', false)
        ->assertSee('<option value="en" >', false);
});
