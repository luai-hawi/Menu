<?php

namespace App\Http\Controllers;

use App\Services\Admin\AdminDashboardService;
use App\Services\VideoService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, AdminDashboardService $adminDashboard, VideoService $videoService)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return view('admin.index', $adminDashboard->build($request, $user));
        }

        if ($user->isRestaurantOwner() || $user->restaurants()->exists()) {
            $restaurants = $user->restaurants()->orderBy('id')->get();

            if ($restaurants->isEmpty()) {
                // Restaurant creation is admin-only, so show an empty state instead of a 403 redirect.
                return view('dashboard', ['ownerWithoutRestaurants' => true]);
            }

            $restaurant = $restaurants->firstWhere('id', (int) session('selected_restaurant_id'))
                ?? $restaurants->first();
            session(['selected_restaurant_id' => $restaurant->id]);

            $categories = $restaurant->menuCategories()
                ->with('menuItems.optionGroups.options')
                ->get();

            $videoAvailable = $videoService->available();

            return view('restaurant.dashboard', compact('restaurant', 'categories', 'restaurants', 'videoAvailable'));
        }

        return view('dashboard', ['ownerWithoutRestaurants' => false]);
    }
}