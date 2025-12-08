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
    protected $fillable = [
        'name',
        'papers',
        'address',
        'bks',
        'contact_person',
        'job',
        'card_id',
        'start_date',
        'end_date',
        'is_priority',
        'actual_date_out',
        'actual_date_in',
        'status',
        'areas',
        'sort',
        'type'
    ];

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
