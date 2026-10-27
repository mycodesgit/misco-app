<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Jenssegers\Agent\Agent;

use App\Models\TicketDB\AuditTrailUser;

class AccountController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load('office');

        return view('pages.user.account', compact('user'));
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
            // Only sent by the first-login (default password) modal
            'contactno'        => 'sometimes|required|string|max:50',
        ], [
            'password.confirmed' => 'New password confirmation does not match.',
            'contactno.required' => 'Contact Number is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        if (Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'New password must be different from the current password.',
            ], 422);
        }

        $changes = ['password' => Hash::make($request->password)];
        if ($request->has('contactno')) {
            $changes['contactno'] = trim($request->contactno);
        }

        $user->update($changes);

        $this->logAudit($request, 'Edit_OwnPassword', ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'message' => $request->has('contactno')
                ? 'Password and contact number updated successfully!'
                : 'Password updated successfully!',
        ]);
    }

    public function updateContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contactno' => 'required|string|max:50',
        ], [
            'contactno.required' => 'Contact Number is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $contactNo = trim($request->contactno);

        $user->update(['contactno' => $contactNo]);

        $this->logAudit($request, 'Edit_OwnContact', ['user_id' => $user->id, 'contactno' => $contactNo]);

        return response()->json([
            'success'   => true,
            'message'   => 'Contact number updated successfully!',
            'contactno' => $contactNo,
        ]);
    }

    private function logAudit(Request $request, string $action, array $payload): void
    {
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        AuditTrailUser::create([
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
