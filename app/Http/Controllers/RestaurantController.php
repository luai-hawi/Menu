<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RestaurantController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    protected function getSelectedRestaurant()
    {
        $user = auth()->user();
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

        // Get selected restaurant from session or use first one
        $selectedRestaurantId = session('selected_restaurant_id', $restaurants->first()->id);
        $restaurant = $restaurants->find($selectedRestaurantId);

        // If selected restaurant doesn't exist or doesn't belong to user, use first one
        if (!$restaurant) {
            $restaurant = $restaurants->first();
            session(['selected_restaurant_id' => $restaurant->id]);
        }

        $categories = $restaurant->menuCategories()->with('menuItems')->get();

        return view('restaurant.dashboard', compact('restaurant', 'categories', 'restaurants'));
    }

    public function create()
    {
        return view('restaurant.create');
    }

    public function selectRestaurant(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id'
        ]);

        $selectedRestaurant = Restaurant::find($request->restaurant_id);

        // Ensure the restaurant belongs to the authenticated user
        if (!$selectedRestaurant || $selectedRestaurant->user_id !== auth()->id()) {
            return redirect()->route('restaurant.dashboard')->with('error', 'Unauthorized access to restaurant.');
        }

        session(['selected_restaurant_id' => $selectedRestaurant->id]);

        return redirect()->route('restaurant.dashboard')->with('success', 'Restaurant selected successfully!');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $slug = Str::slug($request->name);
        
        // Ensure unique slug
        $counter = 1;
        $originalSlug = $slug;
        while (Restaurant::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
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

        return redirect()->route('restaurant.dashboard')->with('success', 'Restaurant created successfully!');
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $restaurant = $this->getSelectedRestaurant();

        MenuCategory::create([
            'name' => $request->name,
            'restaurant_id' => $restaurant->id,
            'sort_order' => MenuCategory::where('restaurant_id', $restaurant->id)->max('sort_order') + 1
        ]);

        return redirect()->route('restaurant.dashboard')->with('success', 'Category added successfully!');
    }

    public function storeItem(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:menu_categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $item = new MenuItem($request->except('image'));
        $item->menu_category_id = $request->category_id;
        $item->sort_order = MenuItem::where('menu_category_id', $request->category_id)->max('sort_order') + 1;
        
        if ($request->hasFile('image')) {
            $item->image = $this->imageService->uploadAndCompressImage(
                $request->file('image'), 
                'menu-items', 
                600, 
                80
            );
        }
        
        $item->save();

        return redirect()->route('restaurant.dashboard')->with('success', 'Menu item added successfully!');
    }

    public function updateItem(Request $request, MenuItem $item)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $oldImage = $item->image;
        
        $item->fill($request->except('image'));
        
        if ($request->hasFile('image')) {
            // Delete old image
            if ($oldImage) {
                $this->imageService->deleteImage($oldImage);
            }
            
            // Upload new image
            $item->image = $this->imageService->uploadAndCompressImage(
                $request->file('image'), 
                'menu-items', 
                600, 
                80
            );
        }
        
        $item->save();

        return redirect()->route('restaurant.dashboard')->with('success', 'Menu item updated successfully!');
    }

    public function deleteItem(MenuItem $item)
    {
        // Delete associated image
        if ($item->image) {
            $this->imageService->deleteImage($item->image);
        }
        
        $item->delete();
        
        return redirect()->route('restaurant.dashboard')->with('success', 'Menu item deleted successfully!');
    }

    public function deleteCategory(MenuCategory $category)
    {
        // Delete all associated menu item images
        foreach ($category->menuItems as $item) {
            if ($item->image) {
                $this->imageService->deleteImage($item->image);
            }
        }
        
        $category->delete();
        
        return redirect()->route('restaurant.dashboard')->with('success', 'Category and all items deleted successfully!');
    }

    public function toggleWhatsApp(Request $request)
    {
        $restaurant = $this->getSelectedRestaurant();

        if ($restaurant) {
            $restaurant->whatsapp_orders_enabled = !$restaurant->whatsapp_orders_enabled;
            $restaurant->save();

            $status = $restaurant->whatsapp_orders_enabled ? 'enabled' : 'disabled';
            return redirect()->route('restaurant.dashboard')
                ->with('success', "WhatsApp orders have been {$status}.");
        }

        return redirect()->route('restaurant.dashboard')
            ->with('error', 'Restaurant not found.');
    }

    public function updateWhatsApp(Request $request)
    {
        $request->validate([
            'whatsapp_number' => 'required|string|regex:/^\+?[1-9]\d{1,14}$/'
        ], [
            'whatsapp_number.regex' => 'Please enter a valid WhatsApp number with country code (e.g., +1234567890)'
        ]);

        $restaurant = $this->getSelectedRestaurant();

        if ($restaurant) {
            $restaurant->whatsapp_number = $request->whatsapp_number;
            $restaurant->save();

            return redirect()->route('restaurant.dashboard')
                ->with('success', 'WhatsApp number updated successfully!');
        }

        return redirect()->route('restaurant.dashboard')
            ->with('error', 'Restaurant not found.');
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_logo' => 'nullable|in:1'
        ]);

        $restaurant = $this->getSelectedRestaurant();

        if ($restaurant) {
            $updateData = [
                'name' => $request->name,
                'description' => $request->description,
            ];

            // Handle logo upload/removal
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($restaurant->logo) {
                    $this->imageService->deleteImage($restaurant->logo);
                }

                // Upload new logo
                $updateData['logo'] = $this->imageService->uploadAndCompressImage(
                    $request->file('logo'),
                    'logos',
                    400,
                    85
                );
            } elseif ($request->has('remove_logo') && $request->remove_logo == '1') {
                // Remove logo if requested
                if ($restaurant->logo) {
                    $this->imageService->deleteImage($restaurant->logo);
                }
                $updateData['logo'] = null;
            }

            // Check if name changed and regenerate slug if needed
            if ($restaurant->name !== $request->name) {
                $slug = Str::slug($request->name);

                // Ensure unique slug
                $counter = 1;
                $originalSlug = $slug;
                while (Restaurant::where('slug', $slug)->where('id', '!=', $restaurant->id)->exists()) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }

                $updateData['slug'] = $slug;
            }

            $restaurant->update($updateData);

            return redirect()->route('restaurant.dashboard')
                ->with('success', 'Restaurant profile updated successfully!');
        }

        return redirect()->route('restaurant.dashboard')
            ->with('error', 'Restaurant not found.');
    }

    public function updateSettings(Request $request)
{
    $request->validate([
        'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
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

    if ($restaurant) {
        $updateData = [
            'facebook_url' => $request->facebook_url,
            'instagram_url' => $request->instagram_url,
            'snapchat_url' => $request->snapchat_url,
            'whatsapp_url' => $request->whatsapp_url,
            'twitter_url' => $request->twitter_url,
            'tiktok_url' => $request->tiktok_url,
            'theme_colors' => [
                'primary' => $request->primary_color ?? '#667eea',
                'secondary' => $request->secondary_color ?? '#764ba2',
                'accent' => $request->accent_color ?? '#4facfe',
                'text' => $request->text_color ?? '#ffffff',
                'background' => $request->background_color ?? '#0a0e27',
                'card' => $request->card_color ?? '#252d56',
                'secondary_bg' => $request->secondary_bg ?? '#141b3c',
                'tertiary_bg' => $request->tertiary_bg ?? '#1e2749',
                'secondary_text' => $request->secondary_text ?? '#e2e8f0',
                'muted_text' => $request->muted_text ?? '#94a3b8',
                'input_bg' => $request->input_bg ?? '#1e2749',
                'input_border' => $request->input_border ?? '#334155',
            ]
        ];

        // Handle background image upload/removal
        if ($request->hasFile('background_image')) {
            $updateData = [];
            // Delete old background image if exists
            if ($restaurant->background_image) {
                $this->imageService->deleteImage($restaurant->background_image);
            }

            // Upload new background image
            $updateData['background_image'] = $this->imageService->uploadAndCompressImage(
                $request->file('background_image'),
                'backgrounds',
                1920,
                80
            );
        } elseif ($request->has('remove_background') && $request->remove_background == '1') {
            // Remove background image if requested
            if ($restaurant->background_image) {
                $updateData = [];
                $this->imageService->deleteImage($restaurant->background_image);
            }
            $updateData['background_image'] = null;
        }
        $restaurant->update($updateData);

        return redirect()->route('restaurant.dashboard')
            ->with('success', 'Settings updated successfully!');
    }

    return redirect()->route('restaurant.dashboard')
        ->with('error', 'Restaurant not found.');
}
}