<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use App\Models\TicketDB\DailyTicketRequest;
use App\Models\TicketDB\User;

class TicketResolvedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param DailyTicketRequest $ticket    The ticket that was just resolved
     * @param User               $recipient The requester who submitted the ticket
     */
    public function __construct(
        public DailyTicketRequest $ticket,
        public User $recipient,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Ticket #' . $this->ticket->ticket_number . ' has been Resolved',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-resolved',
        );
    }
}
