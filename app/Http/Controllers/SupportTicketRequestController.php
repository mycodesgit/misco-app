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
use App\Models\TicketDB\AuditTrailCategory;
use App\Models\TicketDB\AuditTrailCategorySub;
use App\Models\TicketDB\AuditTrailDailyTicketRequest;

class SupportTicketRequestController extends Controller
{
    public function index()
    {
        return view('pages.request.supportalltickets');
    }

    public function showsupportpending()
    {
        $authuseroffice = Auth::user()->office_id;

        $data = DailyTicketRequest::with(['requester.office', 'category', 'subcategory'])
            ->where('status', 'Pending')
            ->where('off_id', $authuseroffice)
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function showprogress()
    {
        $authuseroffice = Auth::user()->office_id;

        $data = DailyTicketRequest::with(['requester.office', 'category', 'subcategory'])
            ->where('status', 'In Progress')
            ->where('off_id', $authuseroffice)
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function showresolved()
    {
        $authuseroffice = Auth::user()->office_id;

        $data = DailyTicketRequest::with(['requester.office', 'category', 'subcategory'])
            ->where('status', 'Resolved')
            ->where('off_id', $authuseroffice)
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function showclosed()
    {
        $authuseroffice = Auth::user()->office_id;

        $data = DailyTicketRequest::with(['requester.office', 'category', 'subcategory'])
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
                $attachmentPath = $file->storeAs('ticket_attachments', $filename, 'public');
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
            broadcast(new \App\Events\TicketListUpdated($ticket->id, $ticket->status, 'created'));

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
                'remarks', 'attachment', 'status', 'created_at'
            ])
            ->with([
                'category:id,ticketcatname', // replace 'ticketcatname' with your actual column name
                'subcategory:id,ticketsubcatname',
                'requester:id,fname,lname,email',
                'requesteroffice:id,office_name,office_abbr',
                'supportoffice:id,office_abbr'
            ])
            ->findOrFail($ticketId);

        return view('pages.request.supportshowticket', compact('ticket'));
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

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $currentYear = date('Y');
            $senderId = auth()->id();
            $extension = $file->getClientOriginalExtension();

            // Format: senderid_year_timestamp.extension (e.g., 12_2026_1710000000.png)
            $fileName = $senderId . '_' . $currentYear . '_' . time() . '.' . $extension;

            // Save to storage/app/public/chat_attachments/2026/
            $attachmentPath = $file->storeAs("chat_attachments/{$currentYear}", $fileName, 'public');
        }

        $chat = TicketChat::create([
            'ticket_id'  => $request->ticket_id,
            'sender_id'  => auth()->id(),
            'message'    => $request->message ?? '',
            'attachment' => $attachmentPath ? 'storage/' . $attachmentPath : null,
        ]);

        // Load sender details for frontend rendering
        $chat->load('sender');
        broadcast(new \App\Events\MessageSent($chat))->toOthers();

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

        return response()->json([
            'success'  => true,
            'messages' => $messages
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $ticket = DailyTicketRequest::findOrFail($id);
        $status = $request->input('status');

        if ($status === 'in_progress') {
            $ticket->update([
                'status'     => 'In Progress',
                'started_at' => Carbon::now(), // Sets started_at timestamp
            ]);
            $message = 'Ticket marked as In Progress.';
        } elseif ($status === 'resolved') {
            $ticket->update([
                'status'      => 'Resolved',
                'resolved_at' => Carbon::now(), // Sets resolved_at timestamp
            ]);
            $message = 'Ticket marked as Resolved.';
        } elseif ($status === 'cancelled') {
            $ticket->update([
                'status'      => 'Cancelled',
            ]);
            $message = 'Ticket marked as Cancelled.';
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status provided.'
            ], 400);
        }

        $ticket->refresh();

        Cache::forever('dashboard_version', time()); // invalidate dashboard cache (atomic, no flush race)
        broadcast(new \App\Events\TicketStatusUpdated($ticket))->toOthers();
        broadcast(new \App\Events\TicketListUpdated($ticket->id, $ticket->status, 'status_changed'));

        return response()->json([
            'success' => true,
            'message' => $message,
            'ticket'  => $ticket
        ]);
    }
}
