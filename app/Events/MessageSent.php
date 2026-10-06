<?php

namespace App\Events;

use App\Models\TicketDB\TicketChat;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chat;

    public function __construct(TicketChat $chat)
    {
        $this->chat = $chat;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('ticket.' . $this->chat->ticket_id),
        ];
    }

    public function broadcastWith(): array
    {
        $sender = $this->chat->sender;
        $senderName = $sender
            ? trim(($sender->fname ?? '') . ' ' . ($sender->lname ?? '')) ?: 'User'
            : 'User';

        return [
            'id'          => $this->chat->id,
            'ticket_id'   => $this->chat->ticket_id,
            'message'     => $this->chat->message,
            'attachment'  => $this->chat->attachment ? asset($this->chat->attachment) : null,
            'sender_id'   => $this->chat->sender_id,
            'sender_name' => $senderName,
            'time'        => $this->chat->created_at?->format('h:i A') ?? now()->format('h:i A'),
        ];
    }
}
