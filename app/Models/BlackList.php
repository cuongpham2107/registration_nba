<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlackList extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'blacklisted_at' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function registrationVehicle()
    {
        return $this->belongsTo(RegistrationVehicle::class, 'registration_vehicle_id');
    }

    public function blacklistedBy()
    {
        return $this->belongsTo(User::class, 'blacklisted_by');
    }
}
