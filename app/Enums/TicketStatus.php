<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Pending = 'pending';
    case Calling = 'calling';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case NoShow = 'no_show';
    case Cancelled = 'cancelled';
}
