<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisitorRegistration extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = [];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function visitorVehicleFee()
    {
        return $this->belongsTo(VisitorVehicleFee::class);
    }

    public function registrationEntries()
    {
        return $this->hasMany(RegistrationEntry::class, 'visitor_registration_id');
    }
}
