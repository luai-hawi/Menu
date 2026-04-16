<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenuItemRequest;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MenuItemOptionGroup;
use App\Models\Restaurant;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RestaurantController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
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
        $restaurants = $user->restaurants;

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
        $restaurants = $user->restaurants;

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

        return view('restaurant.dashboard', compact('restaurant', 'categories', 'restaurants'));
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
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $slug = Str::slug($request->name);
        $counter = 1;
        $originalSlug = $slug;
        while (Restaurant::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        $restaurant = new Restaurant($request->all());
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

        $restaurant->save();

        return $this->ok($request, __('messages.products.flash_saved'));
    }

    /* =================================================================
     * Categories
     * ================================================================= */

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $restaurant = $this->getSelectedRestaurant();
        if (! $restaurant) {
            return $this->fail($request, __('messages.errors.restaurant_not_found'), 404);
        }

        $category = MenuCategory::create([
            'name' => $request->name,
            'restaurant_id' => $restaurant->id,
            'sort_order' => MenuCategory::where('restaurant_id', $restaurant->id)->max('sort_order') + 1,
            'is_active' => true,
        ]);

        return $this->ok($request, __('messages.products.flash_category_created'), [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
            ],
        ]);
    }

    public function deleteCategory(Request $request, MenuCategory $category)
    {
        foreach ($category->menuItems as $item) {
            if ($item->image) {
                $this->imageService->deleteImage($item->image);
            }
        }

        $category->delete();

        return $this->ok($request, __('messages.products.flash_category_deleted'));
    }

    /* =================================================================
     * Menu items (with nested option groups)
     * ================================================================= */

    public function storeItem(MenuItemRequest $request)
    {
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->imageService->uploadAndCompressImage(
                $request->file('image'),
                'menu-items',
                600,
                80
            );
        }

        $item = DB::transaction(function () use ($request, $imagePath) {
            $item = MenuItem::create([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'price' => $request->input('price'),
                'image' => $imagePath,
                'menu_category_id' => $request->input('category_id'),
                'sort_order' => (int) MenuItem::where('menu_category_id', $request->input('category_id'))
                    ->max('sort_order') + 1,
                'is_active' => true,
            ]);

            $this->syncOptionGroups($item, (array) $request->input('option_groups', []));

            return $item;
        });

        return $this->ok($request, __('messages.products.flash_created'), [
            'item_id' => $item->id,
        ]);
    }

    public function updateItem(MenuItemRequest $request, MenuItem $item)
    {
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

        DB::transaction(function () use ($request, $item, $oldImage, $newImagePath) {
            $item->fill([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'price' => $request->input('price'),
            ]);

            if ($newImagePath !== null) {
                if ($oldImage) {
                    $this->imageService->deleteImage($oldImage);
                }
                $item->image = $newImagePath;
            }

            $item->save();

            $this->syncOptionGroups($item, (array) $request->input('option_groups', []));
        });

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
                    'price_delta' => (float) ($opt['price_delta'] ?? 0),
                    'option_note_ar' => $opt['option_note_ar'] ?? null,
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
        if ($item->image) {
            $this->imageService->deleteImage($item->image);
        }

        $item->delete();

        return $this->ok($request, __('messages.products.flash_deleted'));
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
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_logo' => 'nullable|in:1',
        ]);

        $restaurant = $this->getSelectedRestaurant();
        if (! $restaurant) {
            return $this->fail($request, __('messages.errors.restaurant_not_found'), 404);
        }

        $updateData = [
            'name' => $request->name,
            'description' => $request->description,
        ];

        if ($request->hasFile('logo')) {
            if ($restaurant->logo) {
                $this->imageService->deleteImage($restaurant->logo);
            }
            $updateData['logo'] = $this->imageService->uploadAndCompressImage(
                $request->file('logo'),
                'logos',
                400,
                85
            );
        } elseif ($request->has('remove_logo') && $request->remove_logo == '1') {
            if ($restaurant->logo) {
                $this->imageService->deleteImage($restaurant->logo);
            }
            $updateData['logo'] = null;
        }

        $restaurant->update($updateData);

        return $this->ok($request, __('messages.products.flash_saved'), [
            'logo_url' => $restaurant->logo ? asset('storage/'.$restaurant->logo) : null,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'remove_background' => 'nullable|in:1',
            'facebook_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'snapchat_url' => 'nullable|url',
            'whatsapp_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'tiktok_url' => 'nullable|url',
            'primary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'accent_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'text_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'background_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'card_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_bg' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'tertiary_bg' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_text' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'muted_text' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'input_bg' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'input_border' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $restaurant = $this->getSelectedRestaurant();
        if (! $restaurant) {
            return $this->fail($request, __('messages.errors.restaurant_not_found'), 404);
        }

        // Merge: only overwrite fields the form actually submitted so a form
        // that only updates social links doesn't wipe the theme, and vice versa.
        $existingColors = $restaurant->theme_colors ?: [];
        $mergedColors = array_merge($existingColors, array_filter([
            'primary' => $request->primary_color,
            'secondary' => $request->secondary_color,
            'accent' => $request->accent_color,
            'text' => $request->text_color,
            'background' => $request->background_color,
            'card' => $request->card_color,
            'secondary_bg' => $request->secondary_bg,
            'tertiary_bg' => $request->tertiary_bg,
            'secondary_text' => $request->secondary_text,
            'muted_text' => $request->muted_text,
            'input_bg' => $request->input_bg,
            'input_border' => $request->input_border,
        ], fn ($v) => $v !== null && $v !== ''));

        $updateData = [
            'facebook_url' => $request->facebook_url ?? $restaurant->facebook_url,
            'instagram_url' => $request->instagram_url ?? $restaurant->instagram_url,
            'snapchat_url' => $request->snapchat_url ?? $restaurant->snapchat_url,
            'whatsapp_url' => $request->whatsapp_url ?? $restaurant->whatsapp_url,
            'twitter_url' => $request->twitter_url ?? $restaurant->twitter_url,
            'tiktok_url' => $request->tiktok_url ?? $restaurant->tiktok_url,
            'theme_colors' => $mergedColors,
        ];

        if ($request->hasFile('background_image')) {
            if ($restaurant->background_image) {
                $this->imageService->deleteImage($restaurant->background_image);
            }
            $updateData['background_image'] = $this->imageService->uploadAndCompressImage(
                $request->file('background_image'),
                'backgrounds',
                1920,
                80
            );
        } elseif ($request->has('remove_background') && $request->remove_background == '1') {
            if ($restaurant->background_image) {
                $this->imageService->deleteImage($restaurant->background_image);
            }
            $updateData['background_image'] = null;
        }

        $restaurant->update($updateData);

        return $this->ok($request, __('messages.products.flash_saved'), [
            'background_url' => $restaurant->background_image ? asset('storage/'.$restaurant->background_image) : null,
        ]);
    }
}
