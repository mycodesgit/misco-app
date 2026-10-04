<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
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
use App\Models\TicketDB\ClientSatisfactory;
use App\Models\TicketDB\AuditTrailDailyTicketRequest;
use App\Models\TicketDB\AuditTrailClientSatisfactory;

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

            $clientsat = ClientSatisfactory::create([
                'user_id'   => Auth::id(),
                'off_id'    => $request->input('off_id'),
                'cat_id'    => $request->input('cat_id'),
                'subcat_id' => $request->input('subcat_id'),
                'ticket_id' => $ticket->id,
            ]);

            DB::commit();

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
        $authuser = Auth::user()->id;

        $data = DailyTicketRequest::with(['requester.office', 'category', 'subcategory'])
            ->where('status', 'Pending')
            ->where('user_id', $authuser)
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function showreqprogress()
    {
        $data = DailyTicketRequest::with(['requester.office', 'category', 'subcategory'])
            ->where('status', 'In Progress')
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function showreqresolved()
    {
        $data = DailyTicketRequest::with(['requester.office', 'category', 'subcategory'])
            ->where('status', 'Resolved')
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function showreqclosed()
    {
        $data = DailyTicketRequest::with(['requester.office', 'category', 'subcategory'])
            ->where('status', 'Closed')
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['data' => $data]);
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
}
