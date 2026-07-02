<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViolationReport extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'recorded_at' => 'datetime',
        'reporters' => 'array',
        'witnesses' => 'array',
        'violators' => 'array',
    ];
}
