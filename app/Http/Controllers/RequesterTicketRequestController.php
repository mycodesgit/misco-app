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

class RequesterTicketRequestController extends Controller
{
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
            'issue_description' => 'required|string',
            'remarks'           => 'nullable|string',
            'attachment'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
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

                // Store in storage/app/public/ticket_attachments/{YEAR}/
                $file->storeAs($folderPath, $filename, 'public');

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
                'contactno'         => $request->contactno,
                'attachment'        => $attachmentPath,
                'status'            => 'Pending',
            ]);

            $clientsat = ClientFeedback::create([
                'user_id'   => Auth::id(),
                'cat_id'    => $request->input('cat_id'),
                'subcat_id' => $request->input('subcat_id'),
                'ticket_id' => $ticket->id,
            ]);

            DB::commit();

            Cache::forever('dashboard_version', time()); // invalidate dashboard cache (atomic, no flush race)
            broadcast(new \App\Events\TicketListUpdated($ticket->id, $ticket->status, 'created'));

            // 5. Log Audit
            $userPayload = $ticket->toArray();
            $this->logAudit($request, 'Add_DailyTicket', $userPayload);

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

    public function showreqpending()
    {
        $authuserID = Auth::user()->id;

        $data = DailyTicketRequest::with(['requester.office', 'category', 'subcategory'])
            ->where('status', 'Pending')
            ->where('user_id', $authuserID)
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function showreqprogress()
    {
        $authuserID = Auth::user()->id;

        $data = DailyTicketRequest::with(['requester.office', 'category', 'subcategory'])
            ->where('status', 'In Progress')
            ->where('user_id', $authuserID)
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function showreqresolved()
    {
        $authuserID = Auth::user()->id;

        $data = DailyTicketRequest::with(['requester.office', 'category', 'subcategory'])
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
        });

        return response()->json(['data' => $data]);
    }

    public function showreqclosed()
    {
        $authuserID = Auth::user()->id;

        $data = DailyTicketRequest::with(['requester.office', 'category', 'subcategory'])
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
            'actiondata' => json_encode($payload),
            'ip_address' => $request->ip(),
            'user_agent' => $browser . ' on ' . $platform,
        ]);
    }
}
