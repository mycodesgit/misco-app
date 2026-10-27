<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends emails after the response has gone back to the browser, so SMTP
 * never slows down (or fails) the action the user clicked.
 */
class BackgroundMail
{
    /**
     * Queue an email to be sent once the response is finished. Failures are
     * only logged.
     */
    public static function send(string $email, Mailable $mail, string $context = ''): void
    {
        app()->terminating(function () use ($email, $mail, $context) {
            try {
                Mail::to($email)->send($mail);
            } catch (\Throwable $e) {
                Log::warning('Email failed' . ($context !== '' ? " for {$context}" : '') . " to {$email}: " . $e->getMessage());
            }
        });
    }

    /**
     * Mark the response as complete so the browser stops waiting right away,
     * while PHP keeps sending the emails (needed when the server is not
     * running PHP-FPM).
     */
    public static function respondNow(JsonResponse $response): JsonResponse
    {
        ignore_user_abort(true);
        $response->headers->set('Content-Length', strlen($response->getContent()));
        $response->headers->set('Connection', 'close');

        return $response;
    }
}
