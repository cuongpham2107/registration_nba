<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // ← thêm interface này
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasName
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;

    // ✅ Thêm dòng này — trỏ về DB B
    protected $connection = 'id_db';

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

    // User model

    // ✅ Override roles()
    public function roles(): BelongsToMany
    {
        $registrar = app(PermissionRegistrar::class);

        // Giờ config đã có prefix rồi, dùng thẳng config là đủ
        $relation = $this->morphToMany(
            config('permission.models.role'),
            'model',
            config('permission.table_names.model_has_roles'), // → "registration_nba.model_has_roles"
            config('permission.column_names.model_morph_key'),
            $registrar->pivotRole
        );

        if (! $registrar->teams) {
            return $relation;
        }

        $teamsKey = $registrar->teamsKey;
        $teamField = config('permission.table_names.roles').'.'.$teamsKey;

        return $relation
            ->withPivot($teamsKey)
            ->wherePivot($teamsKey, getPermissionsTeamId())
            ->where(fn ($q) => $q->whereNull($teamField)
                ->orWhere($teamField, getPermissionsTeamId()));
    }

    public function permissions(): BelongsToMany
    {
        $registrar = app(PermissionRegistrar::class);

        return $this->morphToMany(
            config('permission.models.permission'),
            'model',
            config('permission.table_names.model_has_permissions'), // → "registration_nba.model_has_permissions"
            config('permission.column_names.model_morph_key'),
            $registrar->pivotPermission
        );
    }

    /**
     * Filament dùng method này để hiển thị tên user trên UI
     * Đổi 'full_name' thành tên field thực tế trong DB B
     */
    public function getFilamentName(): string
    {
        return $this->full_name          // thử các field phổ biến
            ?? $this->display_name
            ?? $this->username
            ?? $this->email
            ?? 'Unknown';
    }
}
