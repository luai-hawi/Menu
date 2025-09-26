<?php
// app/Http/Controllers/MenuController.php
namespace App\Http\Controllers;

use App\Models\Restaurant;

class MenuController extends Controller
{
public function show($slug)
{
    $restaurant = Restaurant::with(['activeMenuCategories.activeMenuItems'])
        ->select([
            'id',
            'name',
            'slug',
            'description',
            'logo',
            'background_image',
            'is_active',
            'whatsapp_orders_enabled',
            'whatsapp_number',
            'facebook_url',
            'instagram_url',
            'snapchat_url',
            'whatsapp_url',
            'twitter_url',
            'tiktok_url',
            'theme_colors'
        ])
        ->where('slug', $slug)
        ->where('is_active', true)
        ->firstOrFail();

    return view('menu.show', compact('restaurant'));
}
}