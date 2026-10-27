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

// Support staff can listen to any ticket; a requester only to their own.
Broadcast::channel('ticket.{ticketId}', function ($user, $ticketId) {
    if ($user->role !== 'Requester') {
        return true;
    }

    return \App\Models\TicketDB\DailyTicketRequest::where('id', $ticketId)
        ->where('user_id', $user->id)
        ->exists();
});
