<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'image',
        'price',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(PackageItem::class);
    }

    /**
     * Get total normal price (sum of all products * qty)
     */
    public function getNormalPriceAttribute()
    {
        return $this->items->sum(function ($item) {
            return ($item->product->price ?? 0) * $item->quantity;
        });
    }

    /**
     * Get savings amount
     */
    public function getSavingsAttribute()
    {
        return max(0, $this->normal_price - $this->price);
    }
}
