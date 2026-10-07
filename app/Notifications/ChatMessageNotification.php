<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ChatMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $ticketId,
        public string $ticketNumber,
        public string $senderName,
        public string $excerpt,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'kind'          => 'chat',
            'ticket_id'     => $this->ticketId,
            'ticket_number' => $this->ticketNumber,
            'title'         => 'New chat on #' . $this->ticketNumber,
            'message'       => $this->senderName . ': ' . $this->excerpt,
            'sender_name'   => $this->senderName,
            'url'           => route('tickets.store') . '?view=' . $this->ticketId,
        ];
    }
}
