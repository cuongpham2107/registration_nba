<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'papers',
        'type',
        'license_plate',
        'areas',
        'note',
        'registration_id',
        'visitor_vehicle_fee_id',
        'gathering_point_fee_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'areas' => 'array',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class, 'registration_id');
    }

    public function visitorVehicleFee()
    {
        return $this->belongsTo(VisitorVehicleFee::class, 'visitor_vehicle_fee_id');
    }

    public function gatheringPointFee()
    {
        return $this->belongsTo(GatheringPointFee::class, 'gathering_point_fee_id');
    }
}
