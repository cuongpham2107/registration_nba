<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LiftingServiceFee;
use Illuminate\Auth\Access\HandlesAuthorization;

class LiftingServiceFeePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LiftingServiceFee');
    }

    public function view(AuthUser $authUser, LiftingServiceFee $liftingServiceFee): bool
    {
        return $authUser->can('View:LiftingServiceFee');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LiftingServiceFee');
    }

    public function update(AuthUser $authUser, LiftingServiceFee $liftingServiceFee): bool
    {
        return $authUser->can('Update:LiftingServiceFee');
    }

    public function delete(AuthUser $authUser, LiftingServiceFee $liftingServiceFee): bool
    {
        return $authUser->can('Delete:LiftingServiceFee');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LiftingServiceFee');
    }

    public function restore(AuthUser $authUser, LiftingServiceFee $liftingServiceFee): bool
    {
        return $authUser->can('Restore:LiftingServiceFee');
    }

    public function forceDelete(AuthUser $authUser, LiftingServiceFee $liftingServiceFee): bool
    {
        return $authUser->can('ForceDelete:LiftingServiceFee');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LiftingServiceFee');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LiftingServiceFee');
    }

    public function replicate(AuthUser $authUser, LiftingServiceFee $liftingServiceFee): bool
    {
        return $authUser->can('Replicate:LiftingServiceFee');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LiftingServiceFee');
    }

}