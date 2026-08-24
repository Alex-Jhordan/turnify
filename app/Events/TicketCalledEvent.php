<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketCalledEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int|string $ticketId;
    public string $ticketCode;
    public int|string $moduleNumber;
    public string $categoryName;
    public bool $isRecall;

    /**
     * Create a new event instance.
     */
    public function __construct(
        int|string $ticketId,
        string $ticketCode,
        int|string $moduleNumber,
        string $categoryName,
        bool $isRecall = false
    ) {
        $this->ticketId = $ticketId;
        $this->ticketCode = $ticketCode;
        $this->moduleNumber = $moduleNumber;
        $this->categoryName = $categoryName;
        $this->isRecall = $isRecall;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn(): Channel
    {
        return new Channel('displays-channel');
    }
}
