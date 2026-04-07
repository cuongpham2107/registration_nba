<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationEntry extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = [];

    protected $attributes = [
        'status' => 'none',
    ];

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function vehicleRegistration()
    {
        return $this->belongsTo(VehicleRegistration::class, 'id_vehicle_registration');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    protected $casts = [
        'areas' => 'array',
    ];
}
