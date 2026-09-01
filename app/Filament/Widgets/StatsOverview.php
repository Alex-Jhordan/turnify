<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalTickets = Ticket::count();

        $completedSessions = Ticket::whereNotNull('ended_at')->count();

        $avgWaitSeconds = Ticket::whereNotNull('called_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, called_at)) as avg_wait')
            ->value('avg_wait') ?? 0;

        $avgSessionSeconds = Ticket::whereNotNull('ended_at')
            ->whereNotNull('started_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_at, ended_at)) as avg_session')
            ->value('avg_session') ?? 0;

        return [
            Stat::make('Total Tickets', $totalTickets)
                ->description('Registered tickets')
                ->color('primary'),

            Stat::make('Completed Sessions', $completedSessions)
                ->description('Finished sessions')
                ->color('success'),

            Stat::make('Avg Wait Time', $this->formatMinutes($avgWaitSeconds))
                ->description('Time prior to call')
                ->color('warning'),

            Stat::make('Avg Session Duration', $this->formatMinutes($avgSessionSeconds))
                ->description('Average service time')
                ->color('info'),
        ];
    }

    private function formatMinutes(float $seconds): string
    {
        $minutes = round($seconds / 60, 1);
        return "{$minutes} min";
    }
}
