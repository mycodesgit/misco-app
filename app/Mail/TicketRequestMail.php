<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use App\Models\TicketDB\DailyTicketRequest;
use App\Models\TicketDB\User;

class TicketRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param DailyTicketRequest $ticket    The newly submitted ticket
     * @param User               $recipient The assigned staff member receiving this email
     */
    public function __construct(
        public DailyTicketRequest $ticket,
        public User $recipient,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[' . $this->ticket->priority . ' Priority] New Ticket Request #' . $this->ticket->ticket_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-request',
        );
    }
}
