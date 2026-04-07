<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleRegistration extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'expected_in_at' => 'datetime',
        // 'expected_out_at' => 'datetime',
        'is_priority' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function registrationEntry()
    {
        return $this->belongsTo(RegistrationEntry::class, 'id_registration_entry');
    }

    public function gatheringPointFee()
    {
        return $this->belongsTo(GatheringPointFee::class);
    }

    public function liftingServiceFee()
    {
        return $this->belongsTo(LiftingServiceFee::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
