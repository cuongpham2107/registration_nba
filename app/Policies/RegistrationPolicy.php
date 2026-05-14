<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Registration;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class RegistrationPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('ViewAny:Registration') || $authUser->hasRole('super_admin');
    }

    public function view(AuthUser $authUser, Registration $registration): bool
    {
        return $authUser->hasPermissionTo('View:Registration') || $authUser->hasRole('super_admin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Create:Registration') || $authUser->hasRole('super_admin');
    }

    public function update(AuthUser $authUser, Registration $registration): bool
    {
        return $authUser->hasPermissionTo('Update:Registration') || $authUser->hasRole('super_admin');
    }

    public function delete(AuthUser $authUser, Registration $registration): bool
    {
        return $authUser->hasPermissionTo('Delete:Registration') || $authUser->hasRole('super_admin');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('DeleteAny:Registration') || $authUser->hasRole('super_admin');
    }

    public function restore(AuthUser $authUser, Registration $registration): bool
    {
        return $authUser->hasPermissionTo('Restore:Registration') || $authUser->hasRole('super_admin');
    }

    public function forceDelete(AuthUser $authUser, Registration $registration): bool
    {
        return $authUser->hasPermissionTo('ForceDelete:Registration') || $authUser->hasRole('super_admin');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('ForceDeleteAny:Registration') || $authUser->hasRole('super_admin');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('RestoreAny:Registration') || $authUser->hasRole('super_admin');
    }

    public function replicate(AuthUser $authUser, Registration $registration): bool
    {
        return $authUser->hasPermissionTo('Replicate:Registration') || $authUser->hasRole('super_admin');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Reorder:Registration') || $authUser->hasRole('super_admin');
    }

    public function approver(AuthUser $authUser, Registration $registration): bool
    {
        return $authUser->hasPermissionTo('Approver:Registration') || $authUser->hasRole('super_admin');
    }

    public function sendEmail(AuthUser $authUser, Registration $registration): bool
    {
        return $authUser->hasPermissionTo('SendEmail:Registration') || $authUser->hasRole('super_admin');
    }
}
