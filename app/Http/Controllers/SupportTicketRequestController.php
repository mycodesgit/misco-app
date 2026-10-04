<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
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
        $data = DailyTicketRequest::with(['requester.office', 'category', 'subcategory'])
            ->where('status', 'In Progress')
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function showresolved()
    {
        $data = DailyTicketRequest::with(['requester.office', 'category', 'subcategory'])
            ->where('status', 'Resolved')
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function showclosed()
    {
        $data = DailyTicketRequest::with(['requester.office', 'category', 'subcategory'])
            ->where('status', 'Closed')
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
            'message'   => 'required|string',
        ]);

        $chat = TicketChat::create([
            'ticket_id' => $request->ticket_id,
            'sender_id' => auth()->id(),
            'message'   => $request->message,
        ]);

        // Load sender details for frontend rendering
        $chat->load('sender');

        return response()->json([
            'success' => true,
            'data'    => [
                'id'         => $chat->id,
                'message'    => $chat->message,
                'sender_id'  => $chat->sender_id,
                'sender_name'=> $chat->sender->fname . ' ' . $chat->sender->lname,
                'time'       => $chat->created_at->format('h:i A'),
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
                    'sender_name' => $chat->sender->fname . ' ' . $chat->sender->lname,
                    'time'        => $chat->created_at->format('h:i A'),
                    'is_me'       => $chat->sender_id === auth()->id()
                ];
            });

        return response()->json([
            'success'  => true,
            'messages' => $messages
        ]);
    }
}
