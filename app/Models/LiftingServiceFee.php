<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiftingServiceFee extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'regular_hours_fee' => 'integer',
        'four_hour_shift_fee' => 'integer',
        'eight_hour_shift_fee' => 'integer',
        'after_hours_fee' => 'integer',
    ];

    /**
     * Get vehicle registrations using this fee.
     */
    public function vehicleRegistrations()
    {
        return $this->hasMany(VehicleRegistration::class);
    }
}
