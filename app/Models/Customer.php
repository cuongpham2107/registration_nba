<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = [];

    public function visitorRegistration()
    {
        return $this->belongsTo(VisitorRegistration::class);
    }

    public function vehicleRegistration()
    {
        return $this->belongsTo(VehicleRegistration::class);
    }

    protected $casts = [
        'areas' => 'array',
    ];
}
