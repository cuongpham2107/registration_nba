<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('ViewAny:Role') || $authUser->hasRole('super_admin');
    }

    public function view(AuthUser $authUser, Role $role): bool
    {
        return $authUser->hasPermissionTo('View:Role') || $authUser->hasRole('super_admin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Create:Role') || $authUser->hasRole('super_admin');
    }

    public function update(AuthUser $authUser, Role $role): bool
    {
        return $authUser->hasPermissionTo('Update:Role') || $authUser->hasRole('super_admin');
    }

    public function delete(AuthUser $authUser, Role $role): bool
    {
        return $authUser->hasPermissionTo('Delete:Role') || $authUser->hasRole('super_admin');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('DeleteAny:Role') || $authUser->hasRole('super_admin');
    }

    public function restore(AuthUser $authUser, Role $role): bool
    {
        return $authUser->hasPermissionTo('Restore:Role') || $authUser->hasRole('super_admin');
    }

    public function forceDelete(AuthUser $authUser, Role $role): bool
    {
        return $authUser->hasPermissionTo('ForceDelete:Role') || $authUser->hasRole('super_admin');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('ForceDeleteAny:Role') || $authUser->hasRole('super_admin');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('RestoreAny:Role') || $authUser->hasRole('super_admin');
    }

    public function replicate(AuthUser $authUser, Role $role): bool
    {
        return $authUser->hasPermissionTo('Replicate:Role') || $authUser->hasRole('super_admin');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Reorder:Role') || $authUser->hasRole('super_admin');
    }
}
