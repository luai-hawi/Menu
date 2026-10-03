<?php

use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\VideoService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

function bilingualOwner(): array
{
    $owner = User::factory()->create(['role' => 'restaurant_owner']);
    $restaurant = Restaurant::create([
        'name' => 'مطعم الاختبار', 'name_en' => 'Test kitchen',
        'description' => 'طعام عربي', 'description_en' => 'Fresh food',
        'slug' => 'kitchen-'.uniqid(), 'user_id' => $owner->id,
        'is_active' => true, 'whatsapp_orders_enabled' => true, 'whatsapp_number' => '+970599123456',
    ]);
    $category = $restaurant->menuCategories()->create(['name' => 'الأطباق', 'name_en' => 'Meals', 'is_active' => true]);
    $item = $category->menuItems()->create([
        'name' => 'طبق عربي', 'name_en' => 'House plate', 'description' => 'وصف عربي',
        'description_en' => 'House recipe', 'price' => 10, 'is_active' => true,
    ]);

    return [$owner, $restaurant, $category, $item];
}

function bilingualGroups(): array
{
    return [[
        'group_type' => 'SINGLE', 'group_name_ar' => 'الحجم', 'group_name_en' => 'Size',
        'is_required' => 1, 'position' => 0,
        'options' => [
            ['option_name_ar' => 'كبير', 'option_name_en' => 'Large', 'option_note_ar' => 'للمشاركة',
                'option_note_en' => 'For sharing', 'price_delta' => 2.5, 'position' => 0, 'is_active' => 1],
            ['option_name_ar' => 'صغير', 'option_name_en' => 'Small', 'price_delta' => -1, 'position' => 1, 'is_active' => 1],
        ],
    ]];
}

test('owner stores and edits every bilingual item and option field without changing prices or order', function () {
    [$owner, $restaurant, $category] = bilingualOwner();
    $this->actingAs($owner)->postJson(route('item.store'), [
        'name' => 'بيتزا', 'name_en' => 'Pizza', 'description' => 'طازجة', 'description_en' => 'Fresh pizza',
        'price' => 12, 'category_id' => $category->id, 'option_groups' => bilingualGroups(),
    ])->assertOk();
    $item = MenuItem::where('name_en', 'Pizza')->firstOrFail();
    expect($item->nameFor('en'))->toBe('Pizza')
        ->and($item->nameFor('ar'))->toBe('بيتزا')
        ->and($item->descriptionFor('en'))->toBe('Fresh pizza')
        ->and($item->optionGroups->first()->nameFor('en'))->toBe('Size')
        ->and($item->optionGroups->first()->options->first()->noteFor('en'))->toBe('For sharing')
        ->and($item->optionGroups->first()->options->pluck('price_delta')->all())->toBe(['2.50', '-1.00']);

    $groups = $item->optionGroups->map(fn ($group) => $group->toArray())->all();
    $groups[0]['group_name_en'] = 'Portion';
    $groups[0]['options'][0]['option_name_en'] = 'Family';
    $groups[0]['options'][0]['option_note_en'] = 'Share this';
    $this->actingAs($owner)->putJson(route('item.update', $item), [
        'name' => 'بيتزا', 'name_en' => 'Pizza special', 'description' => 'طازجة',
        'description_en' => 'New recipe', 'price' => 12, 'is_active' => 0, 'option_groups' => $groups,
    ])->assertOk();
    $item->refresh();
    expect($item->name_en)->toBe('Pizza special')
        ->and($item->description_en)->toBe('New recipe')
        ->and($item->is_active)->toBeFalse()
        ->and($item->optionGroups->first()->nameFor('en'))->toBe('Portion')
        ->and($item->optionGroups->first()->options->first()->nameFor('en'))->toBe('Family')
        ->and($item->optionGroups->first()->options->first()->noteFor('en'))->toBe('Share this');
});

test('customer locale translates restaurant category item options cart and WhatsApp data', function () {
    [$owner, $restaurant, $category, $item] = bilingualOwner();
    $group = $item->optionGroups()->create(collect(bilingualGroups()[0])->except('options')->all());
    foreach (bilingualGroups()[0]['options'] as $option) {
        $group->options()->create($option);
    }
    $restaurant->update([
        'welcome_title' => 'أهلاً بكم', 'welcome_title_en' => 'Welcome to our kitchen',
        'welcome_message' => 'تفضلوا قائمتنا', 'welcome_message_en' => 'Explore our menu',
    ]);
    $restaurant->forceFill(['admin_notes' => 'Private billing note'])->save();

    $en = $this->withCookie('app_locale', 'ar')->get('/'.$restaurant->slug.'?lang=en')->assertOk();
    $en->assertSee('lang="en" dir="ltr"', false)
        ->assertSee('Test kitchen')->assertSee('Meals')->assertSee('House plate')->assertSee('House recipe')
        ->assertSee('Welcome to our kitchen')->assertSee('Explore our menu')
        ->assertSee('Size')->assertSee('Large')->assertSee('For sharing')
        ->assertSee('Your Order')->assertSee('Send Order')
        ->assertDontSee('Private billing note')->assertDontSee('admin_notes')
        ->assertSee('name: opt.name', false)->assertSee('group: g.name', false)
        ->assertSee("item.querySelector('.menu-item-title').textContent", false);
    expect($restaurant->toArray())->not->toHaveKey('admin_notes');

    $this->withCookie('app_locale', 'en')->get('/'.$restaurant->slug.'?lang=ar')->assertOk()
        ->assertSee('lang="ar" dir="rtl"', false)->assertSee('مطعم الاختبار')->assertSee('الأطباق')
        ->assertSee('طبق عربي')->assertSee('وصف عربي')->assertSee('أهلاً بكم');
});

test('English customer data falls back to Arabic only for missing English fields', function () {
    [$owner, $restaurant, $category, $item] = bilingualOwner();
    $restaurant->update(['name_en' => null, 'description_en' => null]);
    $category->update(['name_en' => null]);
    $item->update(['name_en' => null, 'description_en' => null]);
    $this->get('/'.$restaurant->slug.'?lang=en')->assertOk()
        ->assertSee('مطعم الاختبار')->assertSee('طعام عربي')->assertSee('الأطباق')
        ->assertSee('طبق عربي')->assertSee('وصف عربي');
});

test('language and option scripts work even when WhatsApp ordering is disabled', function () {
    [$owner, $restaurant] = bilingualOwner();
    $restaurant->update(['whatsapp_orders_enabled' => false]);
    $this->get('/'.$restaurant->slug.'?lang=en')->assertOk()
        ->assertSee('window.menuItemCard = function', false)->assertSee("getElementById('language-select').addEventListener", false)
        ->assertDontSee('id="orderModal"', false)->assertSee('const translations =', false);
});

test('owner category editor saves translations and publishing flags and public menu excludes hidden content', function () {
    [$owner, $restaurant, $category, $item] = bilingualOwner();
    $this->actingAs($owner)->putJson(route('category.update', $category), [
        'name' => 'قسم جديد', 'name_en' => 'New meals', 'is_active' => 0,
    ])->assertOk();
    expect($category->fresh()->name_en)->toBe('New meals')->and($category->fresh()->is_active)->toBeFalse();
    $this->get('/'.$restaurant->slug.'?lang=en')->assertOk()->assertDontSee('House plate')->assertDontSee('New meals');
    $category->refresh()->update(['is_active' => true]);
    $item->update(['is_active' => false]);
    $this->get('/'.$restaurant->slug.'?lang=en')->assertOk()->assertSee('New meals')->assertDontSee('House plate');
});

test('owners cannot create edit or delete another restaurants categories or items', function () {
    [$owner] = bilingualOwner();
    [$otherOwner, $restaurant, $category, $item] = bilingualOwner();
    $this->actingAs($owner)->postJson(route('item.store'), [
        'name' => 'Intruder', 'price' => 1, 'category_id' => $category->id,
    ])->assertUnprocessable()->assertJsonValidationErrors('category_id');
    $this->putJson(route('item.update', $item), ['name' => 'Intruder', 'price' => 1])->assertForbidden();
    $this->deleteJson(route('item.delete', $item))->assertForbidden();
    $this->putJson(route('category.update', $category), ['name' => 'Intruder'])->assertForbidden();
    $this->deleteJson(route('category.delete', $category))->assertForbidden();
    expect($item->fresh()->name)->toBe('طبق عربي')->and($category->fresh()->name)->toBe('الأطباق');
});

test('selected restaurant scopes destructive actions even when an owner owns both restaurants', function () {
    [$owner, $selected] = bilingualOwner();
    $other = Restaurant::create(['name' => 'Other', 'slug' => 'other', 'user_id' => $owner->id]);
    $category = $other->menuCategories()->create(['name' => 'Other category']);
    $item = $category->menuItems()->create(['name' => 'Other item', 'price' => 1]);
    $this->actingAs($owner)->withSession(['selected_restaurant_id' => $selected->id])
        ->deleteJson(route('item.delete', $item))->assertForbidden();
    $this->deleteJson(route('category.delete', $category))->assertForbidden();
});

test('invalid nested option payloads return validation errors instead of server errors', function (array $groups, string $field) {
    [$owner, $restaurant, $category] = bilingualOwner();
    $this->actingAs($owner)->postJson(route('item.store'), [
        'name' => 'Invalid', 'price' => 1, 'category_id' => $category->id, 'option_groups' => $groups,
    ])->assertUnprocessable()->assertJsonValidationErrors($field);
})->with([
    'non-array group' => [['invalid'], 'option_groups.0'],
    'non-array option' => [[['group_type' => 'SINGLE', 'group_name_ar' => 'حجم', 'options' => ['invalid']]], 'option_groups.0.options.0'],
    'nonnumeric price' => [[['group_type' => 'SINGLE', 'group_name_ar' => 'حجم', 'options' => [['option_name_ar' => 'صغير', 'price_delta' => 'bogus']]]], 'option_groups.0.options.0.price_delta'],
    'invalid boolean' => [[['group_type' => 'SINGLE', 'group_name_ar' => 'حجم', 'is_required' => 'bogus', 'options' => [['option_name_ar' => 'صغير']]]], 'option_groups.0.is_required'],
]);

test('option ids from another item cannot be silently copied into an edit', function () {
    [$owner, $restaurant, $category, $item] = bilingualOwner();
    $other = $category->menuItems()->create(['name' => 'Another', 'price' => 5]);
    $foreignGroup = $other->optionGroups()->create(['group_type' => 'SINGLE', 'group_name_ar' => 'الحجم']);
    $foreignOption = $foreignGroup->options()->create(['option_name_ar' => 'صغير']);
    $groups = bilingualGroups();
    $groups[0]['id'] = $foreignGroup->id;
    $groups[0]['options'][0]['id'] = $foreignOption->id;
    $this->actingAs($owner)->putJson(route('item.update', $item), [
        'name' => $item->name, 'price' => 10, 'option_groups' => $groups,
    ])->assertUnprocessable()->assertJsonValidationErrors(['option_groups.0.id', 'option_groups.0.options.0.id']);
    expect($item->fresh()->optionGroups)->toHaveCount(0)->and($foreignOption->fresh())->not->toBeNull();
});

test('restaurant creation ignores owner ids activation flags and theme data from unvalidated input', function () {
    $owner = User::factory()->create(['role' => 'admin']);
    $other = User::factory()->create();
    $this->actingAs($owner)->postJson(route('restaurant.store'), [
        'name' => 'مطعم جديد', 'name_en' => 'New kitchen', 'description_en' => 'English text',
        'user_id' => $other->id, 'is_active' => false, 'theme_colors' => ['page_bg' => 'invalid'],
    ])->assertOk();
    $restaurant = Restaurant::firstOrFail();
    expect($restaurant->user_id)->toBe($owner->id)->and($restaurant->is_active)->toBeTrue()
        ->and($restaurant->name_en)->toBe('New kitchen')->and($restaurant->description_en)->toBe('English text')
        ->and($restaurant->theme_colors)->toBeNull();
});

test('owner profile and welcome settings retain bilingual text and allow clearing social links', function () {
    [$owner, $restaurant] = bilingualOwner();
    $this->actingAs($owner)->postJson(route('restaurant.update.profile'), [
        'name' => 'اسم جديد', 'name_en' => 'Updated kitchen', 'description' => 'وصف جديد', 'description_en' => 'Updated description',
    ])->assertOk();
    $restaurant->update(['facebook_url' => 'https://facebook.com/old']);
    $this->postJson(route('restaurant.update.settings'), [
        'welcome_title' => 'أهلاً', 'welcome_title_en' => 'Welcome', 'welcome_message' => 'تفضلوا', 'welcome_message_en' => 'Come in',
        'facebook_url' => '',
    ])->assertOk();
    $restaurant->refresh();
    expect($restaurant->name_en)->toBe('Updated kitchen')->and($restaurant->description_en)->toBe('Updated description')
        ->and($restaurant->welcomeTitleFor('en'))->toBe('Welcome')->and($restaurant->welcomeMessageFor('en'))->toBe('Come in')
        ->and($restaurant->facebook_url)->toBeNull();
    $this->postJson(route('restaurant.update.settings'), ['page_bg' => '#123456'])->assertOk();
    expect($restaurant->fresh()->welcome_title_en)->toBe('Welcome');
});

test('owner dashboard exposes bilingual editors publishing welcome constraints and processor status', function () {
    [$owner] = bilingualOwner();
    $this->mock(VideoService::class)->shouldReceive('available')->once()->andReturn(false);
    $this->actingAs($owner)->get(route('dashboard').'?lang=en')->assertOk()
        ->assertSee('name="name_en"', false)->assertSee('name="description_en"', false)
        ->assertSee('group_name_en', false)->assertSee('option_name_en', false)->assertSee('option_note_en', false)
        ->assertSee('Published on the menu')->assertSee('Edit category')->assertSee('10 MB and 15 seconds')
        ->assertSee('2 MB')->assertSee('Video processing is unavailable')->assertDontSee('studio.name_en');
});

test('welcome video uploads use the processor and replacement is deleted only after successful save', function () {
    Storage::fake('public');
    [$owner, $restaurant] = bilingualOwner();
    $restaurant->update(['welcome_video' => 'welcome-videos/old.mp4']);
    Storage::disk('public')->put('welcome-videos/old.mp4', 'old');
    Storage::disk('public')->put('welcome-videos/new.mp4', 'new');
    $this->mock(VideoService::class)->shouldReceive('uploadAndCompressVideo')->once()
        ->andReturn('welcome-videos/new.mp4');
    $this->actingAs($owner)->post(route('restaurant.update.settings'), [
        'welcome_video' => UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4'),
    ], ['Accept' => 'application/json'])->assertOk();
    expect($restaurant->fresh()->welcome_video)->toBe('welcome-videos/new.mp4');
    Storage::disk('public')->assertMissing('welcome-videos/old.mp4');
    Storage::disk('public')->assertExists('welcome-videos/new.mp4');
    $this->postJson(route('restaurant.update.settings'), ['remove_welcome_video' => 1])->assertOk();
    expect($restaurant->fresh()->welcome_video)->toBeNull();
    Storage::disk('public')->assertMissing('welcome-videos/new.mp4');
});

test('failed welcome video processing preserves old video and returns field validation error', function () {
    Storage::fake('public');
    [$owner, $restaurant] = bilingualOwner();
    $restaurant->update(['welcome_video' => 'welcome-videos/old.mp4']);
    Storage::disk('public')->put('welcome-videos/old.mp4', 'old');
    $this->mock(VideoService::class)->shouldReceive('uploadAndCompressVideo')->once()
        ->andThrow(ValidationException::withMessages(['welcome_video' => 'Processor unavailable']));
    $this->actingAs($owner)->post(route('restaurant.update.settings'), [
        'welcome_video' => UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4'),
    ], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('welcome_video');
    expect($restaurant->fresh()->welcome_video)->toBe('welcome-videos/old.mp4');
    Storage::disk('public')->assertExists('welcome-videos/old.mp4');
});

test('failed database save cleans up new welcome media and keeps the old file', function () {
    Storage::fake('public');
    [$owner, $restaurant] = bilingualOwner();
    $restaurant->update(['welcome_video' => 'welcome-videos/old.mp4']);
    Storage::disk('public')->put('welcome-videos/old.mp4', 'old');
    Storage::disk('public')->put('welcome-videos/new.mp4', 'new');
    $this->mock(VideoService::class)->shouldReceive('uploadAndCompressVideo')->once()->andReturn('welcome-videos/new.mp4');
    Event::listen('eloquent.updating: '.Restaurant::class, fn () => throw new RuntimeException('Save failed'));
    try {
        $this->withoutExceptionHandling()->actingAs($owner)->post(route('restaurant.update.settings'), [
            'welcome_video' => UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4'),
        ]);
        test()->fail('Expected the save to fail');
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toBe('Save failed');
    } finally {
        Event::forget('eloquent.updating: '.Restaurant::class);
    }
    expect($restaurant->fresh()->welcome_video)->toBe('welcome-videos/old.mp4');
    Storage::disk('public')->assertExists('welcome-videos/old.mp4');
    Storage::disk('public')->assertMissing('welcome-videos/new.mp4');
});

test('conflicting replacement and removal flags reject uploads before processing', function (string $field, string $remove, string $filename, string $mime) {
    [$owner] = bilingualOwner();
    $this->mock(VideoService::class)->shouldNotReceive('uploadAndCompressVideo');
    $route = $field === 'logo' ? 'restaurant.update.profile' : 'restaurant.update.settings';
    $this->actingAs($owner)->post(route($route), [
        'name' => 'Existing', $field => UploadedFile::fake()->create($filename, 10, $mime), $remove => 1,
    ], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors($field);
})->with([
    ['welcome_video', 'remove_welcome_video', 'clip.mp4', 'video/mp4'],
    ['background_image', 'remove_background', 'header.png', 'image/png'],
    ['logo', 'remove_logo', 'logo.png', 'image/png'],
]);

test('customer welcome video is muted skippable reduced-motion aware and never blocks the menu', function () {
    [$owner, $restaurant] = bilingualOwner();
    $restaurant->update(['welcome_video' => 'welcome-videos/clip.mp4', 'welcome_title_en' => 'Hello']);
    $this->get('/'.$restaurant->slug.'?lang=en')->assertOk()
        ->assertSee('muted playsinline controls preload="none"', false)->assertSee('Skip video')->assertSee('Enter menu')
        ->assertSee('prefers-reduced-motion: reduce', false)->assertSee('id="menuContent"', false)->assertSee('House plate')
        ->assertDontSee('autoplay', false);
});
