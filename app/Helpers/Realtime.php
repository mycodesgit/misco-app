<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Realtime fan-out (Reverb / Pusher) must never fail the operation it
 * belongs to.
 *
 * When the socket server is unreachable (server down, wrong host/port, not
 * started yet) the database change is already saved — the request would
 * otherwise die with a "Pusher error: cURL error 7" even though the ticket
 * was updated. Every broadcast is therefore wrapped: the failure is logged
 * and the caller carries on. The browser falls back to polling/refreshing.
 */
class Realtime
{
    /**
     * Broadcast an event, swallowing (and logging) connection failures.
     */
    public static function broadcast($event, string $context = ''): void
    {
        try {
            broadcast($event);
        } catch (\Throwable $e) {
            self::log('broadcast', $context, $e);
        }
    }

    /**
     * Same as broadcast(), but keeps the "skip the current socket" behaviour.
     */
    public static function broadcastToOthers($event, string $context = ''): void
    {
        try {
            broadcast($event)->toOthers();
        } catch (\Throwable $e) {
            self::log('broadcast', $context, $e);
        }
    }

    /**
     * Store the notification and ping the recipients' sockets.
     */
    public static function notify(iterable $users, $notification, string $context = ''): void
    {
        try {
            Notification::send($users, $notification);
        } catch (\Throwable $e) {
            self::log('notification', $context, $e);
            return;
        }

        \App\Events\UserNotified::dispatchFor($users, $notification);
    }

    private static function log(string $kind, string $context, \Throwable $e): void
    {
        Log::warning(
            'Realtime ' . $kind . ' failed' . ($context !== '' ? " for {$context}" : '') . ': ' . $e->getMessage()
        );
    }
}
