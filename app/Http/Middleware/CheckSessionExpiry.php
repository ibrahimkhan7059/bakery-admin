<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CheckSessionExpiry
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is logged in
        if (Auth::check()) {
            // Get last activity time
            $lastActivity = session('last_activity');
            
            // If last activity exists and more than 30 minutes have passed
            if ($lastActivity && Carbon::parse($lastActivity)->addMinutes(30)->isPast()) {
                // Clear session and logout user
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')->with('message', 'Session expired. Please login again.');
            }
            
            // Update last activity timestamp
            session(['last_activity' => Carbon::now()]);
        }

        return $next($request);
    }
} 