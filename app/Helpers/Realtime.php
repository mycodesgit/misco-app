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
 * otherwise wait for the HTTP client timeout and then die with a
 * "Pusher error: cURL error 28/7" even though the ticket was updated.
 *
 * Every broadcast is therefore:
 *   - deferred until after the HTTP response is sent, so the browser never
 *     waits for the socket server;
 *   - stopped for the rest of the request as soon as one connection fails,
 *     so three broadcasts cannot cost three timeouts;
 *   - logged and swallowed; the browser falls back to reloading.
 */
class Realtime
{
    /**
     * Set once a broadcast fails so the remaining broadcasts in the same
     * request are skipped instead of waiting for their own timeout.
     */
    private static bool $unreachable = false;

    /**
     * Broadcast an event, swallowing (and logging) connection failures.
     */
    public static function broadcast($event, string $context = ''): void
    {
        self::defer(function () use ($event, $context) {
            if (self::$unreachable) {
                return;
            }

            try {
                broadcast($event);
            } catch (\Throwable $e) {
                self::$unreachable = true;
                self::log('broadcast', $context, $e);
            }
        });
    }

    /**
     * Same as broadcast(), but keeps the "skip the current socket" behaviour.
     */
    public static function broadcastToOthers($event, string $context = ''): void
    {
        self::defer(function () use ($event, $context) {
            if (self::$unreachable) {
                return;
            }

            try {
                broadcast($event)->toOthers();
            } catch (\Throwable $e) {
                self::$unreachable = true;
                self::log('broadcast', $context, $e);
            }
        });
    }

    /**
     * Store the notification and ping the recipients' sockets.
     */
    public static function notify(iterable $users, $notification, string $context = ''): void
    {
        $users = is_array($users) ? $users : collect($users)->all();

        try {
            Notification::send($users, $notification);
        } catch (\Throwable $e) {
            self::log('notification', $context, $e);
            return;
        }

        self::defer(function () use ($users, $notification, $context) {
            if (self::$unreachable) {
                return;
            }

            try {
                \App\Events\UserNotified::dispatchFor($users, $notification);
            } catch (\Throwable $e) {
                self::$unreachable = true;
                self::log('notification ping', $context, $e);
            }
        });
    }

    /**
     * Let UserNotified stop the remaining pings after its first failure.
     */
    public static function markUnreachable(): void
    {
        self::$unreachable = true;
    }

    /**
     * Run socket work after the response has been sent. In console/queue
     * processes there is no response to wait for, so run it immediately.
     */
    private static function defer(callable $callback): void
    {
        if (app()->runningInConsole()) {
            $callback();

            return;
        }

        app()->terminating($callback);
    }

    private static function log(string $kind, string $context, \Throwable $e): void
    {
        Log::warning(
            'Realtime ' . $kind . ' failed' . ($context !== '' ? " for {$context}" : '') . ': ' . $e->getMessage()
        );
    }
}
