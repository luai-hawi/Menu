<?php
// database/seeders/DatabaseSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\MenuCategory;
use App\Models\MenuItem;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'phone' => '+1-555-0100',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create restaurant owner
        $owner = User::create([
            'name' => 'Restaurant Owner',
            'email' => 'owner@example.com',
            'phone' => '+1-555-0200',
            'password' => bcrypt('password'),
            'role' => 'restaurant_owner',
            'email_verified_at' => now(),
        ]);

        // Create additional restaurant owners for testing
        $owner2 = User::create([
            'name' => 'Sarah Johnson',
            'email' => 'sarah.johnson@example.com',
            'phone' => '+1-555-0300',
            'password' => bcrypt('password'),
            'role' => 'restaurant_owner',
            'email_verified_at' => now(),
        ]);

        $owner3 = User::create([
            'name' => 'Mike Chen',
            'email' => 'mike.chen@example.com',
            'phone' => '+1-555-0400',
            'password' => bcrypt('password'),
            'role' => 'restaurant_owner',
            'email_verified_at' => now(),
        ]);

        $owner4 = User::create([
            'name' => 'Ahmed Hassan',
            'email' => 'ahmed.hassan@example.com',
            'phone' => '+972-50-123-4567',
            'password' => bcrypt('password'),
            'role' => 'restaurant_owner',
            'email_verified_at' => now(),
        ]);

        // Create sample restaurants
        $restaurant1 = Restaurant::create([
            'name' => 'Hawit Tech Cafe',
            'slug' => 'hawit-tech-cafe',
            'description' => 'Modern cafe with tech-inspired dishes',
            'user_id' => $owner->id,
            'is_active' => true,
        ]);

        $restaurant2 = Restaurant::create([
            'name' => 'Digital Diner',
            'slug' => 'digital-diner',
            'description' => 'Your favorite digital dining experience',
            'user_id' => $owner->id,
            'is_active' => true,
        ]);

        // Create restaurants for additional owners
        $restaurant3 = Restaurant::create([
            'name' => 'Sarah\'s Italian Kitchen',
            'slug' => 'sarahs-italian-kitchen',
            'description' => 'Authentic Italian cuisine with a modern twist',
            'user_id' => $owner2->id,
            'is_active' => true,
        ]);

        $restaurant4 = Restaurant::create([
            'name' => 'Mike\'s Asian Fusion',
            'slug' => 'mikes-asian-fusion',
            'description' => 'Bold flavors from across Asia',
            'user_id' => $owner3->id,
            'is_active' => true,
        ]);

        $restaurant5 = Restaurant::create([
            'name' => 'Ahmed\'s Middle Eastern Grill',
            'slug' => 'ahmeds-middle-eastern-grill',
            'description' => 'Traditional Middle Eastern dishes with modern presentation',
            'user_id' => $owner4->id,
            'is_active' => true,
        ]);

        $restaurant6 = Restaurant::create([
            'name' => 'Ahmed\'s Shawarma Palace',
            'slug' => 'ahmeds-shawarma-palace',
            'description' => 'Authentic shawarma and Middle Eastern specialties',
            'user_id' => $owner4->id,
            'is_active' => true,
        ]);

        // Create menu categories and items for restaurant1
        $appetizers = MenuCategory::create([
            'name' => 'Appetizers',
            'restaurant_id' => $restaurant1->id,
            'sort_order' => 1,
        ]);

        $mains = MenuCategory::create([
            'name' => 'Main Courses',
            'restaurant_id' => $restaurant1->id,
            'sort_order' => 2,
        ]);

        $beverages = MenuCategory::create([
            'name' => 'Beverages',
            'restaurant_id' => $restaurant1->id,
            'sort_order' => 3,
        ]);

        // Sample menu items
        MenuItem::create([
            'name' => 'Code Chips',
            'description' => 'Crispy potato chips with binary seasoning',
            'price' => 8.99,
            'menu_category_id' => $appetizers->id,
            'sort_order' => 1,
        ]);

        MenuItem::create([
            'name' => 'Debug Burger',
            'description' => 'Juicy beef burger that fixes all your problems',
            'price' => 15.99,
            'menu_category_id' => $mains->id,
            'sort_order' => 1,
        ]);

        MenuItem::create([
            'name' => 'Java Coffee',
            'description' => 'Strong coffee to keep you coding all night',
            'price' => 4.99,
            'menu_category_id' => $beverages->id,
            'sort_order' => 1,
        ]);
    }
}