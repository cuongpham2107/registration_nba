<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
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

    protected static function booted(): void
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('is_active', true);
        });
    }

    public function getAvatarAttribute(): ?string
    {
        $avatar = $this->getAttributeFromArray('avatar');

        if (! is_null($avatar) && $avatar !== '') {
            return 'https://id.asgl.net.vn/avatar/'.strtoupper($this->asgl_id);
        }

        $name = str($this->full_name)
            ->trim()
            ->explode(' ')
            ->map(fn (string $segment): string => filled($segment) ? mb_substr($segment, 0, 1) : '')
            ->join(' ');

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=FFFFFF&background=71717b';
    }

    // ----------------------------------------------------------------
    // Filament
    // ----------------------------------------------------------------

    public function getFilamentName(): string
    {
        return $this->full_name
            ?? $this->name
            ?? $this->email
            ?? 'Unknown';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    // ----------------------------------------------------------------
    // Helpers dùng chung — query vào DB A (mysql)
    // ----------------------------------------------------------------

    private function dbA(): Connection
    {
        return DB::connection('mysql');
    }

    private function getRoleIdsFromNames(array $names): Collection
    {
        return $this->dbA()
            ->table('roles')
            ->whereIn('name', $names)
            ->pluck('id');
    }

    private function getUserRoleIds(): Collection
    {
        return $this->dbA()
            ->table('model_has_roles')
            ->where('model_type', static::class)
            ->where('model_id', $this->id)
            ->pluck('role_id');
    }

    private function getUserPermissionIds(): Collection
    {
        return $this->dbA()
            ->table('model_has_permissions')
            ->where('model_type', static::class)
            ->where('model_id', $this->id)
            ->pluck('permission_id');
    }

    // ----------------------------------------------------------------
    // Override Spatie HasRoles methods
    // ----------------------------------------------------------------

    public function getRoleNames(): Collection
    {
        return $this->dbA()
            ->table('roles')
            ->whereIn('id', $this->getUserRoleIds())
            ->pluck('name');
    }

    public function hasRole($roles, ?string $guard = null): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];
        $roleIds = $this->getRoleIdsFromNames($roles);

        if ($roleIds->isEmpty()) {
            return false;
        }

        return $this->dbA()
            ->table('model_has_roles')
            ->where('model_type', static::class)
            ->where('model_id', $this->id)
            ->whereIn('role_id', $roleIds)
            ->exists();
    }

    public function hasAnyRole($roles): bool
    {
        return $this->hasRole(
            is_array($roles) ? $roles : [$roles]
        );
    }

    public function hasAllRoles($roles, ?string $guard = null): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];
        foreach ($roles as $role) {
            if (! $this->hasRole($role)) {
                return false;
            }
        }

        return true;
    }

    public function assignRole(...$roles): static
    {
        $roles = collect($roles)->flatten()->toArray();

        foreach ($roles as $role) {
            $roleModel = $this->dbA()
                ->table('roles')
                ->where('name', $role)
                ->first();

            if (! $roleModel) {
                continue;
            }

            $this->dbA()
                ->table('model_has_roles')
                ->insertOrIgnore([
                    'role_id' => $roleModel->id,
                    'model_type' => static::class,
                    'model_id' => $this->id,
                ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $this;
    }

    public function removeRole($role): static
    {
        $roleModel = $this->dbA()
            ->table('roles')
            ->where('name', $role)
            ->first();

        if ($roleModel) {
            $this->dbA()
                ->table('model_has_roles')
                ->where('model_type', static::class)
                ->where('model_id', $this->id)
                ->where('role_id', $roleModel->id)
                ->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $this;
    }

    public function syncRoles(...$roles): static
    {
        // Xóa hết role cũ
        $this->dbA()
            ->table('model_has_roles')
            ->where('model_type', static::class)
            ->where('model_id', $this->id)
            ->delete();

        // Gán role mới
        return $this->assignRole(collect($roles)->flatten()->toArray());
    }

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        // Check direct permission
        $permId = $this->dbA()
            ->table('permissions')
            ->where('name', $permission)
            ->value('id');

        if (! $permId) {
            return false;
        }

        // Check direct
        $hasDirect = $this->dbA()
            ->table('model_has_permissions')
            ->where('model_type', static::class)
            ->where('model_id', $this->id)
            ->where('permission_id', $permId)
            ->exists();

        if ($hasDirect) {
            return true;
        }

        // Check qua roles
        $roleIds = $this->getUserRoleIds();

        return $this->dbA()
            ->table('role_has_permissions')
            ->whereIn('role_id', $roleIds)
            ->where('permission_id', $permId)
            ->exists();
    }

    public function can($ability, $arguments = []): bool
    {
        // Check Laravel's native policy authorization first
        if (parent::can($ability, $arguments)) {
            return true;
        }

        // Fallback to Spatie string-based permission check
        return $this->hasPermissionTo($ability);
    }

    // ----------------------------------------------------------------
    // Scopes cho Filament list/filter
    // ----------------------------------------------------------------

    public function scopeRole(Builder $query, $roles, $guard = null, $without = false): Builder
    {
        $roleIds = $this->getRoleIdsFromNames((array) $roles);

        $userIds = $this->dbA()
            ->table('model_has_roles')
            ->where('model_type', static::class)
            ->whereIn('role_id', $roleIds)
            ->pluck('model_id');

        return $without
            ? $query->whereNotIn('id', $userIds)
            : $query->whereIn('id', $userIds);
    }

    public function scopeWithoutRole(Builder $query, $roles, $guard = null): Builder
    {
        return $this->scopeRole($query, $roles, $guard, true);
    }

    /**
     * Cấu hình phê duyệt của user này
     */
    public function approverConfig()
    {
        return $this->hasOne(UserApprover::class, 'user_id');
    }

    /**
     * Các dòng cấu hình phê duyệt của user này (nhiều approver)
     */
    public function approverConfigs()
    {
        return $this->hasMany(UserApprover::class, 'user_id');
    }

    /**
     * Lấy thông tin người phê duyệt
     */
    public function getApproverAttribute(): ?User
    {
        $approverId = UserApprover::query()
            ->where('user_id', $this->id)
            ->value('approver_id');

        if (! $approverId) {
            return null;
        }

        return User::on('id_db')->withoutGlobalScopes()->find($approverId);
    }

    /**
     * Những user mà user này phê duyệt
     */
    public function getApprovingAttribute(): Collection
    {
        $userIds = UserApprover::query()
            ->where('approver_id', $this->id)
            ->pluck('user_id');

        return User::on('id_db')->withoutGlobalScopes()->whereIn('id', $userIds)->get();
    }

    public function getDepartmentAttribute(): ?string
    {
        $position_id = DB::connection('id_db')
            ->table('position_user')
            ->where('user_id', $this->id)
            ->value('position_id');

        $department = DB::connection('id_db')
            ->table('positions')
            ->where('id', $position_id)
            ->value('name');

        return $department;
    }
}
