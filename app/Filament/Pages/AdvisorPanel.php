<?php

namespace App\Filament\Pages;

use App\Enums\TicketStatus;
use App\Models\Module;
use App\Models\Ticket;
use App\Services\TicketAssignmentService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class AdvisorPanel extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::ComputerDesktop;

    protected string $view = 'filament.pages.advisor-panel';

    public ?Module $module = null;

    public ?Ticket $currentTicket = null;

    public Collection $availableModules;

    public function mount(): void
    {
        $this->availableModules = collect();
        $this->module = Auth::user()->module;

        if ($this->module) {
            $this->loadCurrentTicket();
        } else {
            $this->loadAvailableModules();
        }
    }

    public function loadAvailableModules(): void
    {
        $this->availableModules = Module::where('is_active', true)
            ->whereNull('current_user_id')
            ->orderBy('module_number')
            ->get();
    }

    public function selectModule(int $moduleId): void
    {
        $userId = Auth::id();

        try {
            DB::transaction(function () use ($moduleId, $userId) {
                $alreadyHasModule = Module::where('current_user_id', $userId)->exists();

                if ($alreadyHasModule) {
                    throw new Exception('USER_ALREADY_ASSIGNED');
                }

                $selectedModule = Module::where('id', $moduleId)
                    ->where('is_active', true)
                    ->whereNull('current_user_id')
                    ->lockForUpdate()
                    ->first();

                if (! $selectedModule) {
                    throw new Exception('MODULE_UNAVAILABLE');
                }

                if ($selectedModule->current_user_id !== null && $selectedModule->current_user_id !== $userId) {
                    throw new Exception('MODULE_OCCUPIED');
                }

                $selectedModule->update([
                    'current_user_id' => $userId,
                ]);

                $this->module = $selectedModule;
            });

            $this->loadCurrentTicket();

            Notification::make()
                ->success()
                ->title("Assigned to Module #{$this->module->module_number}")
                ->send();

        } catch (QueryException $e) {
            $this->loadAvailableModules();

            Notification::make()
                ->warning()
                ->title('Module occupied')
                ->body('Another advisor just selected this module.')
                ->send();

        } catch (Exception $e) {
            $this->loadAvailableModules();

            match ($e->getMessage()) {
                'USER_ALREADY_ASSIGNED' => Notification::make()
                    ->warning()
                    ->title('Module already assigned')
                    ->body('You are already assigned to a module.')
                    ->send(),

                'MODULE_UNAVAILABLE' => Notification::make()
                    ->danger()
                    ->title('Module unavailable')
                    ->send(),

                'MODULE_OCCUPIED' => Notification::make()
                    ->warning()
                    ->title('Module occupied')
                    ->body('This module is currently occupied by another advisor.')
                    ->send(),

                default => Notification::make()
                    ->danger()
                    ->title('An error occurred while selecting the module.')
                    ->send(),
            };
        }
    }

    public function leaveModule(): void
    {
        if ($this->currentTicket) {
            Notification::make()
                ->warning()
                ->title('Cannot leave module')
                ->body('Please finish or resolve the active ticket before leaving the module.')
                ->send();

            return;
        }

        if ($this->module) {
            Module::where('id', $this->module->id)
                ->where('current_user_id', Auth::id())
                ->update(['current_user_id' => null]);
        }

        $this->module = null;
        $this->currentTicket = null;
        $this->loadAvailableModules();

        Notification::make()
            ->info()
            ->title('Module released')
            ->send();
    }

    public function loadCurrentTicket(): void
    {
        if (! $this->module) {
            return;
        }

        $this->currentTicket = Ticket::where('module_id', $this->module->id)
            ->whereIn('status', [TicketStatus::Calling, TicketStatus::InProgress])
            ->first();
    }

    public function callNext(TicketAssignmentService $service): void
    {
        if (! $this->module) {
            Notification::make()
                ->warning()
                ->title('No assigned module')
                ->body('You must select a module to start serving tickets.')
                ->send();

            return;
        }

        $ticket = $service->callNextTicket($this->module, Auth::user());

        if ($ticket) {
            $this->currentTicket = $ticket;
            Notification::make()
                ->success()
                ->title("Ticket {$ticket->code} called")
                ->send();
        } else {
            Notification::make()
                ->info()
                ->title('No pending tickets available')
                ->send();
        }
    }

    public function recall(): void
    {
        if (! $this->currentTicket || $this->currentTicket->status !== TicketStatus::Calling) {
            return;
        }

        $this->currentTicket->increment('call_count');
        $this->currentTicket->update(['called_at' => now()]);

        Notification::make()
            ->info()
            ->title("Recalling ticket {$this->currentTicket->code}")
            ->send();
    }

    public function startAttention(): void
    {
        if (! $this->currentTicket || $this->currentTicket->status !== TicketStatus::Calling) {
            return;
        }

        $this->currentTicket->update([
            'status' => TicketStatus::InProgress,
            'started_at' => now(),
        ]);

        Notification::make()
            ->success()
            ->title('Service started')
            ->send();
    }

    public function markNoShow(): void
    {
        if (! $this->currentTicket || $this->currentTicket->status !== TicketStatus::Calling) {
            return;
        }

        $this->currentTicket->update([
            'status' => TicketStatus::NoShow,
            'cancelled_at' => now(),
        ]);

        Notification::make()
            ->warning()
            ->title('Ticket marked as "No Show"')
            ->send();

        $this->currentTicket = null;
    }

    public function completeAttention(): void
    {
        if (! $this->currentTicket || $this->currentTicket->status !== TicketStatus::InProgress) {
            return;
        }

        $this->currentTicket->update([
            'status' => TicketStatus::Completed,
            'ended_at' => now(),
        ]);

        Notification::make()
            ->success()
            ->title('Service completed')
            ->send();

        $this->currentTicket = null;
    }
}
