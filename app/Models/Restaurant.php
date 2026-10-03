<?php

// app/Models/Restaurant.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
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
        'user_id',
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
    ];

    protected $casts = [
        'theme_colors' => 'array',
        'is_active' => 'boolean',
        'whatsapp_orders_enabled' => 'boolean',
    ];

    protected $hidden = ['admin_notes'];

    public function nameFor(?string $locale = null): string
    {
        return (string) (($locale ?? app()->getLocale()) === 'en' && filled($this->name_en) ? $this->name_en : $this->name);
    }

    public function descriptionFor(?string $locale = null): ?string
    {
        return ($locale ?? app()->getLocale()) === 'en' && filled($this->description_en) ? $this->description_en : $this->description;
    }

    public function welcomeTitleFor(?string $locale = null): ?string
    {
        return ($locale ?? app()->getLocale()) === 'en' && filled($this->welcome_title_en) ? $this->welcome_title_en : $this->welcome_title;
    }

    public function welcomeMessageFor(?string $locale = null): ?string
    {
        return ($locale ?? app()->getLocale()) === 'en' && filled($this->welcome_message_en) ? $this->welcome_message_en : $this->welcome_message;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function menuCategories()
    {
        return $this->hasMany(MenuCategory::class)->orderBy('sort_order');
    }

    public function activeMenuCategories()
    {
        return $this->hasMany(MenuCategory::class)->where('is_active', true)->orderBy('sort_order');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
