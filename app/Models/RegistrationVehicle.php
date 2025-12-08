<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationVehicle extends Model
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

    public function registerDirectly()
    {
        return $this->belongsTo(RegisterDirectly::class, 'id_registration_directly');
    }
}
