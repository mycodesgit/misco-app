<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

/**
 * Restricts a group of routes to the users allowed to use one feature, e.g.
 * ->middleware('feature:daily_task'). Who is allowed is decided by
 * User::hasFeatureAccess(); everyone else is sent back to the dashboard.
 */
class FeatureAccess
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (!Auth::user() || !Auth::user()->hasFeatureAccess($feature)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to access this page.',
                ], 403);
            }

            return redirect()->route('dashboard.index')->with('error1', 'You do not have permission to access this page');
        }

        return $next($request);
    }
}
