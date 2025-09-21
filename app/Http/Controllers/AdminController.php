<?php
// app/Http/Controllers/AdminController.php
namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class AdminController extends Controller
{
    protected $imageService;
    
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index()
    {
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
    }

    public function createRestaurant()
    {
        // Get users who are either restaurant owners by role OR have restaurants
        $owners = User::where(function($query) {
            $query->where('role', 'restaurant_owner')
                  ->orWhereHas('restaurants');
        })->withCount('restaurants')->get();

        return view('admin.create-restaurant', compact('owners'));
    }

    public function storeRestaurant(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:restaurants',
            'description' => 'nullable|string',
            'owner_method' => 'required|in:existing,new',
            'owner_email' => 'required_if:owner_method,new|nullable|email',
            'user_id' => 'required_if:owner_method,existing|nullable|exists:users,id',
            'password' => 'required_if:owner_method,new|nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'logo' => 'nullable|image|max:2048',
            'subscription_amount' => 'nullable|numeric|min:0'
        ]);

        $userId = null;
        $message = '';

        if ($request->owner_method === 'existing') {
            // Use existing user
            $user = User::find($request->user_id);
            $userId = $user->id;
            $message = __('messages.restaurant_created_existing_owner', ['name' => $request->name, 'email' => $user->email]);
        } else {
            // Create new user or use existing one with the email
            $existingUser = User::whereRaw('LOWER(email) = ?', [strtolower($request->owner_email)])->first();

            if ($existingUser) {
                // User exists, use their ID
                $userId = $existingUser->id;
                $message = __('messages.restaurant_created_existing_user', ['name' => $request->name, 'email' => $request->owner_email]);
            } else {
                // Create new user with provided password
                $user = User::create([
                    'name' => explode('@', $request->owner_email)[0], // Use email prefix as name initially
                    'email' => $request->owner_email,
                    'phone' => $request->phone,
                    'password' => Hash::make($request->password),
                    'role' => 'restaurant_owner',
                    'email_verified_at' => now(),
                ]);
                $userId = $user->id;
                $message = __('messages.restaurant_created_new_user', ['name' => $request->name, 'email' => $request->owner_email]);
            }
        }

        // Create subscription if not exists
        \App\Models\Subscription::firstOrCreate(
            ['user_id' => $userId],
            ['amount' => $request->subscription_amount ?: 100.00]
        );

        $restaurant = new Restaurant([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'user_id' => $userId,
        ]);

        if ($request->hasFile('logo')) {
            $restaurant->logo = $request->file('logo')->store('logos', 'public');
        }

        $restaurant->save();

        return redirect()->route('admin.index')->with('success', $message);
    }

    public function toggleRestaurant(Restaurant $restaurant)
    {
        $restaurant->is_active = !$restaurant->is_active;
        $restaurant->save();

        $status = $restaurant->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.index')->with('success', "Restaurant '{$restaurant->name}' has been {$status}.");
    }

    public function deleteRestaurant(Restaurant $restaurant)
    {
        DB::beginTransaction();
        
        try {
            $restaurantName = $restaurant->name;
            
            // Delete restaurant logo if exists
            if ($restaurant->logo) {
                $this->deleteImageSafely($restaurant->logo);
            }
            
            // Get all menu categories with their items
            $categories = $restaurant->menuCategories()->with('menuItems')->get();
            
            // Delete all menu item images first
            foreach ($categories as $category) {
                foreach ($category->menuItems as $item) {
                    if ($item->image) {
                        $this->deleteImageSafely($item->image);
                    }
                }
            }

            $owner = $restaurant->user;
            
            // Delete the restaurant (this will cascade delete categories and items due to foreign keys)
            $restaurant->delete();

            //delete user if they have no other restaurants
            if ($owner && $owner->restaurants()->count() === 0) {
                $owner->delete();
            }
            
            DB::commit();
            
            return redirect()->route('admin.index')->with('success', "Restaurant '{$restaurantName}' and all associated data have been permanently deleted.");
            
        } catch (Exception $e) {
            DB::rollback();
            
            // Log the error for debugging
            \Log::error('Failed to delete restaurant: ' . $e->getMessage(), [
                'restaurant_id' => $restaurant->id,
                'restaurant_name' => $restaurant->name,
                'error' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.index')->with('error', 'Failed to delete restaurant. Please check the logs for more details.');
        }
    }

    public function editSubscription(Subscription $subscription)
    {
        return view('admin.edit-subscription', compact('subscription'));
    }

    public function updateSubscription(Request $request, Subscription $subscription)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $subscription->update([
            'amount' => $request->amount,
        ]);

        return redirect()->route('admin.index')->with('success', 'Subscription cost updated successfully.');
    }

    public function markPaid(Subscription $subscription)
    {
        $subscription->paid_at = now();
        $subscription->expires_at = now()->addYear();
        $subscription->save();

        return redirect()->back()->with('success', 'Subscription marked as paid.');
    }

    /**
     * Safely delete an image, handling both ImageService and direct Storage deletion
     */
    private function deleteImageSafely($imagePath)
    {
        try {
            // Try using ImageService first if it exists
            if ($this->imageService && method_exists($this->imageService, 'deleteImage')) {
                $this->imageService->deleteImage($imagePath);
            } else {
                // Fallback to direct Storage deletion
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
            }
        } catch (Exception $e) {
            // Log the error but don't fail the whole operation
            \Log::warning('Failed to delete image: ' . $imagePath . ' - ' . $e->getMessage());
        }
    }

    public function editUser(User $user)
    {
        return view('admin.edit-user', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Update user data
        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('admin.index')->with('success', __('messages.user_updated_successfully'));
    }
}