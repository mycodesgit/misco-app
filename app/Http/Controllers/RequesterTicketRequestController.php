<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

use Jenssegers\Agent\Agent;

use App\Models\TicketDB\User;
use App\Models\TicketDB\UserRole;
use App\Models\TicketDB\Category;
use App\Models\TicketDB\Subcategory;
use App\Models\TicketDB\DailyTicketRequest;
use App\Models\TicketDB\ClientFeedback;
use App\Models\TicketDB\AuditTrailDailyTicketRequest;
use App\Models\TicketDB\AuditTrailClientFeedback;

use App\Services\LanguageDetectionService;

use App\Mail\TicketRequestMail;
use App\Helpers\BackgroundMail;

class RequesterTicketRequestController extends Controller
{
    /**
     * A resolved ticket can only be reopened within this many days
     * after it was resolved (1 week).
     */
    public const REOPEN_WINDOW_DAYS = 7;

    protected LanguageDetectionService $languageDetection;

    public function __construct(LanguageDetectionService $languageDetection)
    {
        $this->languageDetection = $languageDetection;
    }

    public function index()
    {
        $currentUserRole = Auth::user()->role;

        $urole = UserRole::where('status', 1)
            ->whereNotIn('rolename', ['Administrator', 'Requester'])
            ->when($currentUserRole == 'Administrator', function ($query) use ($currentUserRole) {
                $query->where('rolename', $currentUserRole);
            })
            ->get();

        $cat = Category::with('user')->where('status', 1)->get();

        return view('pages.request.requestertickets', compact('urole', 'cat'));
    }

    public function getCat($supportType)
    {
        $categories = Category::where('status', 1)
            ->whereJsonContains('cattype', $supportType)
            ->whereJsonContains('cattype', 'Requester')
            ->get([
                'id',
                'ticketcatname'
            ]);

        return response()->json($categories);
    }

    public function getSubcat($category)
    {
        $subcategories = Subcategory::where('status', 1)
            ->where('cat_id', $category)
            ->get([
                'id',
                'ticketsubcatname'
            ]);

        return response()->json($subcategories);
    }

    public function getassignedPersonnel($category)
    {
        $users = User::with('assignedTasks')
            ->where('ustatus', '!=', 3)
            ->whereHas('assignedTasks', function ($query) use ($category) {
                $query->whereJsonContains('taskassigned', (string) $category);
            })
            ->get([
                'id',
                'fname',
                'lname',
            ]);

        return response()->json($users);
    }

    public function create(Request $request)
    {
        // 1. Validation Rules
        $validator = Validator::make($request->all(), [
            'off_id'            => 'required|integer',
            'cat_id'            => 'required|exists:categories,id',
            'subcat_id'         => 'required|exists:subcategories,id',
            'assigned_to'       => 'required',
            'priority'          => 'required|in:Low,Medium,High,Urgent',
            'contactno'         => 'nullable|string|max:50',
            'issue_description' => ['required', 'string', 'regex:/^[\x09\x0A\x0D\x20-\x7E\x{2018}\x{2019}\x{201C}\x{201D}\x{2013}\x{2014}\x{2026}]+$/u'],
            'remarks'           => ['nullable', 'string', 'regex:/^[\x09\x0A\x0D\x20-\x7E\x{2018}\x{2019}\x{201C}\x{201D}\x{2013}\x{2014}\x{2026}]*$/u'],
            'attachment'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'issue_description.regex' => 'Issue Description must only contain English characters (letters, numbers, and basic punctuation).',
            'remarks.regex'           => 'Additional Remarks / Notes must only contain English characters (letters, numbers, and basic punctuation).',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        // Reject Philippine-language free text (Tagalog, Cebuano, Ilocano, etc.).
        // English, other languages and non-Latin scripts are unaffected.
        if ($languageError = $this->englishOnlyError($request)) {
            return response()->json([
                'success' => false,
                'message' => $languageError['message'],
                'errors'  => [$languageError['field'] => [$languageError['message']]],
            ], 422);
        }

        // Contact number is required: the one typed in the form, or else the
        // number saved on the requester's profile.
        $contactNo = trim((string) $request->input('contactno')) ?: (Auth::user()->contactno ?? '');
        if ($contactNo === '') {
            return response()->json([
                'success' => false,
                'message' => 'Contact Number is required.',
                'errors'  => ['contactno' => ['Contact Number is required.']],
            ], 422);
        }

        try {
            DB::beginTransaction();

            // 2. Generate Unique Ticket Number (e.g., TKT-MIS-20261012-0001)
            // Prefix = first 3 letters of the selected support role ("MIS Support" -> "MIS")
            $datePrefix = Carbon::now()->format('Ymd');
            $supportCode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', (string) $request->input('support_type', '')), 0, 3));
            if ($supportCode === '') {
                $supportCode = 'MIS';
            }
            $latestTicket = DailyTicketRequest::whereDate('created_at', Carbon::today())
                ->where('ticket_number', 'LIKE', 'TKT-' . $supportCode . '-' . $datePrefix . '-%')
                ->latest('id')
                ->first();

            $sequence = $latestTicket ? ((int) substr($latestTicket->ticket_number, -4)) + 1 : 1;
            $ticketNumber = 'TKT-' . $supportCode . '-' . $datePrefix . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            // 3. Handle File Upload (Formatted: userID_officeAbbr_yearTimestamp.extension)
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');

                // Get current user and office abbreviation
                $userId = auth()->id();
                $officeAbbr = auth()->user()->office->office_abbr ?? 'UNKNOWN';

                // Clean office abbreviation (remove spaces or special characters if any)
                $cleanOfficeAbbr = preg_replace('/[^A-Za-z0-9\-]/', '', $officeAbbr);

                // Format: {userId}_{officeAbbr}_{yearTimestamp}.{extension}
                // Example output: 12_IT_20261004184632.jpg
                $extension = $file->getClientOriginalExtension();
                $filename = $userId . '_' . $cleanOfficeAbbr . '_' . date('YmdHis') . '.' . $extension;

                // Define year folder path
                $currentYear = date('Y');
                $folderPath = 'ticket_attachments/' . $currentYear;

                // Store in EXTERNAL_STORAGE_ROOT/ticket_attachments/{YEAR}/
                $file->storeAs($folderPath, $filename, 'external');

                // Relative path saved in DB
                $attachmentPath = '/storage/' . $folderPath . '/' . $filename;
            }

            // 4. Create Ticket Record
            $ticket = DailyTicketRequest::create([
                'user_id'           => Auth::id(),
                'reqoff_id'         => Auth::user()->office_id,
                'off_id'            => $request->off_id,
                'cat_id'            => $request->cat_id,
                'subcat_id'         => $request->subcat_id,
                'ticket_number'     => $ticketNumber,
                'assigned_to'       => $request->assigned_to,
                'issue_description' => $request->issue_description,
                'remarks'           => $request->remarks,
                'priority'          => $request->priority,
                'contactno'         => $contactNo,
                'attachment'        => $attachmentPath,
                'status'            => 'Pending',
            ]);

            $clientsat = ClientFeedback::create([
                'user_id'   => Auth::id(),
                'cat_id'    => $request->input('cat_id'),
                'subcat_id' => $request->input('subcat_id'),
                'off_id'    => $request->input('off_id'),
                'ticket_id' => $ticket->id,
            ]);

            DB::commit();

            Cache::forever('dashboard_version', time()); // invalidate dashboard cache (atomic, no flush race)
            \App\Helpers\Realtime::broadcast(
                new \App\Events\TicketListUpdated($ticket->id, $ticket->status, 'created'),
                'ticket ' . $ticket->ticket_number
            );

            // Notify support users of the ticket's handling office only (office_id = ticket off_id)
            $supportUsers = User::where('role', '!=', 'Requester')
                ->where('ustatus', '!=', 3)
                ->where('office_id', $request->input('off_id'))
                ->where('id', '!=', Auth::id())
                ->get();
            if ($supportUsers->isNotEmpty()) {
                $requesterName = trim((Auth::user()->fname ?? '') . ' ' . (Auth::user()->lname ?? '')) ?: 'A requester';
                $notif = new \App\Notifications\TicketEventNotification(
                    ticketId: $ticket->id,
                    ticketNumber: $ticketNumber,
                    event: 'created',
                    title: 'New Pending Ticket #' . $ticketNumber,
                    message: $requesterName . ' submitted a new ticket: ' . Str::limit($request->issue_description, 80),
                );
                \App\Helpers\Realtime::notify($supportUsers, $notif, 'ticket ' . $ticket->ticket_number);
            }

            // Email every staff member listed in assigned_to
            $this->emailAssignedStaff($ticket);

            // 5. Log Audit
            $userPayload = $ticket->toArray();
            $this->logAudit($request, 'Add_DailyTicket', $userPayload);

            // Respond right away so the modal closes while the emails send
            return BackgroundMail::respondNow(response()->json([
                'success' => true,
                'message' => 'Ticket #' . $ticketNumber . ' created successfully!',
                'data'    => $ticket
            ], 201));

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send the "New Ticket Request" email to each user in the ticket's
     * comma-separated assigned_to list. Emails go out after the response is
     * sent so SMTP never slows down (or fails) the ticket submission.
     */
    private function emailAssignedStaff(DailyTicketRequest $ticket): void
    {
        $ids = array_filter(array_map('trim', explode(',', (string) $ticket->assigned_to)));

        $recipients = User::whereIn('id', $ids)
            ->where('ustatus', '!=', 3)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        $ticket->load(['requester', 'requesteroffice', 'category', 'subcategory']);

        foreach ($recipients as $recipient) {
            BackgroundMail::send($recipient->email, new TicketRequestMail($ticket, $recipient), 'ticket ' . $ticket->ticket_number);
        }
    }

    /**
     * Fetch a single requester ticket for editing.
     */
    public function edit($id)
    {
        $ticket = DailyTicketRequest::with(['category', 'subcategory'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                => $ticket->id,
                'ticket_number'     => $ticket->ticket_number,
                'category'          => $ticket->category->ticketcatname ?? '-',
                'subcategory'       => $ticket->subcategory->ticketsubcatname ?? '-',
                'priority'          => $ticket->priority,
                'contactno'         => $ticket->contactno,
                'issue_description' => $ticket->issue_description,
                'remarks'           => $ticket->remarks,
            ],
        ]);
    }

    /**
     * Update an existing requester ticket (priority, contact, description, remarks).
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ticket_id'         => 'required|exists:dailyticketrequest,id',
            'priority'          => 'required|in:Low,Medium,High,Urgent',
            'contactno'         => 'nullable|string|max:50',
            'issue_description' => ['required', 'string', 'regex:/^[\x09\x0A\x0D\x20-\x7E\x{2018}\x{2019}\x{201C}\x{201D}\x{2013}\x{2014}\x{2026}]+$/u'],
            'remarks'           => ['nullable', 'string', 'regex:/^[\x09\x0A\x0D\x20-\x7E\x{2018}\x{2019}\x{201C}\x{201D}\x{2013}\x{2014}\x{2026}]*$/u'],
        ], [
            'issue_description.regex' => 'Issue Description must only contain English characters (letters, numbers, and basic punctuation).',
            'remarks.regex'           => 'Additional Remarks / Notes must only contain English characters (letters, numbers, and basic punctuation).',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        // Reject Philippine-language free text (Tagalog, Cebuano, Ilocano, etc.).
        // English, other languages and non-Latin scripts are unaffected.
        if ($languageError = $this->englishOnlyError($request)) {
            return response()->json([
                'success' => false,
                'message' => $languageError['message'],
                'errors'  => [$languageError['field'] => [$languageError['message']]],
            ], 422);
        }

        $ticket = DailyTicketRequest::where('id', $request->ticket_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found.'
            ], 404);
        }

        // Only Pending tickets can still be edited by the requester.
        if ($ticket->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'This ticket can no longer be edited because it is already ' . $ticket->status . '.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $ticket->priority          = $request->priority;
            $ticket->contactno         = $request->contactno;
            $ticket->issue_description = $request->issue_description;
            $ticket->remarks           = $request->remarks;
            $ticket->save();

            DB::commit();

            Cache::forever('dashboard_version', time()); // invalidate dashboard cache (atomic, no flush race)
            \App\Helpers\Realtime::broadcast(
                new \App\Events\TicketListUpdated($ticket->id, $ticket->status, 'updated'),
                'ticket ' . $ticket->ticket_number
            );

            // Log Audit
            $this->logAudit($request, 'Update_DailyTicket', $ticket->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Ticket #' . $ticket->ticket_number . ' updated successfully!',
                'data'    => $ticket
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showreqpending()
    {
        $authuserID = Auth::user()->id;

        $data = DailyTicketRequest::with(['requester.office', 'category', 'subcategory', 'resolver:id,fname,lname,ext'])
            ->where('status', 'Pending')
            ->where('user_id', $authuserID)
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function showreqprogress()
    {
        $authuserID = Auth::user()->id;

        $data = DailyTicketRequest::with(['requester.office', 'category', 'subcategory', 'resolver:id,fname,lname,ext'])
            ->where('status', 'In Progress')
            ->where('user_id', $authuserID)
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function showreqresolved()
    {
        $authuserID = Auth::user()->id;

        $data = DailyTicketRequest::with(['requester.office', 'category', 'subcategory', 'resolver:id,fname,lname,ext'])
            ->where('status', 'Resolved')
            ->where('user_id', $authuserID)
            ->orderBy('id', 'DESC')
            ->get();

        // Detect from DB which tickets already have feedback submitted
        $submittedTicketIds = ClientFeedback::whereIn('ticket_id', $data->pluck('id'))
            ->whereNotNull('rating')
            ->pluck('ticket_id')
            ->toArray();

        $data->each(function ($ticket) use ($submittedTicketIds) {
            $ticket->has_feedback = in_array($ticket->id, $submittedTicketIds);
            $ticket->can_reopen = $this->canReopen($ticket);
            $ticket->reopen_deadline = $this->reopenDeadline($ticket)?->toIso8601String();
        });

        return response()->json(['data' => $data]);
    }

    /**
     * The last moment a resolved ticket may be reopened:
     * 1 week after it was resolved.
     */
    private function reopenDeadline(DailyTicketRequest $ticket): ?Carbon
    {
        $resolvedAt = $ticket->resolved_at ?: $ticket->updated_at ?: $ticket->created_at;

        return $resolvedAt ? Carbon::parse($resolvedAt)->addDays(self::REOPEN_WINDOW_DAYS) : null;
    }

    /**
     * A ticket may only be reopened while it is still Resolved and the
     * 1-week reopen window has not lapsed yet.
     */
    private function canReopen(DailyTicketRequest $ticket): bool
    {
        if ($ticket->status !== 'Resolved') {
            return false;
        }

        $deadline = $this->reopenDeadline($ticket);

        return $deadline !== null && Carbon::now()->lessThanOrEqualTo($deadline);
    }

    /**
     * Reopen a resolved ticket.
     *
     * The ticket number and every other detail stay exactly the same — only
     * the status moves from Resolved back to Pending because the requester is
     * not satisfied with the resolution. A ticket can only be reopened within
     * 1 week of being resolved; after that the window has lapsed.
     */
    public function reopen(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ticket_id' => 'required|integer|exists:dailyticketrequest,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $ticket = DailyTicketRequest::where('id', $request->ticket_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found.'
            ], 404);
        }

        if ($ticket->status !== 'Resolved') {
            return response()->json([
                'success' => false,
                'message' => 'Only resolved tickets can be reopened.'
            ], 403);
        }

        $deadline = $this->reopenDeadline($ticket);

        if ($deadline === null || Carbon::now()->greaterThan($deadline)) {
            return response()->json([
                'success' => false,
                'message' => 'The 1-week reopen window for this ticket has lapsed and it can no longer be reopened.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Only the status changes — the ticket number stays the same
            $ticket->status = 'Pending';
            $ticket->save();

            DB::commit();

            Cache::forever('dashboard_version', time()); // invalidate dashboard cache (atomic, no flush race)
            \App\Helpers\Realtime::broadcast(
                new \App\Events\TicketListUpdated($ticket->id, $ticket->status, 'reopened'),
                'ticket ' . $ticket->ticket_number
            );
            // Also refresh an open chat page (the chat re-opens with the ticket)
            \App\Helpers\Realtime::broadcast(
                new \App\Events\TicketStatusUpdated($ticket->fresh()),
                'ticket ' . $ticket->ticket_number
            );

            // Let the support personnel know the requester is not satisfied
            $this->notifySupportOfReopen($ticket);

            // Log Audit
            $this->logAudit($request, 'Reopen_DailyTicket', $ticket->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Ticket #' . $ticket->ticket_number . ' has been reopened and is now Pending.',
                'data'    => $ticket
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to reopen ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Notify the personnel handling the ticket (or the support office when
     * nobody is assigned) that the requester reopened it.
     */
    private function notifySupportOfReopen(DailyTicketRequest $ticket): void
    {
        $assignedIds = collect(explode(',', (string) $ticket->assigned_to))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->unique()
            ->reject(fn ($id) => $id === (int) Auth::id())
            ->values();

        if ($assignedIds->isNotEmpty()) {
            $recipients = User::whereIn('id', $assignedIds)->where('ustatus', '!=', 3)->get();
        } else {
            $recipients = User::where('office_id', $ticket->off_id)
                ->where('role', '!=', 'Requester')
                ->where('ustatus', '!=', 3)
                ->where('id', '!=', Auth::id())
                ->get();
        }

        if ($recipients->isEmpty()) {
            return;
        }

        $requesterName = trim((Auth::user()->fname ?? '') . ' ' . (Auth::user()->lname ?? '')) ?: 'A requester';

        $notif = new \App\Notifications\TicketEventNotification(
            ticketId: $ticket->id,
            ticketNumber: $ticket->ticket_number,
            event: 'reopened',
            title: 'Ticket #' . $ticket->ticket_number . ' was reopened',
            message: $requesterName . ' reopened this ticket: ' . Str::limit($ticket->issue_description, 80),
        );

        // Realtime fan-out must never fail the reopen itself (Reverb may be down)
        \App\Helpers\Realtime::notify($recipients, $notif, 'ticket ' . $ticket->ticket_number);
    }

    public function showreqclosed()
    {
        $authuserID = Auth::user()->id;

        $data = DailyTicketRequest::with(['requester.office', 'category', 'subcategory', 'resolver:id,fname,lname,ext'])
            ->where('status', 'Cancelled')
            ->where('user_id', $authuserID)
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Get existing feedback for a ticket (to pre-fill the modal).
     */
    public function getFeedback($ticketId)
    {
        $ticket = DailyTicketRequest::where('id', $ticketId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $satisfaction = ClientFeedback::where('ticket_id', $ticket->id)
            ->where('user_id', Auth::id())
            ->first();

        return response()->json([
            'ticket_number' => $ticket->ticket_number,
            'rating'        => $satisfaction->rating ?? null,
            'feedback'      => $satisfaction->feedback ?? null,
        ]);
    }

    /**
     * Store / update requester feedback using ClientFeedback model.
     */
    public function submitFeedback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ticket_id' => 'required|exists:dailyticketrequest,id',
            'rating'    => 'required|integer|min:1|max:5',
            'feedback'  => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        $ticket = DailyTicketRequest::where('id', $request->ticket_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found.'
            ], 404);
        }

        $satisfaction = ClientFeedback::firstOrNew([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
        ]);

        $satisfaction->cat_id    = $ticket->cat_id;
        $satisfaction->subcat_id = $ticket->subcat_id;
        $satisfaction->off_id    = $ticket->off_id;
        $satisfaction->resolved_by = $ticket->resolved_by;
        $satisfaction->rating     = $request->rating;
        $satisfaction->feedback   = $request->feedback;
        $satisfaction->save();

        $this->logAuditSatisfactory($request, 'Submit_ClientFeedback', $satisfaction->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback!',
            'data'    => $satisfaction
        ]);
    }

    /**
     * Check the free-text fields that must be written in English.
     *
     * Returns an array describing the first offending field, or null when all
     * fields pass. Only Philippine languages are rejected; other languages and
     * non-Latin scripts are left to the regex rules above.
     *
     * @return array{field: string, message: string}|null
     */
    private function englishOnlyError(Request $request): ?array
    {
        $fields = [
            'issue_description' => 'Issue Description',
            'remarks'           => 'Additional Remarks / Notes',
        ];

        foreach ($fields as $field => $label) {
            if ($this->languageDetection->isPhilippineLanguage($request->input($field))) {
                return [
                    'field'   => $field,
                    'message' => $label . ' must be written in English. '
                        . 'Please remove Filipino or other Philippine dialect words.',
                ];
            }
        }

        return null;
    }

    /**
     * Helper function to centralize audit trail logging.
     */
    private function logAudit(Request $request, string $action, array $payload): void
    {
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        $browser  = $agent->browser();
        $platform = $agent->platform();

        AuditTrailDailyTicketRequest::create([
            'user_id'    => auth()->id(),
            'email'   => auth()->user()->email ?? 'System',
            'action'     => $action,
            'activity'   => \App\Helpers\AuditActivity::describe($action, $payload),
            'actiondata' => json_encode($payload),
            'ip_address' => $request->ip(),
            'user_agent' => $browser . ' on ' . $platform,
            'login_at'   => now(),
        ]);
    }

    private function logAuditSatisfactory(Request $request, string $action, array $payload): void
    {
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        $browser  = $agent->browser();
        $platform = $agent->platform();

        AuditTrailClientFeedback::create([
            'user_id'    => auth()->id(),
            'email'      => auth()->user()->email ?? 'System',
            'action'     => $action,
            'activity'   => \App\Helpers\AuditActivity::describe($action, $payload),
            'actiondata' => json_encode($payload),
            'ip_address' => $request->ip(),
            'user_agent' => $browser . ' on ' . $platform,
        ]);
    }
}
