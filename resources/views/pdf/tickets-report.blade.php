<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tickets Report - {{ config('app.name', 'Turnify') }} </title>
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 20px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h2>Tickets Analytics Report - {{ config('app.name', 'Turnify') }} </h2>
    <p><strong>Generated at:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
    
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Category</th>
                <th>Module</th>
                <th>Advisor</th>
                <th>Status</th>
                <th>Issued</th>
                <th>Duration</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $ticket)
                <tr>
                    <td>{{ $ticket->code }}</td>
                    <td>{{ $ticket->category?->name }}</td>
                    <td>{{ $ticket->module?->module_number ?? '-' }}</td>
                    <td>{{ $ticket->user?->name ?? '-' }}</td>
                    <td>{{ ucfirst($ticket->status->value) }}</td>
                    <td>{{ $ticket->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        @if($ticket->started_at && $ticket->ended_at)
                            {{ gmdate('H:i:s', \Carbon\Carbon::parse($ticket->started_at)->diffInSeconds(\Carbon\Carbon::parse($ticket->ended_at))) }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No records found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
