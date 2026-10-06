<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketListUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticketId;
    public $status;
    public $action;

    public function __construct(int $ticketId, string $status, string $action = 'updated')
    {
        $this->ticketId = $ticketId;
        $this->status = $status;
        $this->action = $action;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('tickets'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'ticket_id' => $this->ticketId,
            'status'    => $this->status,
            'action'    => $this->action,
        ];
    }
}
