<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'morning_fee' => 'integer',
        'afternoon_fee' => 'integer',
        'full_day_fee' => 'integer',
        'night_fee' => 'integer',
    ];
}
