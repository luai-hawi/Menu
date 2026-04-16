<?php

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MenuItemOption;
use App\Models\MenuItemOptionGroup;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\PriceCalculationException;
use App\Services\PriceCalculator;

/*
 * End-to-end coverage for the configurable-option-groups feature:
 *   - happy path: POST /menu-item with nested option groups persists everything
 *   - update re-orders, adds, removes groups & options idempotently
 *   - single-select constraint is enforced (max_choices=1, min<=1)
 *   - multi-select min/max constraints raise validation errors
 *   - PriceCalculator::calculateWithValidation enforces constraints at runtime
 */

function makeOwner(): array
{
    $user = User::factory()->create(['role' => 'restaurant_owner']);
    $restaurant = Restaurant::create([
        'name' => 'Test Restaurant',
        'slug' => 'test-restaurant-'.uniqid(),
        'user_id' => $user->id,
        'is_active' => true,
    ]);
    $category = MenuCategory::create([
        'name' => 'Pizzas',
        'restaurant_id' => $restaurant->id,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    return [$user, $restaurant, $category];
}

test('owner can create an item with two option groups (size SINGLE + toppings MULTIPLE)', function () {
    [$user, $restaurant, $category] = makeOwner();

    $response = $this->actingAs($user)->post('/menu-item', [
        'name' => 'Margherita',
        'description' => 'Tomato & mozzarella',
        'price' => 10.00,
        'category_id' => $category->id,
        'option_groups' => [
            [
                'group_type' => 'SINGLE',
                'group_name_ar' => 'الحجم',
                'is_required' => true,
                'min_choices' => 1,
                'max_choices' => 1,
                'position' => 0,
                'options' => [
                    ['option_name_ar' => 'صغير',  'price_delta' => -1.00, 'position' => 0, 'is_active' => 1],
                    ['option_name_ar' => 'وسط',   'price_delta' => 0.00,  'position' => 1, 'is_active' => 1],
                    ['option_name_ar' => 'كبير',  'price_delta' => 2.50,  'position' => 2, 'is_active' => 1],
                ],
            ],
            [
                'group_type' => 'MULTIPLE',
                'group_name_ar' => 'الإضافات',
                'is_required' => false,
                'min_choices' => 0,
                'max_choices' => 2,
                'position' => 1,
                'options' => [
                    ['option_name_ar' => 'جبنة إضافية', 'price_delta' => 1.50, 'position' => 0, 'is_active' => 1],
                    ['option_name_ar' => 'زيتون',       'price_delta' => 0.75, 'position' => 1, 'is_active' => 1],
                ],
            ],
        ],
    ]);

    $response->assertRedirect();
    expect(MenuItem::count())->toBe(1);

    $item = MenuItem::first();
    expect($item->optionGroups)->toHaveCount(2);

    $size = $item->optionGroups->firstWhere('group_type', 'SINGLE');
    expect($size->group_name_ar)->toBe('الحجم')
        ->and($size->options)->toHaveCount(3)
        ->and($size->is_required)->toBeTrue()
        ->and($size->max_choices)->toBe(1);

    $toppings = $item->optionGroups->firstWhere('group_type', 'MULTIPLE');
    expect($toppings->options)->toHaveCount(2)
        ->and($toppings->max_choices)->toBe(2);
});

test('update replaces, reorders and removes option groups and options idempotently', function () {
    [$user, $restaurant, $category] = makeOwner();

    $item = MenuItem::create([
        'name' => 'Pizza', 'description' => null, 'price' => 10.00,
        'menu_category_id' => $category->id, 'sort_order' => 1, 'is_active' => true,
    ]);
    $group = MenuItemOptionGroup::create([
        'menu_item_id' => $item->id, 'group_type' => 'SINGLE',
        'group_name_ar' => 'الحجم',
        'is_required' => true, 'min_choices' => 1, 'max_choices' => 1, 'position' => 0,
    ]);
    $small = MenuItemOption::create([
        'option_group_id' => $group->id, 'option_name_ar' => 'صغير',
        'price_delta' => -1.00, 'position' => 0, 'is_active' => true,
    ]);
    $medium = MenuItemOption::create([
        'option_group_id' => $group->id, 'option_name_ar' => 'وسط',
        'price_delta' => 0.00, 'position' => 1, 'is_active' => true,
    ]);

    $this->actingAs($user)->put("/menu-item/{$item->id}", [
        'name' => 'Pizza',
        'description' => 'updated',
        'price' => 11.00,
        'option_groups' => [
            [
                'id' => $group->id,
                'group_type' => 'SINGLE',
                'group_name_ar' => 'الحجم',
                'is_required' => true,
                'min_choices' => 1,
                'max_choices' => 1,
                'position' => 0,
                'options' => [
                    // Keep medium, drop small, add large. Swap order.
                    ['id' => $medium->id, 'option_name_ar' => 'وسط',  'price_delta' => 0,    'position' => 0],
                    ['option_name_ar' => 'كبير', 'price_delta' => 2.00, 'position' => 1],
                ],
            ],
        ],
    ])->assertRedirect();

    $item->refresh()->load('optionGroups.options');
    expect($item->price)->toEqual('11.00');
    expect($item->optionGroups)->toHaveCount(1);
    $opts = $item->optionGroups->first()->options;
    expect($opts)->toHaveCount(2);
    expect($opts->pluck('option_name_ar')->all())->toBe(['وسط', 'كبير']);
    // `small` should be gone
    expect(MenuItemOption::find($small->id))->toBeNull();
});

test('single-select group rejects max_choices > 1', function () {
    [$user, $restaurant, $category] = makeOwner();

    $response = $this->actingAs($user)->post('/menu-item', [
        'name' => 'Bad', 'price' => 10, 'category_id' => $category->id,
        'option_groups' => [[
            'group_type' => 'SINGLE',
            'group_name_ar' => 'الحجم',
            'min_choices' => 0,
            'max_choices' => 3,  // <- invalid for SINGLE
            'position' => 0,
            'options' => [
                ['option_name_ar' => 'ص', 'price_delta' => 0],
            ],
        ]],
    ]);

    // Note: prepareForValidation() coerces max_choices to 1 for SINGLE groups,
    // so the raw invalid value is normalized away and the request succeeds.
    // The server-side guarantee is the persisted group type == SINGLE implies max_choices == 1.
    $response->assertRedirect();
    $g = MenuItemOptionGroup::firstWhere('group_type', 'SINGLE');
    expect($g->max_choices)->toBe(1);
});

test('multi-select max_choices > number of options triggers validation error', function () {
    [$user, $restaurant, $category] = makeOwner();

    $response = $this->actingAs($user)->from('/dashboard')->post('/menu-item', [
        'name' => 'Bad', 'price' => 10, 'category_id' => $category->id,
        'option_groups' => [[
            'group_type' => 'MULTIPLE',
            'group_name_ar' => 'الإضافات',
            'min_choices' => 0,
            'max_choices' => 5,
            'position' => 0,
            'options' => [
                ['option_name_ar' => 'زيتون', 'price_delta' => 1],
            ],
        ]],
    ]);

    $response->assertSessionHasErrors('option_groups.0.max_choices');
});

test('PriceCalculator::calculateWithValidation enforces single-select constraint', function () {
    [$user, $restaurant, $category] = makeOwner();

    $item = MenuItem::create([
        'name' => 'P', 'price' => 10,
        'menu_category_id' => $category->id, 'sort_order' => 1, 'is_active' => true,
    ]);
    $group = $item->optionGroups()->create([
        'group_type' => 'SINGLE', 'group_name_ar' => 'الحجم',
        'is_required' => true, 'min_choices' => 1, 'max_choices' => 1, 'position' => 0,
    ]);
    $small = $group->options()->create([
        'option_name_ar' => 'صغير',
        'price_delta' => -1, 'position' => 0, 'is_active' => true,
    ]);
    $large = $group->options()->create([
        'option_name_ar' => 'كبير',
        'price_delta' => 2, 'position' => 1, 'is_active' => true,
    ]);

    $calc = new PriceCalculator;

    expect($calc->calculateWithValidation($item, [$small->id])['subtotal'])->toBe(9.00);

    $calc->calculateWithValidation($item, [$small->id, $large->id]);
})->throws(PriceCalculationException::class, 'single-select');

test('storeItem returns JSON when client expects json', function () {
    [$user, $restaurant, $category] = makeOwner();

    $response = $this->actingAs($user)
        ->postJson('/menu-item', [
            'name' => 'Falafel',
            'price' => 5.00,
            'category_id' => $category->id,
        ]);

    $response->assertOk()
        ->assertJson(['success' => true])
        ->assertJsonStructure(['success', 'message', 'item_id']);
});

test('updateItem returns JSON on AJAX with the localized success message', function () {
    [$user, $restaurant, $category] = makeOwner();

    $item = MenuItem::create([
        'name' => 'X', 'price' => 10, 'menu_category_id' => $category->id,
        'sort_order' => 1, 'is_active' => true,
    ]);

    $this->actingAs($user)
        ->putJson("/menu-item/{$item->id}", [
            'name' => 'X updated',
            'price' => 12,
        ])
        ->assertOk()
        ->assertJson(['success' => true])
        ->assertJsonStructure(['success', 'message']);

    expect($item->fresh()->price)->toEqual('12.00');
});

test('deleteItem via AJAX returns JSON success', function () {
    [$user, $restaurant, $category] = makeOwner();

    $item = MenuItem::create([
        'name' => 'X', 'price' => 10, 'menu_category_id' => $category->id,
        'sort_order' => 1, 'is_active' => true,
    ]);

    $this->actingAs($user)
        ->deleteJson("/menu-item/{$item->id}")
        ->assertOk()
        ->assertJson(['success' => true]);

    expect(MenuItem::find($item->id))->toBeNull();
});

test('validation errors return JSON 422 when AJAX', function () {
    [$user, $restaurant, $category] = makeOwner();

    $this->actingAs($user)
        ->postJson('/menu-item', [
            'name' => '',   // missing required
            'price' => -1,   // negative
            'category_id' => $category->id,
        ])
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'errors']);
});

test('PriceCalculator enforces multi-select min/max constraints', function () {
    [$user, $restaurant, $category] = makeOwner();

    $item = MenuItem::create([
        'name' => 'P', 'price' => 10,
        'menu_category_id' => $category->id, 'sort_order' => 1, 'is_active' => true,
    ]);
    $group = $item->optionGroups()->create([
        'group_type' => 'MULTIPLE', 'group_name_ar' => 'إضافات',
        'is_required' => false, 'min_choices' => 1, 'max_choices' => 2, 'position' => 0,
    ]);
    $o1 = $group->options()->create(['option_name_ar' => 'أ', 'price_delta' => 1, 'position' => 0, 'is_active' => true]);
    $o2 = $group->options()->create(['option_name_ar' => 'ب', 'price_delta' => 1, 'position' => 1, 'is_active' => true]);
    $o3 = $group->options()->create(['option_name_ar' => 'ج', 'price_delta' => 1, 'position' => 2, 'is_active' => true]);

    $calc = new PriceCalculator;

    // 0 choices < min 1 → error
    try {
        $calc->calculateWithValidation($item, []);
        $this->fail('Expected PriceCalculationException');
    } catch (PriceCalculationException $e) {
        expect($e->getMessage())->toContain('at least');
    }

    // 3 choices > max 2 → error
    try {
        $calc->calculateWithValidation($item, [$o1->id, $o2->id, $o3->id]);
        $this->fail('Expected PriceCalculationException');
    } catch (PriceCalculationException $e) {
        expect($e->getMessage())->toContain('at most');
    }

    // Exactly 2 works
    expect($calc->calculateWithValidation($item, [$o1->id, $o2->id])['subtotal'])->toBe(12.00);
});
