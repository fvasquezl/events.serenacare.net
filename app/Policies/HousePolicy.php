<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\House;
use Illuminate\Auth\Access\HandlesAuthorization;

class HousePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:House');
    }

    public function view(AuthUser $authUser, House $house): bool
    {
        return $authUser->can('View:House');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:House');
    }

    public function update(AuthUser $authUser, House $house): bool
    {
        return $authUser->can('Update:House');
    }

    public function delete(AuthUser $authUser, House $house): bool
    {
        return $authUser->can('Delete:House');
    }

    public function restore(AuthUser $authUser, House $house): bool
    {
        return $authUser->can('Restore:House');
    }

    public function forceDelete(AuthUser $authUser, House $house): bool
    {
        return $authUser->can('ForceDelete:House');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:House');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:House');
    }

    public function replicate(AuthUser $authUser, House $house): bool
    {
        return $authUser->can('Replicate:House');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:House');
    }

}