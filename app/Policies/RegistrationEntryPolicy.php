<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RegistrationEntry;
use Illuminate\Auth\Access\HandlesAuthorization;

class RegistrationEntryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RegistrationEntry');
    }

    public function view(AuthUser $authUser, RegistrationEntry $registrationEntry): bool
    {
        return $authUser->can('View:RegistrationEntry');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RegistrationEntry');
    }

    public function update(AuthUser $authUser, RegistrationEntry $registrationEntry): bool
    {
        return $authUser->can('Update:RegistrationEntry');
    }

    public function delete(AuthUser $authUser, RegistrationEntry $registrationEntry): bool
    {
        return $authUser->can('Delete:RegistrationEntry');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RegistrationEntry');
    }

    public function restore(AuthUser $authUser, RegistrationEntry $registrationEntry): bool
    {
        return $authUser->can('Restore:RegistrationEntry');
    }

    public function forceDelete(AuthUser $authUser, RegistrationEntry $registrationEntry): bool
    {
        return $authUser->can('ForceDelete:RegistrationEntry');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RegistrationEntry');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RegistrationEntry');
    }

    public function replicate(AuthUser $authUser, RegistrationEntry $registrationEntry): bool
    {
        return $authUser->can('Replicate:RegistrationEntry');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RegistrationEntry');
    }

}