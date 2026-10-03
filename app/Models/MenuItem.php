<?php

// app/Models/MenuItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'description',
        'description_en',
        'price',
        'image',
        'menu_category_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function nameFor(?string $locale = null): string
    {
        return (string) (($locale ?? app()->getLocale()) === 'en' && filled($this->name_en) ? $this->name_en : $this->name);
    }

    public function descriptionFor(?string $locale = null): ?string
    {
        return ($locale ?? app()->getLocale()) === 'en' && filled($this->description_en) ? $this->description_en : $this->description;
    }

    public function menuCategory(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class);
    }

    /**
     * All option groups (admin view).
     * Eager-loading this plus its nested `options` relation on a menu page
     * keeps the query count at O(1) regardless of how many items/groups exist.
     */
    public function optionGroups(): HasMany
    {
        return $this->hasMany(MenuItemOptionGroup::class)
            ->orderBy('position');
    }

    /**
     * Convenience helper for the public menu — only groups that have at least
     * one active option are worth showing.
     */
    public function activeOptionGroups(): HasMany
    {
        return $this->hasMany(MenuItemOptionGroup::class)
            ->orderBy('position');
    }
}
