<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class RegisterDirectly extends Model
{
    use HasFactory;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('register_directly');
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = [];

    protected $attributes = [
        'status' => 'none',
    ];

    public function cards()
    {
        return $this->belongsToMany(Card::class);
    }

    public function registrationVehicle()
    {
        return $this->belongsTo(RegistrationVehicle::class, 'id_registration_vehicle');
    }

    public function fee()
    {
        return $this->belongsTo(Fee::class, 'fee_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    protected $casts = [
        'areas' => 'array',
    ];
}
