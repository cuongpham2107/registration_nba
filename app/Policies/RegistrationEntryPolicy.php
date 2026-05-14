<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RegistrationEntry;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class RegistrationEntryPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('ViewAny:RegistrationEntry') || $authUser->hasRole('super_admin');
    }

    public function view(AuthUser $authUser, RegistrationEntry $registrationEntry): bool
    {
        return $authUser->hasPermissionTo('View:RegistrationEntry') || $authUser->hasRole('super_admin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Create:RegistrationEntry') || $authUser->hasRole('super_admin');
    }

    public function update(AuthUser $authUser, RegistrationEntry $registrationEntry): bool
    {
        return $authUser->hasPermissionTo('Update:RegistrationEntry') || $authUser->hasRole('super_admin');
    }

    public function delete(AuthUser $authUser, RegistrationEntry $registrationEntry): bool
    {
        return $authUser->hasPermissionTo('Delete:RegistrationEntry') || $authUser->hasRole('super_admin');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('DeleteAny:RegistrationEntry') || $authUser->hasRole('super_admin');
    }

    public function restore(AuthUser $authUser, RegistrationEntry $registrationEntry): bool
    {
        return $authUser->hasPermissionTo('Restore:RegistrationEntry') || $authUser->hasRole('super_admin');
    }

    public function forceDelete(AuthUser $authUser, RegistrationEntry $registrationEntry): bool
    {
        return $authUser->hasPermissionTo('ForceDelete:RegistrationEntry') || $authUser->hasRole('super_admin');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('ForceDeleteAny:RegistrationEntry') || $authUser->hasRole('super_admin');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('RestoreAny:RegistrationEntry') || $authUser->hasRole('super_admin');
    }

    public function replicate(AuthUser $authUser, RegistrationEntry $registrationEntry): bool
    {
        return $authUser->hasPermissionTo('Replicate:RegistrationEntry') || $authUser->hasRole('super_admin');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Reorder:RegistrationEntry') || $authUser->hasRole('super_admin');
    }
}
