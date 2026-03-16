<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicReport extends Model
{
    protected $fillable = [
        'name',
        'description',
        'view_name',
        'date_column',
        'allowed_roles',
        'show_grand_total',
    ];

    protected $casts = [
        'allowed_roles' => 'array',
        'show_grand_total' => 'boolean',
    ];
}
