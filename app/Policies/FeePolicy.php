<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Fee;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class FeePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('ViewAny:Fee') || $authUser->hasRole('super_admin');
    }

    public function view(AuthUser $authUser, Fee $fee): bool
    {
        return $authUser->hasPermissionTo('View:Fee') || $authUser->hasRole('super_admin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Create:Fee') || $authUser->hasRole('super_admin');
    }

    public function update(AuthUser $authUser, Fee $fee): bool
    {
        return $authUser->hasPermissionTo('Update:Fee') || $authUser->hasRole('super_admin');
    }

    public function delete(AuthUser $authUser, Fee $fee): bool
    {
        return $authUser->hasPermissionTo('Delete:Fee') || $authUser->hasRole('super_admin');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('DeleteAny:Fee') || $authUser->hasRole('super_admin');
    }

    public function restore(AuthUser $authUser, Fee $fee): bool
    {
        return $authUser->hasPermissionTo('Restore:Fee') || $authUser->hasRole('super_admin');
    }

    public function forceDelete(AuthUser $authUser, Fee $fee): bool
    {
        return $authUser->hasPermissionTo('ForceDelete:Fee') || $authUser->hasRole('super_admin');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('ForceDeleteAny:Fee') || $authUser->hasRole('super_admin');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('RestoreAny:Fee') || $authUser->hasRole('super_admin');
    }

    public function replicate(AuthUser $authUser, Fee $fee): bool
    {
        return $authUser->hasPermissionTo('Replicate:Fee') || $authUser->hasRole('super_admin');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Reorder:Fee') || $authUser->hasRole('super_admin');
    }
}
