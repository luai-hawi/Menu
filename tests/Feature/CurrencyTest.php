<?php

use App\Models\Restaurant;
use App\Models\User;
use App\Services\Currency;
use App\Services\VideoService;
use Illuminate\Support\Js;

function currencyOwner(): array
{
    $owner = User::factory()->create(['role' => 'restaurant_owner']);
    $restaurant = Restaurant::create([
        'name' => 'مطعم العملة', 'name_en' => 'Currency kitchen',
        'slug' => 'currency-'.uniqid(), 'user_id' => $owner->id,
        'is_active' => true,
    ]);
    $category = $restaurant->menuCategories()->create(['name' => 'الأطباق', 'name_en' => 'Meals', 'is_active' => true]);
    $item = $category->menuItems()->create([
        'name' => 'طبق', 'name_en' => 'Plate', 'price' => 12, 'is_active' => true,
    ]);

    return [$owner, $restaurant, $item];
}

test('currency service resolves symbols positions and formats both sides of the amount', function () {
    $currency = app(Currency::class);

    expect($currency->symbol('USD'))->toBe('$')
        ->and($currency->symbol('AED'))->toBe('د.إ')
        ->and($currency->position('USD'))->toBe('before')
        ->and($currency->position('AED'))->toBe('after')
        ->and($currency->format(12, 'USD'))->toBe('$12.00')
        ->and($currency->format(12, 'AED'))->toBe('12.00 د.إ')
        ->and($currency->format(12, 'USD', 'after'))->toBe('12.00 $')
        ->and($currency->format(12, 'AED', 'before'))->toBe('د.إ12.00')
        ->and($currency->format('12.5', 'USD'))->toBe('$12.50');
});

test('unknown or missing currency codes fall back to the configured default', function () {
    $currency = app(Currency::class);

    expect($currency->isSupported('USD'))->toBeTrue()
        ->and($currency->isSupported('XYZ'))->toBeFalse()
        ->and($currency->isSupported(null))->toBeFalse()
        ->and($currency->symbol('XYZ'))->toBe($currency->symbol(config('currency.default')))
        ->and($currency->symbol(null))->toBe($currency->symbol(config('currency.default')))
        ->and($currency->position('XYZ', 'nonsense'))->toBe('before')
        ->and($currency->name('ZZZ'))->toBe('ZZZ')
        ->and($currency->name('USD'))->toBe('US Dollar');
});

test('currency options expose every configured code with a translated label', function () {
    $options = app(Currency::class)->options();
    $codes = array_keys($options);

    expect($codes)->toBe(array_keys(config('currency.currencies')))
        ->and($codes)->toContain('USD', 'ILS', 'AED')
        ->and($options['USD']['label'])->toBe('USD — US Dollar');

    app()->setLocale('ar');
    expect(app(Currency::class)->options()['USD']['label'])->toBe('USD — دولار أمريكي');

    foreach ($options as $code => $option) {
        expect($option['symbol'])->not->toBeEmpty()
            ->and($option['position'])->toBeIn(['before', 'after'])
            ->and($option['label'])->toStartWith($code);
    }
});

test('restaurant exposes its own currency helpers and falls back to the default', function () {
    [$owner, $restaurant] = currencyOwner();

    expect($restaurant->currencyCode())->toBe('ILS')
        ->and($restaurant->currencySymbol())->toBe('₪')
        ->and($restaurant->currencyPosition())->toBe('before')
        ->and($restaurant->formatPrice(12))->toBe('₪12.00');

    $restaurant->update(['currency' => 'AED', 'currency_position' => 'after']);
    expect($restaurant->fresh()->formatPrice(12))->toBe('12.00 د.إ');

    // An unsupported stored code resolves through the default symbol, keeping the
    // restaurant's own position choice rather than throwing.
    $restaurant->update(['currency' => 'BOGUS', 'currency_position' => 'before']);
    expect($restaurant->fresh()->formatPrice(12))->toBe('₪12.00');
});

test('owner saves the chosen currency and symbol position from the dashboard', function () {
    [$owner, $restaurant] = currencyOwner();

    $this->actingAs($owner)->postJson(route('restaurant.update.settings'), [
        'currency' => 'USD', 'currency_position' => 'after',
    ])->assertOk();

    $restaurant->refresh();
    expect($restaurant->currency)->toBe('USD')->and($restaurant->currency_position)->toBe('after');

    // Position survives a later settings save that does not mention it.
    $this->postJson(route('restaurant.update.settings'), ['facebook_url' => 'https://facebook.com/kitchen'])->assertOk();
    expect($restaurant->fresh()->currency_position)->toBe('after');
});

test('changing currency without a position adopts the new currency default', function () {
    [$owner, $restaurant] = currencyOwner();
    $restaurant->update(['currency' => 'USD', 'currency_position' => 'before']);

    $this->actingAs($owner)->postJson(route('restaurant.update.settings'), ['currency' => 'AED'])->assertOk();
    expect($restaurant->fresh()->currency)->toBe('AED')->and($restaurant->fresh()->currency_position)->toBe('after');

    // Re-saving the same currency keeps the owner's explicit choice.
    $this->postJson(route('restaurant.update.settings'), ['currency' => 'AED', 'currency_position' => 'before'])->assertOk();
    $this->postJson(route('restaurant.update.settings'), ['currency' => 'AED'])->assertOk();
    expect($restaurant->fresh()->currency_position)->toBe('before');
});

test('unsupported currency codes and positions are rejected', function () {
    [$owner, $restaurant] = currencyOwner();

    $this->actingAs($owner)->postJson(route('restaurant.update.settings'), ['currency' => 'XYZ'])
        ->assertUnprocessable()->assertJsonValidationErrors('currency');
    $this->postJson(route('restaurant.update.settings'), ['currency_position' => 'middle'])
        ->assertUnprocessable()->assertJsonValidationErrors('currency_position');

    expect($restaurant->fresh()->currency)->toBe('ILS');
});

test('owner dashboard offers the currency selector with a live preview', function () {
    [$owner] = currencyOwner();
    $this->mock(VideoService::class)->shouldReceive('available')->andReturn(false);

    $this->actingAs($owner)->get(route('dashboard').'?lang=en')->assertOk()
        ->assertSee('name="currency"', false)->assertSee('name="currency_position"', false)
        ->assertSee('function currencyPicker(', false)
        ->assertSee('USD — US Dollar ($)')->assertSee('Save currency');

    $this->actingAs($owner)->get(route('dashboard').'?lang=ar')->assertOk()
        ->assertSee('درهم إماراتي')->assertSee('حفظ العملة');
});

test('public menu shows the owners currency beside prices in both positions', function () {
    [$owner, $restaurant, $item] = currencyOwner();

    $this->get('/'.$restaurant->slug.'?lang=en')->assertOk()
        ->assertSee('₪12.00')
        ->assertSee(Js::from('₪'), false)
        ->assertSee('currencyPosition: '.Js::from('before'), false);

    $restaurant->update(['currency' => 'USD']);
    $this->get('/'.$restaurant->slug.'?lang=en')->assertOk()
        ->assertSee('$12.00')->assertDontSee('₪12.00')
        ->assertSee('currencySymbol: '.Js::from('$'), false);

    $restaurant->update(['currency_position' => 'after']);
    $this->get('/'.$restaurant->slug.'?lang=en')->assertOk()
        ->assertSee('12.00 $')
        ->assertSee('currencyPosition: '.Js::from('after'), false);
});

test('option deltas in the cart and whatsapp message follow the currency position', function () {
    [$owner, $restaurant, $item] = currencyOwner();
    $group = $item->optionGroups()->create([
        'group_type' => 'SINGLE', 'group_name_ar' => 'الحجم', 'group_name_en' => 'Size',
        'is_required' => true, 'position' => 0,
    ]);
    $group->options()->create([
        'option_name_ar' => 'كبير', 'option_name_en' => 'Large', 'price_delta' => 2.5, 'position' => 0,
    ]);
    $restaurant->update(['currency' => 'AED', 'currency_position' => 'after']);

    // Every JS price goes through the shared formatter; no raw symbol concatenation remains.
    $this->get('/'.$restaurant->slug.'?lang=en')->assertOk()
        ->assertSee('function formatPrice(amount)', false)
        ->assertSee('function formatPriceDelta(delta)', false)
        ->assertSee('currencySymbol: '.Js::from('د.إ'), false)
        ->assertSee('currencyPosition: '.Js::from('after'), false)
        ->assertSee('`${number} ${translations.currencySymbol}`', false)
        ->assertSee('`${translations.currencySymbol}${number}`', false)
        ->assertDontSee('${translations.currencySymbol}${item', false)
        ->assertDontSee('${translations.currencySymbol}${grandTotal', false)
        ->assertDontSee('escapeHtml(translations.currencySymbol)', false)
        ->assertSee('formatPrice(computedPrice)', false)
        ->assertSee('formatPrice(item.total)', false)
        ->assertSee('formatPriceDelta(o.delta)', false);

    $restaurant->update(['currency_position' => 'before']);
    $this->get('/'.$restaurant->slug.'?lang=en')->assertOk()
        ->assertSee('currencyPosition: '.Js::from('before'), false);
});

test('dashboard price inputs and item list use the restaurants currency', function () {
    [$owner, $restaurant, $item] = currencyOwner();
    $restaurant->update(['currency' => 'AED', 'currency_position' => 'after']);

    $this->mock(VideoService::class)->shouldReceive('available')->andReturn(false);
    $this->actingAs($owner)->get(route('dashboard'))->assertOk()
        ->assertSee('class="dash-price-hint">د.إ<', false)
        ->assertSee('12.00 د.إ');
});
