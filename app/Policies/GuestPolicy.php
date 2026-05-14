<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Guest;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class GuestPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('ViewAny:Guest') || $authUser->hasRole('super_admin');
    }

    public function view(AuthUser $authUser, Guest $guest): bool
    {
        return $authUser->hasPermissionTo('View:Guest') || $authUser->hasRole('super_admin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Create:Guest') || $authUser->hasRole('super_admin');
    }

    public function update(AuthUser $authUser, Guest $guest): bool
    {
        return $authUser->hasPermissionTo('Update:Guest') || $authUser->hasRole('super_admin');
    }

    public function delete(AuthUser $authUser, Guest $guest): bool
    {
        return $authUser->hasPermissionTo('Delete:Guest') || $authUser->hasRole('super_admin');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('DeleteAny:Guest') || $authUser->hasRole('super_admin');
    }

    public function restore(AuthUser $authUser, Guest $guest): bool
    {
        return $authUser->hasPermissionTo('Restore:Guest') || $authUser->hasRole('super_admin');
    }

    public function forceDelete(AuthUser $authUser, Guest $guest): bool
    {
        return $authUser->hasPermissionTo('ForceDelete:Guest') || $authUser->hasRole('super_admin');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('ForceDeleteAny:Guest') || $authUser->hasRole('super_admin');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('RestoreAny:Guest') || $authUser->hasRole('super_admin');
    }

    public function replicate(AuthUser $authUser, Guest $guest): bool
    {
        return $authUser->hasPermissionTo('Replicate:Guest') || $authUser->hasRole('super_admin');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Reorder:Guest') || $authUser->hasRole('super_admin');
    }
}
