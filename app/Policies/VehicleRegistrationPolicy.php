<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\VehicleRegistration;
use Illuminate\Auth\Access\HandlesAuthorization;

class VehicleRegistrationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:VehicleRegistration');
    }

    public function view(AuthUser $authUser, VehicleRegistration $vehicleRegistration): bool
    {
        return $authUser->can('View:VehicleRegistration');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:VehicleRegistration');
    }

    public function update(AuthUser $authUser, VehicleRegistration $vehicleRegistration): bool
    {
        return $authUser->can('Update:VehicleRegistration');
    }

    public function delete(AuthUser $authUser, VehicleRegistration $vehicleRegistration): bool
    {
        return $authUser->can('Delete:VehicleRegistration');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:VehicleRegistration');
    }

    public function restore(AuthUser $authUser, VehicleRegistration $vehicleRegistration): bool
    {
        return $authUser->can('Restore:VehicleRegistration');
    }

    public function forceDelete(AuthUser $authUser, VehicleRegistration $vehicleRegistration): bool
    {
        return $authUser->can('ForceDelete:VehicleRegistration');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:VehicleRegistration');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:VehicleRegistration');
    }

    public function replicate(AuthUser $authUser, VehicleRegistration $vehicleRegistration): bool
    {
        return $authUser->can('Replicate:VehicleRegistration');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:VehicleRegistration');
    }

}