<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TempOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'temp_orders';

    /**
     * Hidden from JSON serialization
     */
    protected $hidden = [
        'flag',
        'deleted_at',
        'deleted_by',
    ];

    protected $fillable = [
        'order_number',
        'bill_number',
        'table_id',
        'customer_name',
        'customer_phone',
        'type',
        'status',
        'subtotal',
        'tax',
        'discount',
        'total',
        'notes',
        'cashier_id',
        'created_by',
        'paid_by',
        'deleted_by',
        'shift_id',
        'flag',
        'tax_amount',
        'completed_at',
        'original_order_id',
        'payment_method',
        'payment_amount',
        'payment_received',
        'payment_change',
        'payment_reference',
        'payment_at',
    ];

    protected $casts = [
        'type' => OrderType::class,
        'status' => OrderStatus::class,
        'flag' => 'boolean',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'completed_at' => 'datetime',
        'payment_amount' => 'decimal:2',
        'payment_received' => 'decimal:2',
        'payment_change' => 'decimal:2',
        'payment_at' => 'datetime',
    ];

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function items()
    {
        return $this->hasMany(TempOrderItem::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paidByUser()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function deletedByUser()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
