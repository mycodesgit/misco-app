<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Carbon\Carbon;

use Jenssegers\Agent\Agent;

use App\Models\TicketDB\User;
use App\Models\TicketDB\UserRole;
use App\Models\TicketDB\UserAssignedTask;
use App\Models\TicketDB\Office;
use App\Models\TicketDB\AuditTrailUser;
use App\Models\TicketDB\AuditTrailUserAssignedTask;
use App\Models\TicketDB\Category;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('office')->get();
        $offices = Office::all();
        // Add on the Support Team page: service desk roles only (no "Requester")
        $roles = $this->assignableRoles()->where('rolename', '!=', 'Requester')->values();
        // Edit: any role, so a user can be moved between desk staff and requester
        $editRoles = $this->assignableRoles();
        // Every office's categories: the Assign Task Category modal shows each
        // staff member only the categories of their own office
        $cat = Category::where('status', 1)->orderBy('ticketcatname')->get();

        return view('pages.user.list', compact('users', 'offices', 'roles', 'editRoles', 'cat'));
    }

    public function requesterindex()
    {
        $users = User::with('office')->get();
        $offices = Office::all();
        // Add on the Requester page: requester-only accounts
        $roles = UserRole::where('rolename', '=', 'Requester')->get();
        // Edit: any role, so a requester can be promoted to desk staff
        $editRoles = $this->assignableRoles();

        $cat = Category::where('status', 1)->orderBy('ticketcatname')->get();

        return view('pages.user.client', compact('users', 'offices', 'roles', 'editRoles', 'cat'));
    }

    /**
     * Roles the logged-in user may give to others (Administrator only by admins).
     */
    private function assignableRoles()
    {
        return UserRole::when(Auth::user()->role !== 'Administrator', function ($query) {
            $query->where('rolename', '!=', 'Administrator');
        })->get();
    }

    public function create(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'email' => 'required|unique:users,email',
                'fname'    => 'required',
                'lname'    => 'required',
                'password' => 'required|min:6',
                'role'     => 'required|exists:users_role,rolename',
                'contactno' => 'nullable|string|max:50',
            ]);

            $userEmail = $request->input('email');

            if (User::where('email', $userEmail)->exists()) {
                return response()->json(['error' => true, 'message' => 'User already exists!']);
            }

            try {
                $user = User::create([
                    'lname'          => $request->input('lname'),
                    'fname'          => $request->input('fname'),
                    'mname'          => $request->input('mname'),
                    'email'          => $userEmail,
                    'contactno'      => $request->input('contactno'),
                    'password'       => Hash::make($request->input('password')),
                    'campus_id'      => $request->input('campus_id'),
                    'office_id'      => $request->input('office_id'),
                    'role'           => $request->input('role'),
                    'gender'         => $request->input('gender'),
                    'posted_by'      => Auth::id(),
                    'remember_token' => Str::random(60),
                ]);

                $userAssignedTask = UserAssignedTask::create([
                    'user_id'        => $user->id,
                    'taskassigned'   => $request->input('taskassigned'),
                    'aboutassigned'  => $request->input('aboutassigned'),
                ]);

                $userPayload = $user->makeHidden(['password', 'remember_token'])->toArray();
                $userAssignedTaskPayload = $userAssignedTask->toArray();
                // Abstracted Audit Trail
                $this->logAudit($request, 'Add_User', $userPayload);
                $this->logUserAssignedTaskAudit($request, 'Add_UserAssignedTask', $userAssignedTaskPayload);

                return response()->json(['success' => true, 'message' => 'User stored successfully!']);
            } catch (\Exception $e) {
                return response()->json(['error' => true, 'message' => 'Failed to store User!']);
            }
        }
    }

    public function show()
    {
        $users = User::with([
            'office',
            'assignedTasks:id,user_id,taskassigned',
        ])
        ->where('users.role', '!=', 'Requester')
        ->where('users.ustatus', '!=', 3)
        ->get();

        $data = $users->map(function ($user) {
            $assignedIds = $user->assignedTasks?->taskassigned ?? [];

            // Always return an array of strings
            $assignedIds = collect($assignedIds)
                ->map(fn ($id) => (string) $id)
                ->values()
                ->all();

            $user->taskassigned = $assignedIds;

            return $user;
        });

        return response()->json([
            'data' => $data
        ]);
    }

    public function requestershow()
    {
        $users = User::with([
            'office',
            'assignedTasks:id,user_id,taskassigned',
        ])
        ->where('users.ustatus', '!=', 3)
        ->where('users.role', '=', 'Requester')
        ->get();

        $data = $users->map(function ($user) {
            $assignedIds = $user->assignedTasks?->taskassigned ?? [];

            // Always return an array of strings
            $assignedIds = collect($assignedIds)
                ->map(fn ($id) => (string) $id)
                ->values()
                ->all();

            $user->taskassigned = $assignedIds;

            return $user;
        });

        return response()->json([
            'data' => $data
        ]);
    }


    public function update(Request $request)
    {
        $request->validate([
            'id'     => 'required|exists:users,id',
            'lname'  => 'required',
            'fname'  => 'required',
            'role'   => 'required|exists:users_role,rolename',
            'gender' => 'required',
            'contactno' => 'nullable|string|max:50',
        ]);

        try {
            $userEmail = $request->input('email');

            $existingUser = User::where('email', $userEmail)
                ->where('id', '!=', $request->input('id'))
                ->exists();

            if ($existingUser) {
                return response()->json(['error' => true, 'message' => 'Email already exists!']);
            }

            $user = User::findOrFail($request->input('id'));

            // Uncomment if you want to log before/after diffs:
            // $oldData = $user->makeHidden(['password', 'remember_token'])->toArray();

            $user->update([
                'lname'     => $request->input('lname'),
                'fname'     => $request->input('fname'),
                'mname'     => $request->input('mname'),
                'email'     => $userEmail,
                'contactno' => $request->input('contactno'),
                'office_id' => $request->input('office_id'),
                'role'      => $request->input('role'),
                'gender'    => $request->input('gender'),
                'campus_id' => $request->input('campus_id'),
                'isAllowed' => $request->input('isAllowed'),
            ]);

            $newData = $user->fresh()->makeHidden(['password', 'remember_token'])->toArray();

            $auditPayload = [
                // 'before' => $oldData,
                'after' => $newData,
            ];

            // Abstracted Audit Trail
            $this->logAudit($request, 'Edit_User', $auditPayload);

            return response()->json(['success' => true, 'message' => 'User updated successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => 'Failed to update User!']);
        }
    }

    public function userUpdatePassword(Request $request)
    {
        $request->validate([
            'id'       => 'required|exists:users,id',
            'password' => 'required|string|min:5',
        ]);

        try {
            $user = User::findOrFail($request->input('id'));
            $user->update([
                'password' => Hash::make($request->input('password'))
            ]);

            $newData = $user->fresh()->makeHidden(['password', 'remember_token'])->toArray();

            $auditPayload = [
                'after' => $newData,
            ];

            // Abstracted Audit Trail
            $this->logAudit($request, 'Edit_Password', $auditPayload);

            return response()->json(['success' => true, 'message' => 'User Password updated successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => 'Failed to update User Password!']);
        }
    }

    public function userUpdateStatus(Request $request)
    {
        $user = User::find($request->id);

        $request->validate([
            'id' => 'required|exists:users,id',
            'ustatus' => 'required|in:1,2,3',
        ]);

        try {
            $user = User::findOrFail($request->input('id'));
            $user->update([
                'ustatus' => $request->input('ustatus'),
            ]);

            $newData = $user->fresh()->makeHidden(['password', 'remember_token'])->toArray();

            $auditPayload = [
                'after' => $newData,
            ];

            // Abstracted Audit Trail
            $this->logAudit($request, 'Edit_Status', $auditPayload);

            return response()->json(['success' => true, 'message' => 'User Status updated successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => 'Failed to update User Status!']);
        }
    }

    public function userAssignTaskUpdate(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id',
            'usertask' => 'required',
        ]);

        try {
            $taskuser = UserAssignedTask::updateOrCreate(
                ['user_id' => $request->input('id')],
                ['taskassigned' => $request->input('usertask')]
            );

            $newData = $taskuser->fresh()->toArray();

            $auditPayload = [
                'after' => $newData,
            ];

            // Abstracted Audit Trail
            $this->logUserAssignedTaskAudit($request, 'Edit_Task_Assignment', $auditPayload);

            return response()->json(['success' => true, 'message' => 'User Task Assignment updated successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => 'Failed to update User Task Assignment!']);
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

        AuditTrailUser::create([
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

    private function logUserAssignedTaskAudit(Request $request, string $action, array $payload): void
    {
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        $browser  = $agent->browser();
        $platform = $agent->platform();

        AuditTrailUserAssignedTask::create([
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
}
