<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

use App\Models\TicketDB\AuditLog;

class LoginController extends Controller
{
    public function getLogin()
    {
        return view('auth.login');
    }

    public function postLogin(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required|min:5|max:12',
        ]);

        $remember = $request->has('remember');

        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        $browser = $agent->browser();
        $platform = $agent->platform();

        if (auth()->attempt(['email' => $request->email, 'password' => $request->password], $remember)) {
            $user = auth()->user();

            // Record Login Audit Trail
            $auditLog = AuditLog::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'action' => 'LOGIN',
                'activity' => \App\Helpers\AuditActivity::describe('LOGIN', [], trim(($user->fname ?? '') . ' ' . ($user->lname ?? '')) ?: $user->email),
                'ip_address' => $request->ip(),
                'user_agent' => $browser . ' on ' . $platform,
                'login_at' => now(),
            ]);

            // Store current audit log ID and login time in session
            session([
                'login_time' => now(),
                'audit_log_id' => $auditLog->id,
            ]);

            return redirect()->route('dashboard.index')->with('success', 'Login Successfully');
        }

        AuditLog::create([
            'email' => $request->email,
            'action' => 'FAILED_LOGIN',
            'activity' => \App\Helpers\AuditActivity::describe('FAILED_LOGIN', ['email' => $request->email]),
            'ip_address' => $request->ip(),
            'user_agent' => $browser . ' on ' . $platform,
            'login_at' => now(),
        ]);

        return redirect()->back()->with('error', 'Invalid Credentials');
    }
}
