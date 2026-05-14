<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Card;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CardPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('ViewAny:Card') || $authUser->hasRole('super_admin');
    }

    public function view(AuthUser $authUser, Card $card): bool
    {
        return $authUser->hasPermissionTo('View:Card') || $authUser->hasRole('super_admin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Create:Card') || $authUser->hasRole('super_admin');
    }

    public function update(AuthUser $authUser, Card $card): bool
    {
        return $authUser->hasPermissionTo('Update:Card') || $authUser->hasRole('super_admin');
    }

    public function delete(AuthUser $authUser, Card $card): bool
    {
        return $authUser->hasPermissionTo('Delete:Card') || $authUser->hasRole('super_admin');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('DeleteAny:Card') || $authUser->hasRole('super_admin');
    }

    public function restore(AuthUser $authUser, Card $card): bool
    {
        return $authUser->hasPermissionTo('Restore:Card') || $authUser->hasRole('super_admin');
    }

    public function forceDelete(AuthUser $authUser, Card $card): bool
    {
        return $authUser->hasPermissionTo('ForceDelete:Card') || $authUser->hasRole('super_admin');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('ForceDeleteAny:Card') || $authUser->hasRole('super_admin');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('RestoreAny:Card') || $authUser->hasRole('super_admin');
    }

    public function replicate(AuthUser $authUser, Card $card): bool
    {
        return $authUser->hasPermissionTo('Replicate:Card') || $authUser->hasRole('super_admin');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Reorder:Card') || $authUser->hasRole('super_admin');
    }
}
