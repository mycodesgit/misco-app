<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketEventNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $ticketId,
        public string $ticketNumber,
        public string $event, // created | in_progress | resolved | cancelled
        public string $title,
        public string $message,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'kind'          => 'ticket',
            'event'         => $this->event,
            'ticket_id'     => $this->ticketId,
            'ticket_number' => $this->ticketNumber,
            'title'         => $this->title,
            'message'       => $this->message,
            'url'           => route('tickets.store') . '?view=' . $this->ticketId,
        ];
    }
}
