<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Module;
use Illuminate\Auth\Access\HandlesAuthorization;

class ModulePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:module');
    }

    public function view(AuthUser $authUser, Module $module): bool
    {
        return $authUser->can('view:module');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:module');
    }

    public function update(AuthUser $authUser, Module $module): bool
    {
        return $authUser->can('update:module');
    }

    public function delete(AuthUser $authUser, Module $module): bool
    {
        return $authUser->can('delete:module');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:module');
    }

    public function restore(AuthUser $authUser, Module $module): bool
    {
        return $authUser->can('restore:module');
    }

    public function forceDelete(AuthUser $authUser, Module $module): bool
    {
        return $authUser->can('force_delete:module');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:module');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:module');
    }

    public function replicate(AuthUser $authUser, Module $module): bool
    {
        return $authUser->can('replicate:module');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:module');
    }

}