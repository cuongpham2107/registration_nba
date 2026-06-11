<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleCard extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
        ];
    }
}
