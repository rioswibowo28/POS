<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Hidden from JSON serialization - flag tidak boleh terlihat oleh pihak ketiga
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
    ];

    protected $casts = [
        'type' => OrderType::class,
        'status' => OrderStatus::class,
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'flag' => 'boolean',
        'tax_amount' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = static::generateOrderNumber($order->flag ?? false);
            }
            if (!$order->bill_number) {
                $order->bill_number = static::generateBillNumber($order->flag ?? false);
            }
        });

        // Update table status when order is soft deleted
        static::deleted(function ($order) {
            if ($order->table_id) {
                // Check if there are other active orders for this table
                $hasActiveOrder = static::where('table_id', $order->table_id)
                    ->whereIn('status', ['pending', 'processing'])
                    ->exists();
                
                if (!$hasActiveOrder) {
                    Table::where('id', $order->table_id)->update(['status' => 'available']);
                }
            }
        });
    }

    /**
     * Generate order number based on flag
     * flag=0 (Normal): ORD-20260304-0001 (4 digits)
     * flag=1 (No Tax): ORD-20260304-001 (3 digits)
     */
    public static function generateOrderNumber($flag = false)
    {
        $date = date('Ymd');
        $prefix = 'ORD-';
        
        // Count orders for today with the same flag (including soft-deleted)
        $count = Order::withTrashed()
            ->whereDate('created_at', today())
            ->where('flag', $flag)
            ->count();
        
        // Also count orders that were moved to temp_orders
        $countTemp = TempOrder::whereDate('created_at', today())
            ->where('flag', $flag)
            ->count();
        
        $totalCount = $count + $countTemp;
        
        // Different padding based on flag: 4 digits for normal, 3 digits for flag=1
        $number = $flag ? str_pad($totalCount + 1, 3, '0', STR_PAD_LEFT) : str_pad($totalCount + 1, 4, '0', STR_PAD_LEFT);
        
        return $prefix . $date . '-' . $number;
    }

    /**
     * Generate bill number based on flag
     * flag=0 (Normal): BILL-YYMMDD-0001 (4 digits)
     * flag=1 (No Tax): BILL-YYMMDD-001 (3 digits)
     */
    public static function generateBillNumber($flag = false)
    {
        $date = date('ymd'); // YY format (2 digits untuk tahun)
        $prefix = 'BILL-';
        
        // Count orders for today with the same flag (including soft-deleted)
        $count = Order::withTrashed()
            ->whereDate('created_at', today())
            ->where('flag', $flag)
            ->count();
        
        // Also count orders that were moved to temp_orders
        $countTemp = TempOrder::whereDate('created_at', today())
            ->where('flag', $flag)
            ->count();
        
        $totalCount = $count + $countTemp;
        
        // Different padding based on flag
        $number = $flag ? str_pad($totalCount + 1, 3, '0', STR_PAD_LEFT) : str_pad($totalCount + 1, 4, '0', STR_PAD_LEFT);
        
        return $prefix . $date . '-' . $number;
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
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

    public function calculateTotal()
    {
        $itemsTotal = $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $taxType = \App\Models\Setting::get('tax_type', 'exclude');
        $taxRate = $this->tax / 100;

        if ($taxType === 'include') {
            // Harga sudah termasuk PPN: extract DPP (subtotal) dan PPN
            $this->subtotal = round($itemsTotal / (1 + $taxRate));
            $this->tax_amount = $itemsTotal - $this->subtotal;
            $this->total = $itemsTotal - $this->discount;
        } else {
            // Harga belum termasuk PPN: PPN ditambahkan di atas subtotal
            $this->subtotal = $itemsTotal;
            $this->tax_amount = round($itemsTotal * $taxRate);
            $this->total = $this->subtotal + $this->tax_amount - $this->discount;
        }
        
        $this->save();
    }
}
