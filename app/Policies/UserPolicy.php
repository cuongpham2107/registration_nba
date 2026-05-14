<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('ViewAny:User') || $authUser->hasRole('super_admin');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('View:User') || $authUser->hasRole('super_admin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Create:User') || $authUser->hasRole('super_admin');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Update:User') || $authUser->hasRole('super_admin');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Delete:User') || $authUser->hasRole('super_admin');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('DeleteAny:User') || $authUser->hasRole('super_admin');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Restore:User') || $authUser->hasRole('super_admin');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('ForceDelete:User') || $authUser->hasRole('super_admin');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('ForceDeleteAny:User') || $authUser->hasRole('super_admin');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('RestoreAny:User') || $authUser->hasRole('super_admin');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Replicate:User') || $authUser->hasRole('super_admin');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Reorder:User') || $authUser->hasRole('super_admin');
    }
}
