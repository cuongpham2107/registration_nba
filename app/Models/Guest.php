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
    protected $guarded = [];

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

    public function fee()
    {
        return $this->belongsTo(Fee::class, 'fee_id');
    }
}
