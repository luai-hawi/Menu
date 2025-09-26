<?php
// routes/web.php
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\AdminController;

// Auth routes MUST come first
require __DIR__.'/auth.php';

// Home page
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Default dashboard (redirect to appropriate dashboard based on role)
Route::get('/dashboard', function () {
    if (auth()->user()->isAdmin()) {
        return redirect()->route('admin.index');
    } elseif (auth()->user()->isRestaurantOwner() || auth()->user()->restaurants()->exists()) {
        return redirect()->route('restaurant.dashboard');
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Restaurant owner routes
Route::middleware('auth')->group(function () {
    Route::get('/restaurant/dashboard', [RestaurantController::class, 'dashboard'])->name('restaurant.dashboard');
    Route::post('/restaurant/select', [RestaurantController::class, 'selectRestaurant'])->name('restaurant.select');
    Route::post('/category', [RestaurantController::class, 'storeCategory'])->name('category.store');
    Route::post('/menu-item', [RestaurantController::class, 'storeItem'])->name('item.store');
    Route::put('/menu-item/{item}', [RestaurantController::class, 'updateItem'])->name('item.update');
    Route::delete('/menu-item/{item}', [RestaurantController::class, 'deleteItem'])->name('item.delete');
    Route::delete('/category/{category}', [RestaurantController::class, 'deleteCategory'])->name('category.delete');
    Route::post('/restaurant/whatsapp/toggle', [RestaurantController::class, 'toggleWhatsApp'])->name('restaurant.whatsapp.toggle');
    Route::post('/restaurant/whatsapp/update', [RestaurantController::class, 'updateWhatsApp'])->name('restaurant.whatsapp.update');
    Route::post('/restaurant/profile', [RestaurantController::class, 'updateProfile'])->name('restaurant.update.profile')->middleware('auth');
    Route::post('/restaurant/settings', [RestaurantController::class, 'updateSettings'])->name('restaurant.update.settings')->middleware('auth');
});

// Admin-only restaurant creation routes
Route::middleware('admin')->group(function () {
    Route::get('/restaurant/create', [RestaurantController::class, 'create'])->name('restaurant.create');
    Route::post('/restaurant', [RestaurantController::class, 'store'])->name('restaurant.store');
});

// Admin routes
Route::middleware('admin')->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/restaurant/create', [AdminController::class, 'createRestaurant'])->name('admin.restaurant.create');
    Route::post('/restaurant', [AdminController::class, 'storeRestaurant'])->name('admin.restaurant.store');
    Route::post('/restaurant/{restaurant}/toggle', [AdminController::class, 'toggleRestaurant'])->name('admin.restaurant.toggle');
    Route::delete('/restaurant/{restaurant}', [AdminController::class, 'deleteRestaurant'])->name('admin.restaurant.delete');

    // User management routes
    Route::get('/user/{user}/edit', [AdminController::class, 'editUser'])->name('admin.user.edit');
    Route::put('/user/{user}', [AdminController::class, 'updateUser'])->name('admin.user.update');

    // Subscription management routes
    Route::get('/subscription/{subscription}/edit', [AdminController::class, 'editSubscription'])->name('admin.subscription.edit');
    Route::put('/subscription/{subscription}', [AdminController::class, 'updateSubscription'])->name('admin.subscription.update');
    Route::post('/subscription/{subscription}/mark-paid', [AdminController::class, 'markPaid'])->name('admin.subscription.mark-paid');
});

// Public menu routes (MUST come last to avoid conflicts)
Route::get('/{restaurant:slug}', [MenuController::class, 'show'])->name('menu.show');