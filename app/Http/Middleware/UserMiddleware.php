<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Must be logged in
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Please login first.');
        }

        $user = Auth::user();

        // Only residents (residents) allowed here
        if ($user->role !== 'resident') {
            return redirect()->route('login')
                ->with('error', 'Unauthorized — only residents can access this area.');
        }

        // Block banned users
        if ($user->is_active == 0) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Your account is banned. Contact admin.');
        }

        // Must have an assignment to society
        if (!$user->assignment) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'You are not assigned to any society.');
        }

        // All good → continue
        return $next($request);
    }
}
