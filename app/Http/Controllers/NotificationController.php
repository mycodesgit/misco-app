<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Fetch recent ticket + chat notifications with unread counts.
     */
    public function fetch(Request $request)
    {
        $user = $request->user();

        $ticketType = \App\Notifications\TicketEventNotification::class;
        $chatType   = \App\Notifications\ChatMessageNotification::class;

        $format = function ($n) {
            return [
                'id'            => $n->id,
                'title'         => $n->data['title'] ?? 'Notification',
                'message'       => $n->data['message'] ?? '',
                'ticket_id'     => $n->data['ticket_id'] ?? null,
                'ticket_number' => $n->data['ticket_number'] ?? null,
                'event'         => $n->data['event'] ?? null,
                'url'           => $n->data['url'] ?? null,
                'read'          => !is_null($n->read_at),
                'time'          => $n->created_at?->diffForHumans(),
            ];
        };

        $tickets = $user->notifications()
            ->where('type', $ticketType)
            ->latest()->limit(10)->get()->map($format);

        $chats = $user->notifications()
            ->where('type', $chatType)
            ->latest()->limit(10)->get()->map($format);

        return response()->json([
            'tickets' => $tickets,
            'chats'   => $chats,
            'unread_tickets' => $user->unreadNotifications()->where('type', $ticketType)->count(),
            'unread_chats'   => $user->unreadNotifications()->where('type', $chatType)->count(),
        ]);
    }

    /**
     * Mark one notification as read and return its redirect URL.
     */
    public function read(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'url'     => $notification->data['url'] ?? null,
        ]);
    }

    /**
     * Mark all unread notifications (ticket + chat) for one ticket as read.
     * Called automatically when the user opens that ticket's page.
     */
    public function readTicket(Request $request)
    {
        $request->validate(['ticket_id' => 'required|integer']);

        $request->user()->unreadNotifications()
            ->whereJsonContains('data->ticket_id', (int) $request->ticket_id)
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all (or all of one kind) as read.
     */
    public function readAll(Request $request)
    {
        $query = $request->user()->unreadNotifications();

        if ($request->kind === 'ticket') {
            $query->where('type', \App\Notifications\TicketEventNotification::class);
        } elseif ($request->kind === 'chat') {
            $query->where('type', \App\Notifications\ChatMessageNotification::class);
        }

        $query->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
