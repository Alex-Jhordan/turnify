<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TicketStatus: string implements HasLabel
{
    case Pending = 'pending';
    case Calling = 'calling';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case NoShow = 'no_show';
    case Cancelled = 'cancelled';

    public function getLabel(): string | Htmlable | null
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Calling => 'Calling',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::NoShow => 'No Show',
            self::Cancelled => 'Cancelled',
        };
    }
}
