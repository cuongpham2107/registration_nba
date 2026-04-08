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
        return $this->belongsTo(VehicleRegistration::class, 'vehicle_registration_id');
    }

    public function visitorRegistration()
    {
        return $this->belongsTo(VisitorRegistration::class, 'visitor_registration_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    protected $casts = [
        'areas' => 'array',
    ];
}
