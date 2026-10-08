<?php

namespace App\Helpers;

/**
 * Turns raw audit rows into one-line, auditor-friendly sentences like
 * "Juan Dela Cruz added new Category 'IT Equipment'".
 *
 * Used when writing new audit rows AND as a fallback for old rows that
 * predate the `activity` column (their JSON payload is all we need).
 */
class AuditActivity
{
    public static function describe(string $action, array $payload = [], ?string $actor = null): string
    {
        $actor = $actor ?: self::authName() ?: 'System';

        // Edit/Delete payloads are wrapped as ['after' => [...]]
        $data = $payload['after'] ?? $payload;
        if (!is_array($data)) {
            $data = [];
        }

        switch ($action) {
            case 'LOGIN':
                return "{$actor} logged in";
            case 'LOGOUT':
                return "{$actor} logged out";
            case 'FAILED_LOGIN':
                $email = $payload['email'] ?? null;
                return 'Failed login attempt' . ($email ? " for '{$email}'" : '');

            case 'Add_Category':
                return "{$actor} added new Category '" . self::str($data, 'ticketcatname') . "'";
            case 'Edit_Category':
                return "{$actor} updated Category '" . self::str($data, 'ticketcatname') . "'";
            case 'Delete_Category':
                return "{$actor} deleted Category '" . self::str($data, 'ticketcatname') . "'";

            case 'Add_Subcategory':
                return "{$actor} added new Subcategory '" . self::str($data, 'ticketsubcatname') . "'";
            case 'Edit_Subcategory':
                return "{$actor} updated Subcategory '" . self::str($data, 'ticketsubcatname') . "'";
            case 'Delete_Subcategory':
                return "{$actor} deleted Subcategory '" . self::str($data, 'ticketsubcatname') . "'";

            case 'Add_Office':
                return "{$actor} added new Office '" . self::office($data) . "'";
            case 'Edit_Office':
                return "{$actor} updated Office '" . self::office($data) . "'";

            case 'Add_DailyTask':
                return "{$actor} added new Daily Task '" . self::trunc(self::str($data, 'dailytaskdesc')) . "'";
            case 'Edit_DailyTask':
                return "{$actor} updated Daily Task '" . self::trunc(self::str($data, 'dailytaskdesc')) . "'";

            case 'Add_User':
                return "{$actor} added new User '" . self::person($data) . "'";
            case 'Edit_User':
                return "{$actor} updated User '" . self::person($data) . "'";
            case 'Edit_Password':
                return "{$actor} updated password of User '" . self::person($data) . "'";
            case 'Edit_Status':
                return "{$actor} updated status of User '" . self::person($data) . "'";
            case 'Edit_OwnPassword':
                return "{$actor} changed their own password";

            case 'Add_Role':
                return "{$actor} added new Role '" . self::str($data, 'rolename') . "'";
            case 'Edit_Role':
                return "{$actor} updated Role '" . self::str($data, 'rolename') . "'";

            case 'Add_UserAssignedTask':
                return "{$actor} assigned tasks to " . self::userRef($data);
            case 'Edit_Task_Assignment':
                return "{$actor} updated task assignment of " . self::userRef($data);

            case 'Add_DailyTicket':
                return "{$actor} filed new Ticket '" . self::str($data, 'ticket_number') . "'";

            case 'Submit_ClientFeedback':
                $rating = $data['rating'] ?? '?';
                $ticket = $data['ticket_id'] ?? '?';
                return "{$actor} submitted {$rating}-star feedback on Ticket #{$ticket}";

            default:
                // Generic fallback: Add_X -> "added X", Edit_X -> "updated X", ...
                if (preg_match('/^(Add|Edit|Delete|Submit)_(.+)$/', $action, $m)) {
                    $verb = ['Add' => 'added', 'Edit' => 'updated', 'Delete' => 'deleted', 'Submit' => 'submitted'][$m[1]];
                    $object = ucwords(str_replace('_', ' ', $m[2]));
                    $target = self::target($data);
                    return trim("{$actor} {$verb} {$object}" . ($target ? " '{$target}'" : ''));
                }
                return trim("{$actor} performed {$action}");
        }
    }

    private static function authName(): ?string
    {
        try {
            $u = auth()->user();
            if (!$u) {
                return null;
            }
            $name = trim(($u->fname ?? '') . ' ' . ($u->lname ?? ''));
            return $name !== '' ? $name : ($u->email ?? null);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private static function str(array $data, string $key): string
    {
        $v = $data[$key] ?? '';
        if (is_array($v)) {
            $v = implode(', ', $v);
        }
        return trim((string) $v) !== '' ? trim((string) $v) : '-';
    }

    private static function trunc(string $text, int $len = 60): string
    {
        if (mb_strlen($text) <= $len) {
            return $text;
        }
        return mb_substr($text, 0, $len) . '…';
    }

    private static function person(array $data): string
    {
        $name = trim(($data['fname'] ?? '') . ' ' . ($data['lname'] ?? ''));
        if ($name !== '') {
            return $name;
        }
        if (!empty($data['email'])) {
            return $data['email'];
        }
        return self::userRef($data);
    }

    private static function userRef(array $data): string
    {
        return !empty($data['user_id']) ? 'User #' . $data['user_id'] : 'a user';
    }

    private static function office(array $data): string
    {
        $abbr = trim((string) ($data['office_abbr'] ?? ''));
        $name = trim((string) ($data['office_name'] ?? ''));
        if ($abbr !== '' && $name !== '') {
            return "{$abbr} - {$name}";
        }
        return $name !== '' ? $name : ($abbr !== '' ? $abbr : '-');
    }

    private static function target(array $data): string
    {
        foreach (['ticket_number', 'ticketcatname', 'ticketsubcatname', 'office_name', 'rolename', 'dailytaskdesc'] as $key) {
            if (!empty($data[$key]) && !is_array($data[$key])) {
                return self::trunc((string) $data[$key]);
            }
        }
        $person = self::person($data);
        return $person !== 'a user' ? $person : '';
    }
}
