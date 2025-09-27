<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            // Admin dashboard logic
            $restaurants = Restaurant::with('user')->latest()->get();

            // Get users who are either restaurant owners by role OR have restaurants
            $users = User::where(function($query) {
                $query->where('role', 'restaurant_owner')
                      ->orWhereHas('restaurants');
            })->withCount('restaurants')->get();

            // Get unpaid or expired subscriptions
            $unpaidSubscriptions = Subscription::where(function($query) {
                $query->whereNull('paid_at')->orWhere('expires_at', '<', now());
            })->with('user')->get();

            return view('admin.index', compact('restaurants', 'users', 'unpaidSubscriptions'));
        } elseif ($user->isRestaurantOwner() || $user->restaurants()->exists()) {
            // Restaurant owner dashboard logic
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

        // Default dashboard for other users
        return view('dashboard');
    }
}