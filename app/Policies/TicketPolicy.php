<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Ticket;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:ticket');
    }

    public function view(AuthUser $authUser, Ticket $ticket): bool
    {
        return $authUser->can('view:ticket');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:ticket');
    }

    public function update(AuthUser $authUser, Ticket $ticket): bool
    {
        return $authUser->can('update:ticket');
    }

    public function delete(AuthUser $authUser, Ticket $ticket): bool
    {
        return $authUser->can('delete:ticket');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:ticket');
    }

    public function restore(AuthUser $authUser, Ticket $ticket): bool
    {
        return $authUser->can('restore:ticket');
    }

    public function forceDelete(AuthUser $authUser, Ticket $ticket): bool
    {
        return $authUser->can('force_delete:ticket');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:ticket');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:ticket');
    }

    public function replicate(AuthUser $authUser, Ticket $ticket): bool
    {
        return $authUser->can('replicate:ticket');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:ticket');
    }

}