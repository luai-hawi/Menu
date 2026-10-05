<?php

// app/Http/Controllers/MenuController.php

namespace App\Http\Controllers;

use App\Models\Restaurant;

class MenuController extends Controller
{
    public function show($slug)
    {
        $locale = request()->query('lang', request()->cookie('app_locale', config('app.locale')));
        if (in_array($locale, ['ar', 'en'], true)) {
            app()->setLocale($locale);
        }

        $restaurant = Restaurant::with([
            'activeMenuCategories.activeMenuItems.optionGroups.activeOptions',
        ])
            ->select([
                'id',
                'name',
                'name_en',
                'slug',
                'description',
                'description_en',
                'welcome_title',
                'welcome_title_en',
                'welcome_message',
                'welcome_message_en',
                'welcome_video',
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
                'theme_colors',
                'currency',
                'currency_position',
            ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('menu.show', compact('restaurant'));
    }
}
