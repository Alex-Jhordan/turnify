<x-filament-panels::page>
    @if (! $module)
        <x-filament::section heading="Select Module">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                Select an available module to occupy and start attending tickets.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @forelse ($availableModules as $item)
                    <button
                        type="button"
                        wire:click="selectModule({{ $item->id }})"
                        class="flex flex-col items-center justify-center p-6 border rounded-xl shadow-sm transition hover:border-primary-500 hover:ring-2 hover:ring-primary-500 bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-800 text-center group relative overflow-hidden"
                    >
                        <div class="absolute top-2 right-2">
                            <span class="inline-flex items-center rounded-md bg-emerald-50 dark:bg-emerald-500/10 px-2 py-1 text-xs font-medium text-emerald-700 dark:text-emerald-400 ring-1 ring-inset ring-emerald-600/20">
                                Available
                            </span>
                        </div>

                        <x-filament::icon
                            icon="heroicon-o-computer-desktop"
                            class="mb-3 text-gray-400 group-hover:text-primary-500 transition"
                            style="width: 3rem; height: 3rem;"
                        />

                        <span class="text-2xl font-black text-gray-900 dark:text-white">
                            Module {{ $item->module_number }}
                        </span>

                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 font-medium">
                            Click to occupy
                        </span>
                    </button>
                @empty
                    <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
                        <x-filament::icon
                            icon="heroicon-o-exclamation-triangle"
                            class="mx-auto text-gray-400 mb-2"
                            style="width: 2.5rem; height: 2.5rem;"
                        />
                        <p class="font-medium">No available modules</p>
                        <p class="text-xs mt-1">All modules are currently occupied or inactive.</p>
                    </div>
                @endforelse
            </div>
        </x-filament::section>
    @else
        <div class="space-y-6">
            <x-filament::section>
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h2 class="text-lg font-bold leading-6 text-gray-950 dark:text-white">
                            Module #{{ $module->module_number }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Advisor: {{ auth()->user()->name }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        @if ($currentTicket)
                            <x-filament::badge color="warning" icon="heroicon-m-clock">
                                In Progress
                            </x-filament::badge>
                        @else
                            <x-filament::badge color="success" icon="heroicon-m-check-circle">
                                Available
                            </x-filament::badge>
                        @endif

                        <x-filament::button
                            wire:click="leaveModule"
                            color="danger"
                            outlined
                            size="sm"
                            icon="heroicon-m-arrow-right-on-rectangle"
                        >
                            Leave Module
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section heading="Active Ticket">
                @if ($currentTicket)
                    <div class="fi-ta-content">
                        <div class="text-center">
                            <span class="text-6xl font-black tracking-tight text-primary-600 dark:text-primary-400">
                                {{ $currentTicket->code }}
                            </span>
                            <div class="mt-3 flex items-center justify-center gap-2">
                                <x-filament::badge color="gray">
                                    {{ $currentTicket->category->name ?? 'General' }}
                                </x-filament::badge>

                                @if ($currentTicket->is_priority)
                                    <x-filament::badge color="danger">
                                        Priority
                                    </x-filament::badge>
                                @endif
                            </div>

                            @if ($currentTicket->status === App\Enums\TicketStatus::InProgress)
                                <div 
                                    x-data="{
                                        startedAt: {{ $currentTicket->started_at ? $currentTicket->started_at->timestamp * 1000 : 'Date.now()' }},
                                        remainingSeconds: 1800,
                                        timer: null,
                                        formatTime() {
                                            const mins = Math.floor(Math.abs(this.remainingSeconds) / 60).toString().padStart(2, '0');
                                            const secs = (Math.abs(this.remainingSeconds) % 60).toString().padStart(2, '0');
                                            return `${this.remainingSeconds < 0 ? '-' : ''}${mins}:${secs}`;
                                        },
                                        updateTimer() {
                                            const elapsedSeconds = Math.floor((Date.now() - this.startedAt) / 1000);
                                            this.remainingSeconds = 1800 - elapsedSeconds;
                                        },
                                        get timerClass() {
                                            if (this.remainingSeconds >= 600) return 'text-emerald-600 dark:text-emerald-400 border-emerald-500/20 bg-emerald-50 dark:bg-emerald-500/10';
                                            if (this.remainingSeconds >= 180) return 'text-amber-600 dark:text-amber-400 border-amber-500/20 bg-amber-50 dark:bg-amber-500/10';
                                            return 'text-rose-600 dark:text-rose-400 border-rose-500/20 bg-rose-50 dark:bg-rose-500/10';
                                        }
                                    }"
                                    x-init="
                                        updateTimer();
                                        timer = setInterval(() => updateTimer(), 1000);
                                    "
                                    x-destroy="clearInterval(timer)"
                                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-lg border font-mono text-xl font-bold transition-colors duration-300"
                                    :class="timerClass"
                                >
                                    <x-filament::icon icon="heroicon-m-clock" class="w-5 h-5" />
                                    <span x-text="formatTime()"></span>
                                </div>
                            @endif
                        </div>

                        <div class="mt-6 border-t border-gray-100 dark:border-gray-800 pt-4 text-xs text-gray-500 dark:text-gray-400 grid grid-cols-3 gap-2 text-center">
                            <div>
                                <span class="block text-gray-400">Status</span>
                                <span class="uppercase font-semibold text-gray-700 dark:text-gray-200">
                                    {{ $currentTicket->status->getLabel() }}
                                </span>
                            </div>
                            <div>
                                <span class="block text-gray-400">Call Count</span>
                                <span class="font-semibold text-gray-700 dark:text-gray-200">
                                    {{ $currentTicket->call_count }}
                                </span>
                            </div>
                            <div>
                                <span class="block text-gray-400">Called At</span>
                                <span class="font-semibold text-gray-700 dark:text-gray-200">
                                    {{ $currentTicket->called_at ? $currentTicket->called_at->format('H:i:s') : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-filament::icon
                            icon="heroicon-o-ticket"
                            class="mx-auto text-gray-400"
                            style="width: 3rem; height: 3rem;"
                        />
                        <p class="mt-2 text-base font-semibold text-gray-900 dark:text-white">No active ticket being served</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Click "Call Next" to request a new ticket from the queue.</p>
                    </div>
                @endif
            </x-filament::section>

            <div 
                x-data="{
                    calledAt: {{ ($currentTicket && $currentTicket->called_at) ? $currentTicket->called_at->timestamp * 1000 : 'null' }},
                    lockRemaining: 0,
                    lockTimer: null,
                    updateLock() {
                        if (!this.calledAt) {
                            this.lockRemaining = 0;
                            return;
                        }
                        const elapsed = Math.floor((Date.now() - this.calledAt) / 1000);
                        this.lockRemaining = Math.max(0, 20 - elapsed);
                    }
                }"
                x-init="
                    updateLock();
                    lockTimer = setInterval(() => updateLock(), 1000);
                "
                x-destroy="clearInterval(lockTimer)"
                class="flex items-center gap-3"
            >
                @if (! $currentTicket)
                    <x-filament::button wire:click="callNext" icon="heroicon-m-phone-arrow-up-right" size="lg">
                        Call Next
                    </x-filament::button>
                @else
                    @if ($currentTicket->status === App\Enums\TicketStatus::Calling)
                        <x-filament::button wire:click="recall" color="warning" icon="heroicon-m-arrow-path">
                            Recall Ticket
                        </x-filament::button>

                        <x-filament::button wire:click="startAttention" color="success" icon="heroicon-m-play">
                            Start Service
                        </x-filament::button>

                        <x-filament::button 
                            wire:click="markNoShow" 
                            color="danger" 
                            icon="heroicon-m-x-circle"
                            ::disabled="lockRemaining > 0"
                            x-bind:class="{ 'opacity-50 cursor-not-allowed': lockRemaining > 0 }"
                        >
                            <span x-show="lockRemaining === 0">No Show</span>
                            <span x-show="lockRemaining > 0" x-text="`No Show (${lockRemaining}s)`"></span>
                        </x-filament::button>
                    @elseif ($currentTicket->status === App\Enums\TicketStatus::InProgress)
                        <x-filament::button wire:click="completeAttention" color="info" icon="heroicon-m-check">
                            Complete Service
                        </x-filament::button>
                    @endif
                @endif
            </div>
        </div>
    @endif
</x-filament-panels::page>