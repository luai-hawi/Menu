<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItemOptionGroup extends Model
{
    use HasFactory;

    public const TYPE_SINGLE = 'SINGLE';

    public const TYPE_MULTIPLE = 'MULTIPLE';

    protected $fillable = [
        'menu_item_id',
        'group_type',
        'group_name_ar',
        'min_choices',
        'max_choices',
        'is_required',
        'position',
    ];

    protected $casts = [
        'min_choices' => 'integer',
        'max_choices' => 'integer',
        'is_required' => 'boolean',
        'position' => 'integer',
    ];

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(MenuItemOption::class, 'option_group_id')
            ->orderBy('position');
    }

    public function activeOptions(): HasMany
    {
        return $this->hasMany(MenuItemOption::class, 'option_group_id')
            ->where('is_active', true)
            ->orderBy('position');
    }

    public function isSingle(): bool
    {
        return $this->group_type === self::TYPE_SINGLE;
    }

    public function isMultiple(): bool
    {
        return $this->group_type === self::TYPE_MULTIPLE;
    }

    /**
     * Localized group name. Arabic is the only stored value for now; the
     * method signature is preserved so future locales can plug in without
     * changing the callers.
     */
    public function nameFor(?string $locale = null): string
    {
        return (string) ($this->group_name_ar ?? '');
    }
}
