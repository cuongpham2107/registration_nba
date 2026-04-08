<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = [];

    /**
     * Người phê duyệt
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Người tạo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function guests()
    {
        return $this->hasMany(Guest::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function registrationEntries()
    {
        return $this->hasOne(RegistrationEntry::class, 'registration_id');
    }
}
