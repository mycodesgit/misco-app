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
        if(Auth::guard('web')->check()){
            if(!Auth::user()->role){
                return redirect()->route('getLogin')->with('error','You have to be admin user to access this page');
            }
            if(Auth::user()->hasRole('Requester')){
                if ($request->is('users') || $request->is('users/*')) {
                    return redirect()->route('dashboard.index')->with('error1', 'You do not have permission to access this page');
                }
            }
        }else{
            return redirect()->route('getLogin')->with('error','You have to Sign In first to access this page');
        }
        return $next($request)->header('Cache-Control','no-cache, no-store, max-age=0, must-revalidate')
                              ->header('Pragma','no-cache')
                              ->header('Expires','Sat 01 Jan 1990 00:00:00 GMT');
    }
}
