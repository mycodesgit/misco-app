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

use App\Models\TicketDB\UserRole;
use App\Models\TicketDB\AuditTrailUserRole;

class UserRolesController extends Controller
{
    public function index()
    {
        return view('pages.user.utype');
    }

    public function show()
    {
        $data = UserRole::orderBy('rolename', 'ASC')->get();

        return response()->json(['data' => $data]);
    }

    public function create(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'rolename' => 'required',
            ]);

            $rolesName = $request->input('rolename');
            $existingRole = UserRole::where('rolename', $rolesName)->first();

            if ($existingRole) {
                return response()->json(['error' => true, 'message' => 'Role already exists!'],  404);
            }

            try {
                $urole = UserRole::create([
                    'off_id' => Auth::user()->office_id,
                    'rolename'  => $rolesName,
                ]);

                $userPayload = $urole->toArray();
                $this->logAudit($request, 'Add_Role', $userPayload);

                return response()->json(['success' => true, 'message' => 'Role stored successfully!'],  200);
            } catch (\Exception $e) {
                return response()->json(['error' => true, 'message' => 'Failed to add Role!'],  404);
            }
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'rolename' => 'required',
        ]);

        //try {
            $rolesName = $request->input('rolename');
            $existingRole = UserRole::where('rolename', $rolesName)->where('id', '!=', $request->input('id'))->first();

            if ($existingRole) {
                return response()->json(['error' => true, 'message' => 'Role already exists!'], 200);
            }

            $urole = UserRole::findOrFail($request->input('id'));
            $urole->update([
                'rolename'  => $rolesName,
                'status'  => $request->input('status'),
            ]);

            $newData = $urole->fresh()->toArray();

            $auditPayload = [
                'after' => $newData,
            ];

            $this->logAudit($request, 'Edit_Role', $auditPayload);

            return response()->json(['success' => true, 'message' => 'Updated Successfully'], 200);
        //} catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => 'Failed to update Role!'], 404);
        //}
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

        AuditTrailUserRole::create([
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
