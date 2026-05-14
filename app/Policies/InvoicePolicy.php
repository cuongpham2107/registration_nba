<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Invoice;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class InvoicePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('ViewAny:Invoice') || $authUser->hasRole('super_admin');
    }

    public function view(AuthUser $authUser, Invoice $invoice): bool
    {
        return $authUser->hasPermissionTo('View:Invoice') || $authUser->hasRole('super_admin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Create:Invoice') || $authUser->hasRole('super_admin');
    }

    public function update(AuthUser $authUser, Invoice $invoice): bool
    {
        return $authUser->hasPermissionTo('Update:Invoice') || $authUser->hasRole('super_admin');
    }

    public function delete(AuthUser $authUser, Invoice $invoice): bool
    {
        return $authUser->hasPermissionTo('Delete:Invoice') || $authUser->hasRole('super_admin');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('DeleteAny:Invoice') || $authUser->hasRole('super_admin');
    }

    public function restore(AuthUser $authUser, Invoice $invoice): bool
    {
        return $authUser->hasPermissionTo('Restore:Invoice') || $authUser->hasRole('super_admin');
    }

    public function forceDelete(AuthUser $authUser, Invoice $invoice): bool
    {
        return $authUser->hasPermissionTo('ForceDelete:Invoice') || $authUser->hasRole('super_admin');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('ForceDeleteAny:Invoice') || $authUser->hasRole('super_admin');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('RestoreAny:Invoice') || $authUser->hasRole('super_admin');
    }

    public function replicate(AuthUser $authUser, Invoice $invoice): bool
    {
        return $authUser->hasPermissionTo('Replicate:Invoice') || $authUser->hasRole('super_admin');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->hasPermissionTo('Reorder:Invoice') || $authUser->hasRole('super_admin');
    }
}
