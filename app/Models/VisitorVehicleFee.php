<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorVehicleFee extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'per_visit_fee' => 'integer',
        'monthly_fee' => 'integer',
    ];

    /**
     * Get visitor registrations using this fee.
     */
    public function visitorRegistrations()
    {
        return $this->hasMany(VisitorRegistration::class);
    }
}
