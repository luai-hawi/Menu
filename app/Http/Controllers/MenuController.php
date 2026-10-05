<?php

// app/Http/Controllers/MenuController.php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Services\Language;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function show(Request $request, string $slug, Language $language)
    {
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
                'default_language',
            ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // A first-time customer has neither an explicit ?lang= nor a saved
        // preference, so the owner's default decides what they see. Content
        // reads its locale from the app at render time, so this only has to
        // happen before the view is built.
        app()->setLocale($language->resolve(
            $request->query('lang'),
            $request->cookie('app_locale'),
            $restaurant->defaultLanguage(),
        ));

        return view('menu.show', compact('restaurant'));
    }
}
