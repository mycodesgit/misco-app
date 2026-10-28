<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;

use App\Models\TicketDB\Office;
use App\Models\TicketDB\AuditTrailOffice;

/**
 * Office Calendar — the office's shared Google Workspace calendar, embedded
 * with Google's own calendar UI. Everyone sees their own office's calendar.
 *
 * Users log in here with email + password, not Google, so Google itself
 * decides who may see the events: the embed shows them to whoever is signed
 * in to Google in that browser with access to the calendar. The page's
 * "Sync with Google" button opens Google's account chooser for that. Events
 * are added and edited in Google Calendar ("Open in Google Calendar").
 */
class CalendarController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $office = $user->office;
        $calendarId = $office->google_calendar_id ?? null;

        $embedUrl = $calendarId ? $this->embedUrl($calendarId) : null;

        // Desk staff connect / change their own office's calendar
        $canManage = $office && $user->isDeskStaff();

        return view('pages.calendar.index', compact('office', 'calendarId', 'embedUrl', 'canManage'));
    }

    /**
     * Save the office's Google Calendar ID.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $office = $user->office;

        if (!$office || !$user->isDeskStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Only your office\'s support staff can change its calendar.',
            ], 403);
        }

        $input = trim((string) $request->input('google_calendar_id'));

        // Empty = remove the calendar from this office
        $calendarId = $input === '' ? null : $this->extractCalendarId($input);

        if ($input !== '' && !$calendarId) {
            return response()->json([
                'success' => false,
                'message' => 'That doesn\'t look like a Google Calendar ID. Copy it from Google Calendar → Settings → Integrate calendar.',
            ], 422);
        }

        $office->update(['google_calendar_id' => $calendarId]);

        $this->logAudit($request, 'Edit_OfficeCalendar', [
            'office_id'          => $office->id,
            'google_calendar_id' => $calendarId,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => $calendarId ? 'Office calendar saved!' : 'Office calendar removed.',
            'embedUrl' => $calendarId ? $this->embedUrl($calendarId) : null,
        ]);
    }

    /**
     * Google's embeddable calendar view for one calendar.
     */
    private function embedUrl(string $calendarId): string
    {
        return 'https://calendar.google.com/calendar/embed?' . http_build_query([
            'src'           => $calendarId,
            'ctz'           => config('app.timezone'),
            'mode'          => 'MONTH',
            'wkst'          => 1, // week starts on Sunday
            'showPrint'     => 0,
            'showCalendars' => 0,
            'showTitle'     => 0,
        ]);
    }

    /**
     * Accept what people usually copy from Google Calendar — the Calendar ID
     * itself, the embed URL, or the whole <iframe> code — and return the ID.
     */
    private function extractCalendarId(string $input): ?string
    {
        // <iframe src="..."> -> the src URL
        if (preg_match('/src=["\']([^"\']+)["\']/i', $input, $m)) {
            $input = html_entity_decode($m[1]);
        }

        // https://calendar.google.com/calendar/embed?src=ID&... -> ID
        if (str_starts_with($input, 'http')) {
            parse_str((string) parse_url($input, PHP_URL_QUERY), $query);
            $input = $query['src'] ?? $query['cid'] ?? '';
        }

        $calendarId = trim(urldecode($input));

        // Calendar IDs look like email addresses
        return preg_match('/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/', $calendarId)
            ? $calendarId
            : null;
    }

    private function logAudit(Request $request, string $action, array $payload): void
    {
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        AuditTrailOffice::create([
            'user_id'    => auth()->id(),
            'email'      => auth()->user()->email ?? 'System',
            'action'     => $action,
            'activity'   => \App\Helpers\AuditActivity::describe($action, $payload),
            'actiondata' => json_encode($payload),
            'ip_address' => $request->ip(),
            'user_agent' => $agent->browser() . ' on ' . $agent->platform(),
        ]);
    }
}
