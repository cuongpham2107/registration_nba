<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id')->withoutGlobalScopes();
    }

    /**
     * Người tạo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes();
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function registrationEntries(): HasOne
    {
        return $this->hasOne(RegistrationEntry::class, 'registration_id');
    }
}
