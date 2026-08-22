<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Module;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TicketAssignmentService
{
    public function callNextTicket(Module $module, User $user): ?Ticket
    {
        return DB::transaction(function () use ($module, $user) {
            $permittedCategoryIds = $module->categories()->pluck('categories.id');

            if ($permittedCategoryIds->isEmpty()) {
                return null;
            }

            $ticket = Ticket::where('status', 'pending')
                ->whereIn('category_id', $permittedCategoryIds)
                ->orderBy('is_priority', 'desc')
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->first();

            if (! $ticket) {
                return null;
            }

            $ticket->update([
                'status' => TicketStatus::Calling,
                'module_id' => $module->id,
                'user_id' => $user->id,
                'called_at' => now(),
                'call_count' => 1,
            ]);

            return $ticket;
        });
    }
}
