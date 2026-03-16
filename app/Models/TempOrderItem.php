<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempOrderItem extends Model
{
    use HasFactory;

    protected $table = 'temp_order_items';

    protected $fillable = [
        'temp_order_id',
        'product_id',
        'product_variant_id',
        'name',
        'price',
        'quantity',
        'modifiers',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'modifiers' => 'array',
    ];

    public function tempOrder()
    {
        return $this->belongsTo(TempOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function getSubtotalAttribute()
    {
        $modifierTotal = 0;
        if ($this->modifiers) {
            foreach ($this->modifiers as $modifier) {
                $modifierTotal += $modifier['price'] ?? 0;
            }
        }
        return ($this->price + $modifierTotal) * $this->quantity;
    }
}
