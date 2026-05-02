<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderNumberAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_type',
        'source_id',
        'action',
        'order_number',
        'bill_number',
        'previous_order_number',
        'previous_bill_number',
        'business_date',
        'flag',
        'status',
        'shift_id',
        'user_id',
        'context',
    ];

    protected $casts = [
        'business_date' => 'date',
        'flag' => 'boolean',
        'context' => 'array',
    ];
}
