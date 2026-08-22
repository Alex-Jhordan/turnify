<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Category;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:category');
    }

    public function view(AuthUser $authUser, Category $category): bool
    {
        return $authUser->can('view:category');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:category');
    }

    public function update(AuthUser $authUser, Category $category): bool
    {
        return $authUser->can('update:category');
    }

    public function delete(AuthUser $authUser, Category $category): bool
    {
        return $authUser->can('delete:category');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:category');
    }

    public function restore(AuthUser $authUser, Category $category): bool
    {
        return $authUser->can('restore:category');
    }

    public function forceDelete(AuthUser $authUser, Category $category): bool
    {
        return $authUser->can('force_delete:category');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:category');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:category');
    }

    public function replicate(AuthUser $authUser, Category $category): bool
    {
        return $authUser->can('replicate:category');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:category');
    }

}