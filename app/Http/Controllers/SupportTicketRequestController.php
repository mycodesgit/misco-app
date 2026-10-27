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
use App\Models\TicketDB\Category;
use App\Models\TicketDB\Subcategory;
use App\Models\TicketDB\DailyTicketRequest;
use App\Models\TicketDB\TicketChat;
use App\Models\TicketDB\ClientFeedback;
use App\Models\TicketDB\AuditTrailCategory;
use App\Models\TicketDB\AuditTrailCategorySub;
use App\Models\TicketDB\AuditTrailDailyTicketRequest;

use App\Mail\TicketResolvedMail;
use App\Helpers\BackgroundMail;

class SupportTicketRequestController extends Controller
{
    public function index()
    {
        return view('pages.request.supportalltickets');
    }

    public function showsupportpending()
    {
        $authuseroffice = Auth::user()->office_id;

        $data = DailyTicketRequest::with(['requester.office', 'category', 'subcategory', 'resolver:id,fname,lname,ext'])
            ->where('status', 'Pending')
            ->where('off_id', $authuseroffice)
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function showprogress()
    {
        $authuseroffice = Auth::user()->office_id;

        $data = DailyTicketRequest::with(['requester.office', 'category', 'subcategory', 'resolver:id,fname,lname,ext'])
            ->where('status', 'In Progress')
            ->where('off_id', $authuseroffice)
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function showresolved()
    {
        $authuseroffice = Auth::user()->office_id;

        $data = DailyTicketRequest::with(['requester.office', 'category', 'subcategory', 'resolver:id,fname,lname,ext'])
            ->where('status', 'Resolved')
            ->where('off_id', $authuseroffice)
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function showclosed()
    {
        $authuseroffice = Auth::user()->office_id;

        $data = DailyTicketRequest::with(['requester.office', 'category', 'subcategory', 'resolver:id,fname,lname,ext'])
            ->where('status', 'Cancelled')
            ->where('off_id', $authuseroffice)
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function create(Request $request)
    {
        // 1. Validation Rules
        $validator = Validator::make($request->all(), [
            'cat_id'            => 'required|exists:categories,id',
            'subcat_id'         => 'required|exists:subcategories,id',
            'issue_description' => 'required|string|max:1000',
            'priority'          => 'required|in:Low,Medium,High,Urgent',
            'contactno'         => 'nullable|string|max:20',
            'attachment'        => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048', // Max 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // 2. Generate Unique Ticket Number (e.g., TKT-20261002-0001)
            $datePrefix = Carbon::now()->format('Ymd');
            $latestTicket = DailyTicketRequest::whereDate('created_at', Carbon::today())
                ->latest('id')
                ->first();

            $sequence = $latestTicket ? ((int) substr($latestTicket->ticket_number, -4)) + 1 : 1;
            $ticketNumber = 'TKT-' . $datePrefix . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            // 3. Handle File Upload (Optional)
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = time() . '_' . $file->getClientOriginalName();
                // Store in EXTERNAL_STORAGE_ROOT/ticket_attachments/{YEAR}/
                $stored = $file->storeAs('ticket_attachments/' . date('Y'), $filename, 'external');
                $attachmentPath = $stored ? '/storage/' . $stored : null;
            }

            // 4. Create Ticket Record
            $ticket = DailyTicketRequest::create([
                'user_id'           => Auth::id(),
                'cat_id'            => $request->cat_id,
                'subcat_id'         => $request->subcat_id,
                'ticket_number'     => $ticketNumber,
                'issue_description' => $request->issue_description,
                'priority'          => $request->priority,
                'contactno'         => $request->contactno,
                'attachment'        => $attachmentPath,
                'status'            => 'Pending',
            ]);

            DB::commit();

            Cache::forever('dashboard_version', time()); // invalidate dashboard cache (atomic, no flush race)
            \App\Helpers\Realtime::broadcast(
                new \App\Events\TicketListUpdated($ticket->id, $ticket->status, 'created'),
                'ticket ' . $ticket->ticket_number
            );

            return response()->json([
                'success' => true,
                'message' => 'Ticket #' . $ticketNumber . ' created successfully!',
                'data'    => $ticket
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $ticketId = $request->query('view');

        $ticket = DailyTicketRequest::select([
                'id', 'ticket_number', 'user_id', 'reqoff_id', 'off_id', 'cat_id', 'subcat_id',
                'assigned_to', 'priority', 'contactno', 'issue_description',
                'remarks', 'attachment', 'status', 'created_at',
                'resolution_remarks', 'resolved_at', 'started_at'
            ])
            ->with([
                'category:id,ticketcatname', // replace 'ticketcatname' with your actual column name
                'subcategory:id,ticketsubcatname',
                'requester:id,fname,lname,email',
                'requesteroffice:id,office_name,office_abbr',
                'supportoffice:id,office_abbr'
            ])
            ->findOrFail($ticketId);

        // Opening a pending ticket picks it up: it flips to "In Progress" on its
        // own, so there is no need to press "Mark as In Progress" afterwards.
        // Only support staff trigger this — a requester viewing the same page
        // must never change the status.
        if ($this->isSupportUser() && $ticket->status === 'Pending') {
            $this->applyStatus($ticket, 'in_progress');
        }

        return view('pages.request.supportshowticket', compact('ticket'));
    }

    /**
     * True when the logged-in user is IT support staff (not a requester).
     */
    private function isSupportUser(): bool
    {
        return (string) Auth::user()->role !== 'Requester';
    }

    // Send Message
    public function sendMessage(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:dailyticketrequest,id',
            'message'   => 'nullable|string|max:2000',
            'attachment' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // Ensure either message or attachment is provided
        if (!$request->filled('message') && !$request->hasFile('attachment')) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a message or select an image.'
            ], 422);
        }

        // The chat is closed once the ticket is resolved (or cancelled),
        // so nothing can be sent anymore.
        $ticket = DailyTicketRequest::find($request->ticket_id);

        if ($ticket && in_array(strtolower((string) $ticket->status), ['resolved', 'cancelled'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'This ticket is already ' . $ticket->status . ', so the chat is closed and no more messages can be sent.'
            ], 403);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $currentYear = date('Y');
            $senderId = auth()->id();
            $extension = $file->getClientOriginalExtension();

            // Format: senderid_year_timestamp.extension (e.g., 12_2026_1710000000.png)
            $fileName = $senderId . '_' . $currentYear . '_' . time() . '.' . $extension;

            // Save to EXTERNAL_STORAGE_ROOT/chat_attachments/2026/
            $attachmentPath = $file->storeAs("chat_attachments/{$currentYear}", $fileName, 'external');
        }

        $chat = TicketChat::create([
            'ticket_id'  => $request->ticket_id,
            'sender_id'  => auth()->id(),
            'message'    => $request->message ?? '',
            'attachment' => $attachmentPath ? 'storage/' . $attachmentPath : null,
        ]);

        // Load sender details for frontend rendering
        $chat->load('sender');

        // Realtime fan-out must never fail the send itself: if Reverb or the
        // notification store is misconfigured (e.g. new server), the message
        // is already saved — log and still return success. Polling fallback
        // on the client keeps delivery live without refresh.
        \App\Helpers\Realtime::broadcastToOthers(
            new \App\Events\MessageSent($chat),
            'chat ' . $chat->id
        );

        // Notify ticket participants (except the sender) of the new chat message
        if ($ticket) {
            $participantIds = collect(explode(',', (string) $ticket->assigned_to))
                ->map(fn ($id) => (int) trim($id))
                ->filter()
                ->push((int) $ticket->user_id)
                ->unique()
                ->reject(fn ($id) => $id === (int) auth()->id())
                ->values();

            $recipients = User::whereIn('id', $participantIds)->get();
            if ($recipients->isNotEmpty()) {
                $senderName = trim((auth()->user()->fname ?? '') . ' ' . (auth()->user()->lname ?? '')) ?: 'User';
                $excerpt = $request->filled('message')
                    ? Str::limit($request->message, 80)
                    : 'sent an attachment';
                $chatNotif = new \App\Notifications\ChatMessageNotification(
                    ticketId: $ticket->id,
                    ticketNumber: $ticket->ticket_number,
                    senderName: $senderName,
                    excerpt: $excerpt,
                );
                \App\Helpers\Realtime::notify($recipients, $chatNotif, 'ticket ' . $ticket->ticket_number);
            }
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'         => $chat->id,
                'message'    => $chat->message,
                'attachment'  => $chat->attachment ? asset($chat->attachment) : null,
                'sender_id'  => $chat->sender_id,
                'sender_name'=> trim(($chat->sender->fname ?? '') . ' ' . ($chat->sender->lname ?? '')) ?: 'User',
                'time'       => $chat->created_at?->format('h:i A') ?? now()->format('h:i A'),
                'is_me'      => $chat->sender_id === auth()->id()
            ]
        ]);
    }


    // Fetch Messages
    public function fetchMessages($ticketId)
    {
        $messages = TicketChat::with('sender')
            ->where('ticket_id', $ticketId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($chat) {
                return [
                    'id'          => $chat->id,
                    'message'     => $chat->message,
                    'sender_id'   => $chat->sender_id,
                    'attachment'  => $chat->attachment ? asset($chat->attachment) : null,
                    'sender_name' => trim(($chat->sender->fname ?? '') . ' ' . ($chat->sender->lname ?? '')) ?: 'User',
                    'time'        => $chat->created_at?->format('h:i A') ?? now()->format('h:i A'),
                    'is_me'       => $chat->sender_id === auth()->id()
                ];
            });

        // Current status lets the page catch up on a status change it missed
        // while the socket was down (or while it was polling instead).
        $ticket = DailyTicketRequest::find($ticketId, ['id', 'status', 'resolution_remarks']);

        return response()->json([
            'success'  => true,
            'messages' => $messages,
            'ticket'   => $ticket ? [
                'status'             => $ticket->status,
                'resolution_remarks' => $ticket->resolution_remarks,
            ] : null,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $ticket = DailyTicketRequest::findOrFail($id);
        $status = $request->input('status');

        $rules = [
            'status'              => 'required|in:in_progress,resolved,cancelled',
            'resolution_remarks'  => $status === 'resolved'
                ? 'required|string|max:2000'
                : 'nullable|string|max:2000',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $message = $this->applyStatus($ticket, $status, $request->input('resolution_remarks'));

        if ($message === null) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status provided.'
            ], 400);
        }

        if ($status === 'resolved') {
            $this->emailRequesterResolved($ticket);
        }

        // Respond right away so the UI updates while the email sends
        return BackgroundMail::respondNow(response()->json([
            'success' => true,
            'message' => $message,
            'ticket'  => $ticket
        ]));
    }

    /**
     * Send the "Ticket Resolved" email (with the resolution remarks) to the
     * requester who submitted the ticket. It goes out after the response.
     */
    private function emailRequesterResolved(DailyTicketRequest $ticket): void
    {
        $requester = User::find($ticket->user_id);

        if (!$requester || empty($requester->email)) {
            return;
        }

        $mailTicket = $ticket->fresh(['category', 'subcategory', 'resolver']);

        BackgroundMail::send($requester->email, new TicketResolvedMail($mailTicket, $requester), 'resolved ticket ' . $ticket->ticket_number);
    }

    /**
     * Move a ticket to another status.
     *
     * Updates the record, refreshes the dashboard cache, broadcasts the change
     * to the other open screens and notifies the requester. Returns a short
     * message, or null when the status is not a known one.
     */
    private function applyStatus(DailyTicketRequest $ticket, string $status, ?string $resolutionRemarks = null): ?string
    {
        if ($status === 'in_progress') {
            $ticket->update([
                'status'     => 'In Progress',
                'started_at' => Carbon::now(), // Sets started_at timestamp
            ]);
            $message = 'Ticket marked as In Progress.';
        } elseif ($status === 'resolved') {
            $ticket->update([
                'status'             => 'Resolved',
                'resolved_at'        => Carbon::now(), // Sets resolved_at timestamp
                'resolved_by'        => Auth::id(), // Staff who resolved it
                'resolution_remarks' => $resolutionRemarks, // How the issue was resolved
            ]);
            // Carry the resolver onto the feedback row so reports show who it's for
            ClientFeedback::where('ticket_id', $ticket->id)
                ->update(['resolved_by' => Auth::id()]);
            $message = 'Ticket marked as Resolved.';
        } elseif ($status === 'cancelled') {
            $ticket->update([
                'status'      => 'Cancelled',
            ]);
            $message = 'Ticket marked as Cancelled.';
        } else {
            return null;
        }

        $ticket->refresh();

        Cache::forever('dashboard_version', time()); // invalidate dashboard cache (atomic, no flush race)
        \App\Helpers\Realtime::broadcastToOthers(
            new \App\Events\TicketStatusUpdated($ticket),
            'ticket ' . $ticket->ticket_number
        );
        \App\Helpers\Realtime::broadcast(
            new \App\Events\TicketListUpdated($ticket->id, $ticket->status, 'status_changed'),
            'ticket ' . $ticket->ticket_number
        );

        // Notify the requester (own ticket status changed)
        if ($ticket->user_id && (int) $ticket->user_id !== (int) auth()->id()) {
            $requester = User::find($ticket->user_id);
            if ($requester) {
                $eventMap = [
                    'In Progress' => 'in_progress',
                    'Resolved'    => 'resolved',
                    'Cancelled'   => 'cancelled',
                ];
                $notif = new \App\Notifications\TicketEventNotification(
                    ticketId: $ticket->id,
                    ticketNumber: $ticket->ticket_number,
                    event: $eventMap[$ticket->status] ?? 'updated',
                    title: 'Ticket #' . $ticket->ticket_number . ' is now ' . $ticket->status,
                    message: 'Your ticket "' . Str::limit($ticket->issue_description, 80) . '" was marked as ' . $ticket->status . '.',
                );
                \App\Helpers\Realtime::notify([$requester], $notif, 'ticket ' . $ticket->ticket_number);
            }
        }

        return $message;
    }
}
