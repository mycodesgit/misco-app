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
        ], [
            'password.confirmed' => 'New password confirmation does not match.',
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

        $user->update(['password' => Hash::make($request->password)]);

        $this->logAudit($request, 'Edit_OwnPassword', ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully!',
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
            'actiondata' => json_encode($payload),
            'ip_address' => $request->ip(),
            'user_agent' => $agent->browser() . ' on ' . $agent->platform(),
        ]);
    }
}
