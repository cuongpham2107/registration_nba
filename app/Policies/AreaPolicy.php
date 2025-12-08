<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Area;
use Illuminate\Auth\Access\HandlesAuthorization;

class AreaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view_any_area');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Area $area): bool
    {
        return $user->checkPermissionTo('view_area');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create_area');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Area $area): bool
    {
        return $user->checkPermissionTo('update_area');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Area $area): bool
    {
        return $user->checkPermissionTo('delete_area');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete_any_area');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Area $area): bool
    {
        return $user->checkPermissionTo('force_delete_area');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force_delete_any_area');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Area $area): bool
    {
        return $user->checkPermissionTo('restore_area');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore_any_area');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Area $area): bool
    {
        return $user->checkPermissionTo('replicate_area');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder_area');
    }
}
