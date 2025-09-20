<?php
// app/Models/MenuItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'menu_category_id',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2'
    ];

    public function menuCategory()
    {
        return $this->belongsTo(MenuCategory::class);
    }
}