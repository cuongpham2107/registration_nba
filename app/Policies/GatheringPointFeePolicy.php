<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\GatheringPointFee;
use Illuminate\Auth\Access\HandlesAuthorization;

class GatheringPointFeePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:GatheringPointFee');
    }

    public function view(AuthUser $authUser, GatheringPointFee $gatheringPointFee): bool
    {
        return $authUser->can('View:GatheringPointFee');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:GatheringPointFee');
    }

    public function update(AuthUser $authUser, GatheringPointFee $gatheringPointFee): bool
    {
        return $authUser->can('Update:GatheringPointFee');
    }

    public function delete(AuthUser $authUser, GatheringPointFee $gatheringPointFee): bool
    {
        return $authUser->can('Delete:GatheringPointFee');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:GatheringPointFee');
    }

    public function restore(AuthUser $authUser, GatheringPointFee $gatheringPointFee): bool
    {
        return $authUser->can('Restore:GatheringPointFee');
    }

    public function forceDelete(AuthUser $authUser, GatheringPointFee $gatheringPointFee): bool
    {
        return $authUser->can('ForceDelete:GatheringPointFee');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:GatheringPointFee');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:GatheringPointFee');
    }

    public function replicate(AuthUser $authUser, GatheringPointFee $gatheringPointFee): bool
    {
        return $authUser->can('Replicate:GatheringPointFee');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:GatheringPointFee');
    }

}