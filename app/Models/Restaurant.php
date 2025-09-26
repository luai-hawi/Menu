<?php
// app/Models/Restaurant.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Restaurant extends Model
{
    use HasFactory;

   protected $fillable = [
    'name',
    'slug',
    'description',
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
    'theme_colors'
];

protected $casts = [
    'theme_colors' => 'array'
];

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