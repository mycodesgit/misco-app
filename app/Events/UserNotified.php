<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserNotified implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public array $payload, // kind, title, message, ticket_id, ticket_number, url
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->userId),
        ];
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }

    /**
     * Store already handled via Notification::send — this pushes the
     * realtime Reverb ping so the topbar updates + chimes instantly.
     */
    public static function dispatchFor(iterable $users, \Illuminate\Notifications\Notification $notification): void
    {
        foreach ($users as $user) {
            broadcast(new self((int) $user->id, $notification->toDatabase($user)));
        }
    }
}
