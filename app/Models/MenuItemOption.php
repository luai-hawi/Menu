<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItemOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'option_group_id',
        'option_name_ar',
        'price_delta',
        'option_note_ar',
        'position',
        'is_active',
    ];

    protected $casts = [
        'price_delta' => 'decimal:2',
        'position' => 'integer',
        'is_active' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(MenuItemOptionGroup::class, 'option_group_id');
    }

    /**
     * Localized label — Arabic-only today; signature kept so callers
     * don't need to change if a second language is reintroduced.
     */
    public function nameFor(?string $locale = null): string
    {
        return (string) ($this->option_name_ar ?? '');
    }

    /**
     * Localized note — returns null when the note is empty.
     */
    public function noteFor(?string $locale = null): ?string
    {
        return ! empty($this->option_note_ar) ? $this->option_note_ar : null;
    }
}
