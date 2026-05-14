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
        return $authUser->can('ViewAny:CarCatalog');
    }

    public function view(AuthUser $authUser, CarCatalog $carCatalog): bool
    {
        return $authUser->can('View:CarCatalog');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CarCatalog');
    }

    public function update(AuthUser $authUser, CarCatalog $carCatalog): bool
    {
        return $authUser->can('Update:CarCatalog');
    }

    public function delete(AuthUser $authUser, CarCatalog $carCatalog): bool
    {
        return $authUser->can('Delete:CarCatalog');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:CarCatalog');
    }

    public function restore(AuthUser $authUser, CarCatalog $carCatalog): bool
    {
        return $authUser->can('Restore:CarCatalog');
    }

    public function forceDelete(AuthUser $authUser, CarCatalog $carCatalog): bool
    {
        return $authUser->can('ForceDelete:CarCatalog');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CarCatalog');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CarCatalog');
    }

    public function replicate(AuthUser $authUser, CarCatalog $carCatalog): bool
    {
        return $authUser->can('Replicate:CarCatalog');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CarCatalog');
    }
}
