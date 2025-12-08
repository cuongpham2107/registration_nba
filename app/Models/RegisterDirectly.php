<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RegisterDirectly extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = [];

    protected $attributes = [
        'status' => 'none',
    ];
    
    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    protected $casts = [
        'areas' => 'array',
    ];
}
