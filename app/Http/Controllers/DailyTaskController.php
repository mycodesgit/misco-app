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
use App\Models\TicketDB\DailyTask;
use App\Models\TicketDB\AuditTrailCategory;
use App\Models\TicketDB\AuditTrailCategorySub;
use App\Models\TicketDB\AuditTrailDailyTask;


class DailyTaskController extends Controller
{
    public function index()
    {
        $cat = Category::where('status', 1)->orderBy('ticketcatname', 'ASC')->get();
        $subcat = Subcategory::where('status', 1)->orderBy('ticketsubcatname', 'ASC')->get();

        return view('pages.task.daily', compact('cat', 'subcat'));
    }

    public function show(Request $request)
    {
        $query = DailyTask::with(['user', 'category', 'subcategory']);

        // Filter by month if selected
        $year = $request->input('year', now()->year);

        if ($request->has('month') && !empty($request->month)) {
            $month = $request->month;

            $query->whereMonth('created_at', $month)
                ->whereYear('created_at', $year);
        } else {
            // If "All Months" is selected, filter by the chosen year
            $query->whereYear('created_at', $year);
        }

        $data = $query->orderBy('id', 'DESC')->get();

        return response()->json(['data' => $data]);
    }

    public function getSubcategories($categoryId)
    {
        $subcategories = Subcategory::where('cat_id', $categoryId)
            ->where('status', 1)
            ->select('id', 'ticketsubcatname')
            ->get();

        return response()->json($subcategories);
    }

    public function create(Request $request)
    {
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'dailytaskdesc' => 'required|string',
                'cat_id'        => 'nullable|exists:categories,id',
                'subcat_id'     => 'nullable|exists:subcategories,id',
                'status'        => 'nullable|in:Pending,In Progress,Completed',
            ]);

            try {
                $status = $request->input('status', 'Pending');

                $task = DailyTask::create([
                    'user_id'       => auth()->id(), // Use authenticated user ID
                    'cat_id'        => $request->input('cat_id'),
                    'subcat_id'     => $request->input('subcat_id'),
                    'dailytaskdesc' => $request->input('dailytaskdesc'),
                    'type'          => $request->input('type', 'Daily Task'),
                    'status'        => $status,
                    'started_at'    => $status === 'In Progress' ? now() : null,
                    'completed_at'  => $status === 'Completed' ? now() : null,
                ]);

                $userPayload = $task->toArray();

                $this->logAudit($request, 'Add_DailyTask', $userPayload);

                return response()->json([
                    'success' => true,
                    'message' => 'Daily task created successfully'
                ], 200);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create daily task: ' . $e->getMessage()
                ], 500);
            }
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'id'            => 'required|exists:dailytask,id',
            'cat_id'        => 'required|exists:categories,id',
            'subcat_id'     => 'required|exists:subcategories,id',
            'dailytaskdesc' => 'required|string',
            'status'        => 'required|in:Pending,In Progress,Completed',
        ]);

        try {
            $task = DailyTask::findOrFail($request->id);
            $newStatus = $request->status;

            $task->cat_id = $request->cat_id;
            $task->subcat_id = $request->subcat_id;
            $task->dailytaskdesc = $request->dailytaskdesc;

            // Manage transition timestamps
            if ($newStatus === 'In Progress' && $task->status !== 'In Progress' && is_null($task->started_at)) {
                $task->started_at = now();
            }

            if ($newStatus === 'Completed' && $task->status !== 'Completed') {
                $task->completed_at = now();
            } elseif ($newStatus !== 'Completed') {
                // Reset completed_at if status changed back from Completed
                $task->completed_at = null;
            }

            $task->status = $newStatus;
            $task->save();

            $newData = $task->fresh()->toArray();

            $auditPayload = [
                'after' => $newData,
            ];

            $this->logAudit($request, 'Edit_DailyTask', $auditPayload);

            return response()->json(['success' => true, 'message' => 'Updated Successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => 'Failed to update DailyTask!'], 404);
        }
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

        AuditTrailDailyTask::create([
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
