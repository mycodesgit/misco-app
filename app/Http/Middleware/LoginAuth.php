<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class LoginAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!Auth::guard('web')->check()){
            return redirect()->route('getLogin')->with('error','You have to Sign In first to access this page');
        }

        $user = Auth::guard('web')->user();

        // Accounts without a role, or disabled/deleted while still signed in
        // (open session or "Remember me" cookie), are signed out right away
        if(!$user->role || $user->isBlocked()){
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Your account is disabled. Please contact the administrator.'], 401);
            }

            return redirect()->route('getLogin')->with('error_disabled', 'Your account is disabled. Please contact the administrator.');
        }

        return $next($request)->header('Cache-Control','no-cache, no-store, max-age=0, must-revalidate')
                              ->header('Pragma','no-cache')
                              ->header('Expires','Sat 01 Jan 1990 00:00:00 GMT');
    }
}
