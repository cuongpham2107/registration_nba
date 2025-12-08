<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Registration;
use Illuminate\Auth\Access\HandlesAuthorization;

class RegistrationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view_any_registration');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Registration $registration): bool
    {
        return $user->checkPermissionTo('view_registration');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create_registration');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Registration $registration): bool
    {
        return $user->checkPermissionTo('update_registration');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Registration $registration): bool
    {
        return $user->checkPermissionTo('delete_registration');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete_any_registration');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Registration $registration): bool
    {
        return $user->checkPermissionTo('force_delete_registration');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force_delete_any_registration');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Registration $registration): bool
    {
        return $user->checkPermissionTo('restore_registration');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore_any_registration');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Registration $registration): bool
    {
        return $user->checkPermissionTo('replicate_registration');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder_registration');
    }
}
