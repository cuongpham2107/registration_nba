<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class RegistrationVehicle extends Model
{
    use HasFactory;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('registration_vehicle');
    }


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

    public function registerDirectly()
    {
        return $this->belongsTo(RegisterDirectly::class, 'id_registration_directly');
    }

     public function blacklist()
    {
        return $this->hasOne(BlackList::class, 'registration_vehicle_id');
    }
     public function fee()
    {
        return $this->belongsTo(Fee::class, 'fee_id');
    }
}
