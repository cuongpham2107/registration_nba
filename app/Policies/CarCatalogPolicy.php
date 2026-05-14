<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CarCatalog;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CarCatalogPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('ViewAny:CarCatalog') || $authUser->hasRole('super_admin');
    }

    public function view(AuthUser $authUser, CarCatalog $carCatalog): bool
    {
        return $authUser->hasPermissionTo('View:CarCatalog') || $authUser->hasRole('super_admin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Create:CarCatalog') || $authUser->hasRole('super_admin');
    }

    public function update(AuthUser $authUser, CarCatalog $carCatalog): bool
    {
        return $authUser->hasPermissionTo('Update:CarCatalog') || $authUser->hasRole('super_admin');
    }

    public function delete(AuthUser $authUser, CarCatalog $carCatalog): bool
    {
        return $authUser->hasPermissionTo('Delete:CarCatalog') || $authUser->hasRole('super_admin');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('DeleteAny:CarCatalog') || $authUser->hasRole('super_admin');
    }

    public function restore(AuthUser $authUser, CarCatalog $carCatalog): bool
    {
        return $authUser->hasPermissionTo('Restore:CarCatalog') || $authUser->hasRole('super_admin');
    }

    public function forceDelete(AuthUser $authUser, CarCatalog $carCatalog): bool
    {
        return $authUser->hasPermissionTo('ForceDelete:CarCatalog') || $authUser->hasRole('super_admin');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('ForceDeleteAny:CarCatalog') || $authUser->hasRole('super_admin');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('RestoreAny:CarCatalog') || $authUser->hasRole('super_admin');
    }

    public function replicate(AuthUser $authUser, CarCatalog $carCatalog): bool
    {
        return $authUser->hasPermissionTo('Replicate:CarCatalog') || $authUser->hasRole('super_admin');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Reorder:CarCatalog') || $authUser->hasRole('super_admin');
    }
}
