<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

use App\Models\TicketDB\AuditLog;

class LogoutController extends Controller
{
    public function logout(Request $request)
    {
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        $browser = $agent->browser();
        $platform = $agent->platform();

        $auditLogId = session('audit_log_id');

        // Record Logout Audit Trail
        if ($auditLogId) {
            AuditLog::where('id', $auditLogId)->update([
                'logout_at' => now(),
            ]);
        } elseif (auth()->check()) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'username' => auth()->user()->username,
                'action' => 'LOGOUT',
                'activity' => \App\Helpers\AuditActivity::describe('LOGOUT'),
                'ip_address' => $request->ip(),
                'user_agent' => $browser . ' on ' . $platform,
                'logout_at' => now(),
            ]);
        }

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('getLogin')->with('success', 'You have logged out successfully.');
    }
}

