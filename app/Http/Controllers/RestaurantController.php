<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenuItemRequest;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MenuItemOptionGroup;
use App\Models\Restaurant;
use App\Services\Currency;
use App\Services\ImageService;
use App\Services\VideoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RestaurantController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService, protected VideoService $videoService)
    {
        $this->imageService = $imageService;
    }

    /* =================================================================
     * AJAX-friendly response helpers
     * ================================================================= */

    /**
     * Return JSON when the client expects it, otherwise fall back to a
     * classic Laravel redirect with a flash message. This lets every save
     * endpoint be reused by both a JS-driven fetch() call and a no-JS
     * form POST.
     */
    protected function ok(Request $request, string $message, array $extra = [], ?string $fallbackRoute = 'restaurant.dashboard'): JsonResponse|RedirectResponse
    {
        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ] + $extra);
        }

        return redirect()->route($fallbackRoute)->with('success', $message);
    }

    protected function fail(Request $request, string $message, int $status = 422, array $extra = [], ?string $fallbackRoute = 'restaurant.dashboard'): JsonResponse|RedirectResponse
    {
        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ] + $extra, $status);
        }

        return redirect()->route($fallbackRoute)->with('error', $message);
    }

    protected function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax();
    }

    /* =================================================================
     * Dashboard + restaurant switching
     * ================================================================= */

    protected function getSelectedRestaurant(): ?Restaurant
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }
        $restaurants = $user->restaurants()->get();

        if ($restaurants->isEmpty()) {
            return null;
        }

        $selectedRestaurantId = session('selected_restaurant_id', $restaurants->first()->id);
        $restaurant = $restaurants->find($selectedRestaurantId);

        return $restaurant ?: $restaurants->first();
    }

    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $restaurants = $user->restaurants()->get();

        if ($restaurants->isEmpty()) {
            return redirect()->route('restaurant.create');
        }

        $selectedRestaurantId = session('selected_restaurant_id', $restaurants->first()->id);
        $restaurant = $restaurants->find($selectedRestaurantId);

        if (! $restaurant) {
            $restaurant = $restaurants->first();
            session(['selected_restaurant_id' => $restaurant->id]);
        }

        $categories = $restaurant->menuCategories()
            ->with(['menuItems.optionGroups.options'])
            ->get();

        $videoAvailable = $this->videoService->available();

        return view('restaurant.dashboard', array_merge(
            compact('restaurant', 'categories', 'restaurants', 'videoAvailable'),
            ['currencies' => app(Currency::class)->options()],
        ));
    }

    public function create()
    {
        return view('restaurant.create');
    }

    public function selectRestaurant(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
        ]);

        $selectedRestaurant = Restaurant::find($request->restaurant_id);

        if (! $selectedRestaurant || $selectedRestaurant->user_id !== auth()->id()) {
            return $this->fail($request, __('messages.errors.unauthorized_restaurant'), 403);
        }

        session(['selected_restaurant_id' => $selectedRestaurant->id]);

        return $this->ok($request, __('messages.products.flash_saved'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'description_en' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $slug = Str::slug($request->input('name_en') ?: $request->name) ?: 'restaurant';
        $counter = 1;
        $originalSlug = $slug;
        while (Restaurant::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        $restaurant = new Restaurant(collect($validated)->except('logo')->all());
        $restaurant->slug = $slug;
        $restaurant->user_id = auth()->id();

        if ($request->hasFile('logo')) {
            $restaurant->logo = $this->imageService->uploadAndCompressImage(
                $request->file('logo'),
                'logos',
                400,
                85
            );
        }

        try {
            $restaurant->save();
        } catch (\Throwable $exception) {
            if ($restaurant->logo) {
                Storage::disk('public')->delete($restaurant->logo);
            }
            throw $exception;
        }

        return $this->ok($request, __('messages.products.flash_saved'));
    }

    /* =================================================================
     * Categories
     * ================================================================= */

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $restaurant = $this->getSelectedRestaurant();
        if (! $restaurant) {
            return $this->fail($request, __('messages.errors.restaurant_not_found'), 404);
        }

        $category = MenuCategory::create([
            'name' => $request->name,
            'name_en' => $request->input('name_en'),
            'restaurant_id' => $restaurant->id,
            'sort_order' => MenuCategory::where('restaurant_id', $restaurant->id)->max('sort_order') + 1,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->ok($request, __('messages.products.flash_category_created'), [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'name_en' => $category->name_en,
                'is_active' => $category->is_active,
            ],
        ]);
    }

    public function deleteCategory(Request $request, MenuCategory $category)
    {
        if (! $this->ownsCategory($category)) {
            return $this->fail($request, __('messages.errors.unauthorized_restaurant'), 403);
        }

        $images = $category->menuItems()->pluck('image')->filter();
        $category->delete();
        foreach ($images as $image) {
            $this->imageService->deleteImage($image);
        }

        return $this->ok($request, __('messages.products.flash_category_deleted'));
    }

    public function updateCategory(Request $request, MenuCategory $category)
    {
        if (! $this->ownsCategory($category)) {
            return $this->fail($request, __('messages.errors.unauthorized_restaurant'), 403);
        }
        $category->update($request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]));

        return $this->ok($request, __('messages.products.flash_saved'));
    }

    protected function ownsCategory(MenuCategory $category): bool
    {
        $restaurant = $this->getSelectedRestaurant();

        return $restaurant && (int) $category->restaurant_id === (int) $restaurant->id;
    }

    /* =================================================================
     * Menu items (with nested option groups)
     * ================================================================= */

    public function storeItem(MenuItemRequest $request)
    {
        $category = MenuCategory::findOrFail($request->input('category_id'));
        if (! $this->ownsCategory($category)) {
            return $this->fail($request, __('messages.errors.unauthorized_restaurant'), 403);
        }
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->imageService->uploadAndCompressImage(
                $request->file('image'),
                'menu-items',
                600,
                80
            );
        }

        try {
            $item = DB::transaction(function () use ($request, $imagePath) {
                $item = MenuItem::create([
                    'name' => $request->input('name'),
                    'name_en' => $request->input('name_en'),
                    'description' => $request->input('description'),
                    'description_en' => $request->input('description_en'),
                    'price' => $request->input('price'),
                    'image' => $imagePath,
                    'menu_category_id' => $request->input('category_id'),
                    'sort_order' => (int) MenuItem::where('menu_category_id', $request->input('category_id'))
                        ->max('sort_order') + 1,
                    'is_active' => $request->boolean('is_active', true),
                ]);

                $this->syncOptionGroups($item, (array) $request->input('option_groups', []));

                return $item;
            });
        } catch (\Throwable $exception) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            throw $exception;
        }

        return $this->ok($request, __('messages.products.flash_created'), [
            'item_id' => $item->id,
        ]);
    }

    public function updateItem(MenuItemRequest $request, MenuItem $item)
    {
        if (! $this->ownsCategory($item->menuCategory)) {
            return $this->fail($request, __('messages.errors.unauthorized_restaurant'), 403);
        }
        $oldImage = $item->image;
        $newImagePath = null;

        if ($request->hasFile('image')) {
            $newImagePath = $this->imageService->uploadAndCompressImage(
                $request->file('image'),
                'menu-items',
                600,
                80
            );
        }

        try {
            DB::transaction(function () use ($request, $item, $newImagePath) {
                $item->fill([
                    'name' => $request->input('name'),
                    'name_en' => $request->input('name_en', $item->name_en),
                    'description' => $request->input('description'),
                    'description_en' => $request->input('description_en', $item->description_en),
                    'price' => $request->input('price'),
                    'is_active' => $request->boolean('is_active', $item->is_active),
                ]);

                if ($newImagePath !== null) {
                    $item->image = $newImagePath;
                }

                $item->save();

                $this->syncOptionGroups($item, (array) $request->input('option_groups', []));
            });
        } catch (\Throwable $exception) {
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }
            throw $exception;
        }
        if ($newImagePath && $oldImage) {
            $this->imageService->deleteImage($oldImage);
        }

        return $this->ok($request, __('messages.products.flash_updated'));
    }

    /**
     * Persist the nested option_groups[] payload against the item:
     *   - deletes groups/options no longer present (cascade via FK removes options)
     *   - upserts remaining groups in order
     *   - upserts each group's options in order
     */
    protected function syncOptionGroups(MenuItem $item, array $groups): void
    {
        $keptGroupIds = [];

        foreach (array_values($groups) as $gIdx => $groupData) {
            $groupId = $groupData['id'] ?? null;

            $payload = [
                'menu_item_id' => $item->id,
                'group_type' => $groupData['group_type'] ?? MenuItemOptionGroup::TYPE_SINGLE,
                'group_name_ar' => $groupData['group_name_ar'] ?? '',
                'group_name_en' => $groupData['group_name_en'] ?? null,
                'min_choices' => (int) ($groupData['min_choices'] ?? 0),
                'max_choices' => (int) ($groupData['max_choices'] ?? 1),
                'is_required' => (bool) ($groupData['is_required'] ?? false),
                'position' => (int) ($groupData['position'] ?? $gIdx),
            ];

            if ($groupId && $existing = $item->optionGroups()->find($groupId)) {
                $existing->update($payload);
                $group = $existing;
            } else {
                $group = $item->optionGroups()->create($payload);
            }

            $keptGroupIds[] = $group->id;

            $keptOptionIds = [];
            foreach (array_values($groupData['options'] ?? []) as $oIdx => $opt) {
                $optPayload = [
                    'option_group_id' => $group->id,
                    'option_name_ar' => $opt['option_name_ar'] ?? '',
                    'option_name_en' => $opt['option_name_en'] ?? null,
                    'price_delta' => (float) ($opt['price_delta'] ?? 0),
                    'option_note_ar' => $opt['option_note_ar'] ?? null,
                    'option_note_en' => $opt['option_note_en'] ?? null,
                    'position' => (int) ($opt['position'] ?? $oIdx),
                    'is_active' => (bool) ($opt['is_active'] ?? true),
                ];

                if (! empty($opt['id']) && $existingOpt = $group->options()->find($opt['id'])) {
                    $existingOpt->update($optPayload);
                    $keptOptionIds[] = $existingOpt->id;
                } else {
                    $keptOptionIds[] = $group->options()->create($optPayload)->id;
                }
            }

            $group->options()->whereNotIn('id', $keptOptionIds)->delete();
        }

        $item->optionGroups()->whereNotIn('id', $keptGroupIds)->delete();
    }

    public function deleteItem(Request $request, MenuItem $item)
    {
        if (! $this->ownsCategory($item->menuCategory)) {
            return $this->fail($request, __('messages.errors.unauthorized_restaurant'), 403);
        }
        $image = $item->image;
        $item->delete();
        if ($image) {
            $this->imageService->deleteImage($image);
        }

        return $this->ok($request, __('messages.products.flash_deleted'));
    }

    /* =================================================================
     * Reordering
     * ================================================================= */

    public function reorderCategories(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|distinct',
        ]);

        $restaurant = $this->getSelectedRestaurant();
        if (! $restaurant) {
            return $this->fail($request, __('messages.errors.restaurant_not_found'), 404);
        }

        $ids = $request->input('order');

        // Ensure every id actually belongs to this restaurant
        $valid = MenuCategory::whereIn('id', $ids)
            ->where('restaurant_id', $restaurant->id)
            ->count();

        if ($valid !== count($ids)) {
            return $this->fail($request, __('messages.errors.unauthorized_restaurant'), 403);
        }

        foreach ($ids as $position => $id) {
            MenuCategory::where('id', $id)->update(['sort_order' => $position]);
        }

        return $this->ok($request, __('messages.products.flash_saved'));
    }

    public function reorderItems(Request $request, MenuCategory $category)
    {
        $restaurant = $this->getSelectedRestaurant();
        if (! $restaurant || $category->restaurant_id !== $restaurant->id) {
            return $this->fail($request, __('messages.errors.unauthorized_restaurant'), 403);
        }

        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|distinct',
        ]);

        $ids = $request->input('order');

        $valid = MenuItem::whereIn('id', $ids)
            ->where('menu_category_id', $category->id)
            ->count();

        if ($valid !== count($ids)) {
            return $this->fail($request, __('messages.errors.unauthorized_restaurant'), 403);
        }

        foreach ($ids as $position => $id) {
            MenuItem::where('id', $id)->update(['sort_order' => $position]);
        }

        return $this->ok($request, __('messages.products.flash_saved'));
    }

    /* =================================================================
     * WhatsApp settings
     * ================================================================= */

    public function toggleWhatsApp(Request $request)
    {
        $restaurant = $this->getSelectedRestaurant();
        if (! $restaurant) {
            return $this->fail($request, __('messages.errors.restaurant_not_found'), 404);
        }

        $restaurant->whatsapp_orders_enabled = ! $restaurant->whatsapp_orders_enabled;
        $restaurant->save();

        return $this->ok($request, __('messages.products.flash_saved'), [
            'whatsapp_orders_enabled' => (bool) $restaurant->whatsapp_orders_enabled,
        ]);
    }

    public function updateWhatsApp(Request $request)
    {
        $request->validate([
            'whatsapp_number' => 'required|string|regex:/^\+?[1-9]\d{1,14}$/',
        ], [
            'whatsapp_number.regex' => __('messages.errors.invalid_whatsapp_number'),
        ]);

        $restaurant = $this->getSelectedRestaurant();
        if (! $restaurant) {
            return $this->fail($request, __('messages.errors.restaurant_not_found'), 404);
        }

        $restaurant->whatsapp_number = $request->whatsapp_number;
        $restaurant->save();

        return $this->ok($request, __('messages.products.flash_saved'));
    }

    /* =================================================================
     * Profile & settings
     * ================================================================= */

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'description_en' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048|prohibited_if:remove_logo,1',
            'remove_logo' => 'nullable|in:1',
        ]);

        $restaurant = $this->getSelectedRestaurant();
        if (! $restaurant) {
            return $this->fail($request, __('messages.errors.restaurant_not_found'), 404);
        }

        $updateData = [
            'name' => $request->name,
            'name_en' => $request->input('name_en', $restaurant->name_en),
            'description' => $request->description,
            'description_en' => $request->input('description_en', $restaurant->description_en),
        ];

        if ($request->hasFile('logo')) {
            $updateData['logo'] = $this->imageService->uploadAndCompressImage(
                $request->file('logo'),
                'logos',
                400,
                85
            );
        } elseif ($request->has('remove_logo') && $request->remove_logo == '1') {
            $updateData['logo'] = null;
        }

        $this->saveWithMedia($restaurant, $updateData);

        return $this->ok($request, __('messages.products.flash_saved'), [
            'logo_url' => $restaurant->logo ? asset('storage/'.$restaurant->logo) : null,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $colorRule = 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/';
        $request->validate([
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120|prohibited_if:remove_background,1',
            'remove_background' => 'nullable|in:1',
            'welcome_title' => 'nullable|string|max:255',
            'welcome_title_en' => 'nullable|string|max:255',
            'welcome_message' => 'nullable|string|max:1000',
            'welcome_message_en' => 'nullable|string|max:1000',
            'welcome_video' => 'nullable|file|mimes:mp4,mov,webm|max:10240|prohibited_if:remove_welcome_video,1',
            'remove_welcome_video' => 'nullable|in:1',
            'facebook_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'snapchat_url' => 'nullable|url',
            'whatsapp_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'tiktok_url' => 'nullable|url',
            'currency' => ['nullable', Rule::in(app(Currency::class)->codes())],
            'currency_position' => ['nullable', Rule::in(['before', 'after'])],
            // ── New comprehensive color tokens ──
            'page_bg' => $colorRule,
            'page_bg_2' => $colorRule,
            'page_bg_3' => $colorRule,
            'header_bg_start' => $colorRule,
            'header_bg_end' => $colorRule,
            'restaurant_name' => $colorRule,
            'restaurant_tagline' => $colorRule,
            'text_primary' => $colorRule,
            'text_secondary' => $colorRule,
            'text_muted' => $colorRule,
            'text_price' => $colorRule,
            'text_option_price' => $colorRule,
            'card_bg' => $colorRule,
            'card_border' => $colorRule,
            'card_border_hover' => $colorRule,
            'card_accent_bar' => $colorRule,
            'card_accent_bar_end' => $colorRule,
            'btn_primary' => $colorRule,
            'btn_primary_end' => $colorRule,
            'btn_qty' => $colorRule,
            'btn_qty_end' => $colorRule,
            'btn_order' => $colorRule,
            'btn_order_end' => $colorRule,
            'pill_bg' => $colorRule,
            'pill_border' => $colorRule,
            'pill_text' => $colorRule,
            'pill_active' => $colorRule,
            'pill_active_end' => $colorRule,
            'pill_active_text' => $colorRule,
            'option_group_bg' => $colorRule,
            'option_selected_bg' => $colorRule,
            'option_input_accent' => $colorRule,
            'input_bg' => $colorRule,
            'input_border' => $colorRule,
            'input_focus' => $colorRule,
            'input_text' => $colorRule,
            'footer_bg' => $colorRule,
            'footer_text' => $colorRule,
            'footer_heading' => $colorRule,
            'border' => $colorRule,
            'border_secondary' => $colorRule,
        ]);

        $restaurant = $this->getSelectedRestaurant();
        if (! $restaurant) {
            return $this->fail($request, __('messages.errors.restaurant_not_found'), 404);
        }

        // Color token keys (match form field names = theme_colors array keys)
        $colorKeys = [
            'page_bg',
            'page_bg_2',
            'page_bg_3',
            'header_bg_start',
            'header_bg_end',
            'restaurant_name',
            'restaurant_tagline',
            'text_primary',
            'text_secondary',
            'text_muted',
            'text_price',
            'text_option_price',
            'card_bg',
            'card_border',
            'card_border_hover',
            'card_accent_bar',
            'card_accent_bar_end',
            'btn_primary',
            'btn_primary_end',
            'btn_qty',
            'btn_qty_end',
            'btn_order',
            'btn_order_end',
            'pill_bg',
            'pill_border',
            'pill_text',
            'pill_active',
            'pill_active_end',
            'pill_active_text',
            'option_group_bg',
            'option_selected_bg',
            'option_input_accent',
            'input_bg',
            'input_border',
            'input_focus',
            'input_text',
            'footer_bg',
            'footer_text',
            'footer_heading',
            'border',
            'border_secondary',
        ];

        $existingColors = $restaurant->theme_colors ?: [];
        $newColors = [];
        foreach ($colorKeys as $key) {
            $val = $request->input($key);
            if ($val !== null && $val !== '') {
                $newColors[$key] = $val;
            }
        }
        $mergedColors = array_merge($existingColors, $newColors);

        $updateData = [
            'theme_colors' => $mergedColors,
        ];
        foreach (['facebook_url', 'instagram_url', 'snapchat_url', 'whatsapp_url', 'twitter_url', 'tiktok_url',
            'welcome_title', 'welcome_title_en', 'welcome_message', 'welcome_message_en',
            'currency', 'currency_position'] as $field) {
            if ($request->exists($field)) {
                $updateData[$field] = $request->input($field);
            }
        }

        // A currency switch without an explicit position should adopt the new
        // currency's own convention rather than inherit the previous one.
        if ($request->exists('currency') && ! $request->exists('currency_position')
            && $request->input('currency') !== $restaurant->currency) {
            $updateData['currency_position'] = app(Currency::class)->position($request->input('currency'));
        }

        // Validate/process video before images so a rejected video cannot orphan an image.
        if ($request->hasFile('welcome_video')) {
            $updateData['welcome_video'] = $this->videoService->uploadAndCompressVideo($request->file('welcome_video'));
        } elseif ($request->boolean('remove_welcome_video')) {
            $updateData['welcome_video'] = null;
        }
        if ($request->hasFile('background_image')) {
            try {
                $updateData['background_image'] = $this->imageService->uploadAndCompressImage(
                    $request->file('background_image'), 'backgrounds', 1920, 80
                );
            } catch (\Throwable $exception) {
                if (! empty($updateData['welcome_video'])) {
                    Storage::disk('public')->delete($updateData['welcome_video']);
                }
                throw $exception;
            }
        } elseif ($request->has('remove_background') && $request->remove_background == '1') {
            $updateData['background_image'] = null;
        }

        $this->saveWithMedia($restaurant, $updateData);

        return $this->ok($request, __('messages.products.flash_saved'), [
            'background_url' => $restaurant->background_image ? asset('storage/'.$restaurant->background_image) : null,
            'welcome_video_url' => $restaurant->welcome_video ? asset('storage/'.$restaurant->welcome_video) : null,
        ]);
    }

    protected function saveWithMedia(Restaurant $restaurant, array $data): void
    {
        $old = [];
        foreach (['logo', 'background_image', 'welcome_video'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== $restaurant->$field) {
                $old[$field] = $restaurant->$field;
            }
        }
        try {
            DB::transaction(fn () => $restaurant->update($data));
        } catch (\Throwable $exception) {
            foreach ($old as $field => $path) {
                if (! empty($data[$field])) {
                    Storage::disk('public')->delete($data[$field]);
                }
            }
            throw $exception;
        }
        foreach ($old as $path) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
