<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\VisitorVehicleFee;
use Illuminate\Auth\Access\HandlesAuthorization;

class VisitorVehicleFeePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:VisitorVehicleFee');
    }

    public function view(AuthUser $authUser, VisitorVehicleFee $visitorVehicleFee): bool
    {
        return $authUser->can('View:VisitorVehicleFee');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:VisitorVehicleFee');
    }

    public function update(AuthUser $authUser, VisitorVehicleFee $visitorVehicleFee): bool
    {
        return $authUser->can('Update:VisitorVehicleFee');
    }

    public function delete(AuthUser $authUser, VisitorVehicleFee $visitorVehicleFee): bool
    {
        return $authUser->can('Delete:VisitorVehicleFee');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:VisitorVehicleFee');
    }

    public function restore(AuthUser $authUser, VisitorVehicleFee $visitorVehicleFee): bool
    {
        return $authUser->can('Restore:VisitorVehicleFee');
    }

    public function forceDelete(AuthUser $authUser, VisitorVehicleFee $visitorVehicleFee): bool
    {
        return $authUser->can('ForceDelete:VisitorVehicleFee');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:VisitorVehicleFee');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:VisitorVehicleFee');
    }

    public function replicate(AuthUser $authUser, VisitorVehicleFee $visitorVehicleFee): bool
    {
        return $authUser->can('Replicate:VisitorVehicleFee');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:VisitorVehicleFee');
    }

}