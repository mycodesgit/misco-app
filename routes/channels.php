<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// A ticket's channel (chat, status, typing) belongs to its requester and its
// handling desk only — the same people who can open the ticket.
Broadcast::channel('ticket.{ticketId}', function ($user, $ticketId) {
    $ticket = \App\Models\TicketDB\DailyTicketRequest::find($ticketId, ['id', 'user_id', 'off_id', 'assigned_to']);

    return $ticket && $user->canViewTicket($ticket);
});
