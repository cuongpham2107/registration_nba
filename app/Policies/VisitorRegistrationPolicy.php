<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\VisitorRegistration;
use Illuminate\Auth\Access\HandlesAuthorization;

class VisitorRegistrationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:VisitorRegistration');
    }

    public function view(AuthUser $authUser, VisitorRegistration $visitorRegistration): bool
    {
        return $authUser->can('View:VisitorRegistration');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:VisitorRegistration');
    }

    public function update(AuthUser $authUser, VisitorRegistration $visitorRegistration): bool
    {
        return $authUser->can('Update:VisitorRegistration');
    }

    public function delete(AuthUser $authUser, VisitorRegistration $visitorRegistration): bool
    {
        return $authUser->can('Delete:VisitorRegistration');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:VisitorRegistration');
    }

    public function restore(AuthUser $authUser, VisitorRegistration $visitorRegistration): bool
    {
        return $authUser->can('Restore:VisitorRegistration');
    }

    public function forceDelete(AuthUser $authUser, VisitorRegistration $visitorRegistration): bool
    {
        return $authUser->can('ForceDelete:VisitorRegistration');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:VisitorRegistration');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:VisitorRegistration');
    }

    public function replicate(AuthUser $authUser, VisitorRegistration $visitorRegistration): bool
    {
        return $authUser->can('Replicate:VisitorRegistration');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:VisitorRegistration');
    }

}