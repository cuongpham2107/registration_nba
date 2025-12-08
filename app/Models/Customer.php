<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = [];
    
    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    protected $casts = [
        'areas' => 'array',
    ];
}
