<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarCatalog extends Model
{
    use HasFactory;
     /**
     * The attributes that are mass assignable.
     */
    protected $guarded = [];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
