<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Area;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AreaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('ViewAny:Area') || $authUser->hasRole('super_admin');
    }

    public function view(AuthUser $authUser, Area $area): bool
    {
        return $authUser->hasPermissionTo('View:Area') || $authUser->hasRole('super_admin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Create:Area') || $authUser->hasRole('super_admin');
    }

    public function update(AuthUser $authUser, Area $area): bool
    {
        return $authUser->hasPermissionTo('Update:Area') || $authUser->hasRole('super_admin');
    }

    public function delete(AuthUser $authUser, Area $area): bool
    {
        return $authUser->hasPermissionTo('Delete:Area') || $authUser->hasRole('super_admin');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('DeleteAny:Area') || $authUser->hasRole('super_admin');
    }

    public function restore(AuthUser $authUser, Area $area): bool
    {
        return $authUser->hasPermissionTo('Restore:Area') || $authUser->hasRole('super_admin');
    }

    public function forceDelete(AuthUser $authUser, Area $area): bool
    {
        return $authUser->hasPermissionTo('ForceDelete:Area') || $authUser->hasRole('super_admin');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('ForceDeleteAny:Area') || $authUser->hasRole('super_admin');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('RestoreAny:Area') || $authUser->hasRole('super_admin');
    }

    public function replicate(AuthUser $authUser, Area $area): bool
    {
        return $authUser->hasPermissionTo('Replicate:Area') || $authUser->hasRole('super_admin');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Reorder:Area') || $authUser->hasRole('super_admin');
    }
}
