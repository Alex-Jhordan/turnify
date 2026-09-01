<?php

namespace App\Exports;

use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TicketsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(protected array $filters = [])
    {

    }

    public function query(): Builder
    {
        return Ticket::query()
            ->with([
                'category',
                'module',
                'user',
            ])
            ->when($this->filters['start_date'] ?? null, fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($this->filters['end_date'] ?? null, fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date))
            ->when($this->filters['category_id'] ?? null, fn (Builder $query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($this->filters['module_id'] ?? null, fn (Builder $query, $moduleId) => $query->where('module_id', $moduleId));
    }

    public function headings(): array
    {
        return [
            'Ticket Code',
            'Category',
            'Module',
            'Advisor',
            'Document',
            'Status',
            'Issuance Time',
            'Call Time',
            'Start Time',
            'End Time',
            'Total Session Duration',
        ];
    }

    /**
     * @param Ticket $ticket
     */
    public function map($ticket): array
    {
        $sessionDuration = '-';
        
        if ($ticket->started_at && $ticket->ended_at) {
            $seconds = Carbon::parse($ticket->started_at)->diffInSeconds(Carbon::parse($ticket->ended_at));
            $sessionDuration = gmdate('H:i:s', $seconds);
        }

        return [
            $ticket->code,
            $ticket->category?->name,
            $ticket->module?->module_number ?? '-',
            $ticket->user?->name ?? '-',
            $ticket->document_number ?? '-',
            ucfirst($ticket->status->value),
            $ticket->created_at->format('Y-m-d H:i:s'),
            $ticket->called_at ? Carbon::parse($ticket->called_at)->format('Y-m-d H:i:s') : '-',
            $ticket->started_at ? Carbon::parse($ticket->started_at)->format('Y-m-d H:i:s') : '-',
            $ticket->ended_at ? Carbon::parse($ticket->ended_at)->format('Y-m-d H:i:s') : '-',
            $sessionDuration,
        ];
    }
}
