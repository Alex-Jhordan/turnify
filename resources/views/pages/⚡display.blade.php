<?php

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Title('Display | Turnify')] #[Layout('layouts::app')] class extends Component {
    public ?array $currentTicket = null;
    public $history = [];

    public function mount(): void
    {
        $this->loadInitialState();
    }

    public function getListeners()
    {
        return [
            "echo:displays-channel,TicketCalledEvent" => 'onTicketCalled',
            "echo:displays-channel,TicketStatusUpdatedEvent" => 'onTicketStatusUpdated',
        ];
    }

    protected function loadInitialState(): void
    {
        $latestCalled = Ticket::with(['category', 'module'])
            ->where('status', TicketStatus::Calling)
            ->whereNotNull('called_at')
            ->orderByDesc('called_at')
            ->first();

        if ($latestCalled) {
            $this->currentTicket = [
                'ticketId' => $latestCalled->id,
                'ticketCode' => $latestCalled->code,
                'moduleNumber' => $latestCalled->module?->module_number,
                'categoryName' => $latestCalled->category?->name,
                'isRecall' => $latestCalled->call_count > 1,
            ];
        }

        $this->history = Ticket::with(['category', 'module'])
            ->where('status', TicketStatus::Calling)
            ->whereNotNull('called_at')
            ->when($latestCalled, fn ($query) => $query->where('id', '!=', $latestCalled->id))
            ->orderByDesc('called_at')
            ->take(5)
            ->get()
            ->map(fn (Ticket $ticket) => [
                'ticketId' => $ticket->id,
                'ticketCode' => $ticket->code,
                'moduleNumber' => $ticket->module?->module_number,
                'categoryName' => $ticket->category?->name,
                'isRecall' => $ticket->call_count > 1,
            ])
            ->toArray();
    }

    public function onTicketCalled(array $event)
    {
        $incomingTicketId = $event['ticketId'];

        if ($this->currentTicket && $this->currentTicket['ticketId'] !== $incomingTicketId) {
            $this->history = array_filter(
                $this->history, 
                fn ($item) => $item['ticketId'] !== $this->currentTicket['ticketId']
            );

            array_unshift($this->history, $this->currentTicket);
            $this->history = array_slice($this->history, 0, 5);
        }

        $this->currentTicket = [
            'ticketId' => $event['ticketId'],
            'ticketCode' => $event['ticketCode'],
            'moduleNumber' => $event['moduleNumber'],
            'categoryName' => $event['categoryName'],
            'isRecall' => $event['isRecall'],
        ];

        $this->dispatch('announce-current-ticket', 
            ticketCode: $event['ticketCode'],
            moduleNumber: $event['moduleNumber'],
        );
    }

    public function onTicketStatusUpdated(array $event): void
    {
        $ticketId = $event['ticketId'];
        $newStatus = $event['status'];

        if ($newStatus !== TicketStatus::Calling) {
            if ($this->currentTicket && $this->currentTicket['ticketId'] === $ticketId) {
                $this->currentTicket = null;
            }

            $this->history = array_values(array_filter(
                $this->history,
                fn ($item) => $item['ticketId'] !== $ticketId
            ));
        }
    }
};
?>

<div
    x-data="{ audioEnabled: false }" 
    x-on:click="
        if (!audioEnabled) {
            audioEnabled = true;
            window.speechSynthesis?.speak(new SpeechSynthesisUtterance(''));
        }
    "
    x-on:announce-current-ticket.window="
        const data = $event.detail;
        if (typeof window.playTicketAnnouncement === 'function') {
            window.playTicketAnnouncement(data.ticketCode, data.moduleNumber);
        }
    "
    class="min-h-screen w-screen overflow-hidden bg-[#f4f1ea] text-[#17211d] font-sans selection:bg-[#f2c14e] selection:text-[#17211d]">
    <div 
        x-show="!audioEnabled" 
        x-transition 
        class="fixed top-4 right-4 z-50 rounded-lg bg-[#b45f3c] px-4 py-3 text-white shadow-xl flex items-center gap-3 cursor-pointer"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
        </svg>
        <span class="text-sm font-semibold">Haz clic aquí para activar el audio de la pantalla</span>
    </div>
    <div class="flex min-h-screen flex-col px-5 py-5 sm:px-8 sm:py-7 lg:px-12 lg:py-8">
        <header class="mb-6 flex items-end justify-between border-b border-[#17211d]/15 pb-5 sm:mb-8">
            <div>
                <div class="mb-2 flex items-center gap-3 text-xs font-bold uppercase tracking-[0.25em] text-[#b45f3c]">
                    <span class="h-2 w-2 rounded-full bg-[#4c956c] shadow-[0_0_12px_rgba(76,149,108,0.8)]"></span>
                    Now Serving
                </div>
                <h1 class="text-3xl font-black tracking-[-0.04em] text-[#17211d] sm:text-5xl">TURNIFY<span class="text-[#b45f3c]">.</span></h1>
            </div>
            <time id="clock" class="text-right text-xl font-semibold tabular-nums text-[#17211d]/70 sm:text-3xl">--:--:--</time>
        </header>

        <main class="grid min-h-0 flex-1 grid-cols-1 gap-5 lg:grid-cols-10 lg:gap-8">
            <section class="relative flex min-h-100 flex-col justify-center overflow-hidden border border-[#17211d]/10 bg-[#fffdf8] px-6 py-12 text-center text-[#17211d] shadow-[10px_10px_0_#f2c14e] sm:px-12 lg:col-span-7 lg:min-h-0 lg:px-16">
                <div class="absolute left-0 top-0 h-3 w-32 bg-[#f2c14e] sm:w-48"></div>
                <div class="absolute right-8 top-7 text-xs font-bold uppercase tracking-[0.25em] text-[#17211d]/55">Next Call</div>

                @if($currentTicket)
                    <div class="relative space-y-5 sm:space-y-7">
                        @if($currentTicket['isRecall'])
                            <span class="inline-flex items-center border border-[#b45f3c] bg-[#b45f3c]/10 px-4 py-2 text-xs font-black uppercase tracking-[0.2em] text-[#b45f3c]">Recall</span>
                        @else
                            <span class="inline-flex items-center border border-[#17211d]/15 bg-[#f4f1ea] px-4 py-2 text-xs font-black uppercase tracking-[0.2em] text-[#17211d]/65">Current Ticket</span>
                        @endif
                        <p class="truncate text-lg font-bold uppercase tracking-[0.16em] text-[#17211d]/60 sm:text-2xl">{{ $currentTicket['categoryName'] ?: 'General Service' }}</p>
                        <div class="wrap-break-word text-[clamp(4.5rem,13vw,9rem)] font-black leading-[0.8] tracking-[-0.08em] text-[#17211d]">{{ $currentTicket['ticketCode'] }}</div>
                        <div class="mx-auto flex w-fit items-center gap-3 bg-[#17211d] px-6 py-3 text-xl font-black uppercase tracking-[0.12em] text-[#f2c14e] sm:px-8 sm:py-4 sm:text-3xl">
                            <span class="text-sm font-bold text-[#f4f1ea]/75 sm:text-base">Counter</span>
                            {{ $currentTicket['moduleNumber'] }}
                        </div>
                    </div>
                @else
                    <div class="mx-auto max-w-md">
                        <div class="mb-6 text-6xl font-black text-[#17211d]/20">--</div>
                        <p class="text-2xl font-bold uppercase tracking-[0.12em] text-[#17211d]/70 sm:text-3xl">Waiting for Calls</p>
                        <p class="mt-3 text-sm text-[#17211d]/55">Tickets will appear here</p>
                    </div>
                @endif
            </section>

            <aside class="flex min-h-0 flex-col border border-[#17211d]/10 bg-[#fffdf8] px-5 py-6 sm:px-7 lg:col-span-3">
                <div class="mb-5 flex items-end justify-between border-b border-[#17211d]/15 pb-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#b45f3c]">History</p>
                        <h2 class="mt-1 text-2xl font-black tracking-[-0.03em] text-[#17211d] sm:text-3xl">Last Tickets Called</h2>
                    </div>
                    <span class="inline-flex min-w-10 items-center justify-center rounded-full border border-[#17211d]/15 bg-[#f4f1ea] px-2 py-1 text-xs font-black text-[#17211d]">
                        {{ str_pad(min(count($history), 5), 2, '0', STR_PAD_LEFT) }}
                    </span>
                </div>
                <div class="flex min-h-0 flex-1 flex-col gap-3">
                    @forelse(array_slice($history, 0, 5) as $item)
                        <div class="flex items-center justify-between rounded-lg border border-[#17211d]/10 bg-[#f4f1ea] p-3">
                            <div class="min-w-0 pr-3">
                                <div class="truncate text-xl font-black tracking-tight text-[#17211d] sm:text-2xl">{{ $item['ticketCode'] }}</div>
                                <div class="mt-1 truncate text-[10px] font-bold uppercase tracking-[0.12em] text-[#17211d]/65">{{ $item['categoryName'] }}</div>
                            </div>
                            <div class="shrink-0 border border-[#17211d]/15 bg-[#fffdf8] px-3 py-2 text-base font-black tabular-nums text-[#17211d] sm:text-xl">C-{{ $item['moduleNumber'] }}</div>
                        </div>
                    @empty
                        <div class="flex flex-1 items-center justify-center text-center text-sm font-semibold uppercase tracking-[0.12em] text-[#17211d]/55">No Last Tickets Called</div>
                    @endforelse
                </div>
            </aside>
        </main>

        <footer class="pt-5 text-center text-[10px] font-bold uppercase tracking-[0.24em] text-[#17211d]/60 sm:text-left">Queue Management System</footer>
    </div>

    <script>
        const updateClock = () => {
            const clock = document.getElementById('clock');
            if (clock) clock.innerText = new Date().toLocaleTimeString('es-PE', { hour12: false });
        };
        updateClock();
        setInterval(updateClock, 1000);
    </script>
</div>