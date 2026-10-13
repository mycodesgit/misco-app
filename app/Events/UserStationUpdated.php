<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserStationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $name;
    public $station;

    public function __construct(int $userId, string $name, string $station)
    {
        $this->userId  = $userId;
        $this->name    = $name;
        $this->station = $station;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('station'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'name'    => $this->name,
            'station' => $this->station,
        ];
    }
}
