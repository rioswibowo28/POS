<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_number',
        'shift_date',
        'opened_by',
        'closed_by',
        'opened_at',
        'closed_at',
        'opening_cash',
        'closing_cash',
        'expected_cash',
        'cash_difference',
        'total_sales',
        'total_orders',
        'status',
        'notes',
    ];

    protected $casts = [
        'shift_date' => 'date',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'total_sales' => 'decimal:2',
    ];

    // Relationships
    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('shift_date', today());
    }

    // Helper methods
    public function isOpen()
    {
        return $this->status === 'open';
    }

    public function isClosed()
    {
        return $this->status === 'closed';
    }

    public static function getCurrentShift()
    {
        // Get ANY open shift regardless of date
        // This ensures only one shift can be open at a time
        return self::where('status', 'open')->first();
    }

    public static function getShiftNumber()
    {
        $now = now();
        $hour = $now->hour;
        
        // Shift 1: 05:00 - 17:00
        // Shift 2: 17:00 - 05:00
        
        if ($hour >= 5 && $hour < 17) {
            return 1;
        }
        
        return 2;
    }

    public static function getShiftDate()
    {
        $now = now();
        $hour = $now->hour;
        
        // For Shift 2 (17:00 - 05:00) that spans across midnight:
        // - If opened between 00:00 - 04:59 → use yesterday's date
        // - If opened between 17:00 - 23:59 → use today's date
        
        // For Shift 1 (05:00 - 17:00) → always use today's date
        
        if ($hour >= 0 && $hour < 5) {
            // Between midnight and 5 AM → part of shift 2 from yesterday
            return now()->subDay()->toDateString();
        }
        
        return now()->toDateString();
    }

    public function calculateTotals()
    {
        // Calculate total from cash payments only
        $cashPayments = $this->payments()
            ->where('method', 'cash')
            ->where('status', 'paid')
            ->sum('amount');

        $this->expected_cash = $this->opening_cash + $cashPayments;
        $this->save();
    }
}
