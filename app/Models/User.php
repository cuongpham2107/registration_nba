<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;
    use HasPanelShield;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Determine if the user can access the Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Super admin luôn được truy cập
        if ($this->hasRole('super_admin')) {
            return true;
        }

        // User có bất kỳ role nào (panel_user, approver, etc.) đều được truy cập
        if ($this->roles()->count() > 0) {
            return true;
        }

        // User không có role vẫn được truy cập (có thể điều chỉnh theo nhu cầu)
        return true;
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    // Relationship ngược lại: Những user mà user này phê duyệt
    public function approving()
    {
        return $this->hasMany(User::class, 'approver_id');
    }
}
