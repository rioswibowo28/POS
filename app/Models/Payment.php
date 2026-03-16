<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'shift_id',
        'payment_number',
        'method',
        'status',
        'amount',
        'received_amount',
        'change_amount',
        'reference_number',
        'notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'method' => PaymentMethod::class,
        'status' => PaymentStatus::class,
        'amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (!$payment->payment_number) {
                $payment->payment_number = static::generatePaymentNumber($payment->order_id);
            }
        });
    }

    /**
     * Generate payment number berdasarkan flag order-nya
     * flag=0 (Normal): PAY-YYYYMMDD-0001 (4 digit, sequential tanpa gap untuk pihak ketiga)
     * flag=1 (No Tax): PAY-YYYYMMDD-001 (3 digit, sequential terpisah)
     */
    public static function generatePaymentNumber($orderId = null)
    {
        $date = date('Ymd');
        $prefix = 'PAY-';

        // Determine flag from the order
        $flag = false;
        if ($orderId) {
            $order = Order::find($orderId);
            $flag = $order ? (bool) $order->flag : false;
        }

        // Count today's payments with the same flag type
        $count = Payment::whereDate('created_at', today())
            ->whereHas('order', function ($q) use ($flag) {
                $q->where('flag', $flag);
            })
            ->count();

        $number = $flag
            ? str_pad($count + 1, 3, '0', STR_PAD_LEFT)
            : str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        return $prefix . $date . '-' . $number;
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
