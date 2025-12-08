<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Card;
use Illuminate\Auth\Access\HandlesAuthorization;

class CardPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view_any_card');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Card $card): bool
    {
        return $user->checkPermissionTo('view_card');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create_card');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Card $card): bool
    {
        return $user->checkPermissionTo('update_card');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Card $card): bool
    {
        return $user->checkPermissionTo('delete_card');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete_any_card');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Card $card): bool
    {
        return $user->checkPermissionTo('force_delete_card');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force_delete_any_card');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Card $card): bool
    {
        return $user->checkPermissionTo('restore_card');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore_any_card');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Card $card): bool
    {
        return $user->checkPermissionTo('replicate_card');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder_card');
    }
}
