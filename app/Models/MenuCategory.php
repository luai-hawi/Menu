<?php

// app/Models/MenuCategory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'restaurant_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function nameFor(?string $locale = null): string
    {
        return (string) (($locale ?? app()->getLocale()) === 'en' && filled($this->name_en) ? $this->name_en : $this->name);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    public function activeMenuItems()
    {
        return $this->hasMany(MenuItem::class)->where('is_active', true)->orderBy('sort_order');
    }
}
