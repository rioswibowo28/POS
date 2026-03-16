<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterShift extends Model
{
    protected $fillable = ['name', 'start_time', 'end_time', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getFormattedTimeAttribute()
    {
        return substr($this->start_time, 0, 5) . ' - ' . substr($this->end_time, 0, 5);
    }
}
