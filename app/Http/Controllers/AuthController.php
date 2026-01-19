<?php

namespace App\Http\Controllers;

use App\Models\ResidentAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Strong input validation (prevents injection & attacks)
        $request->validate([
            'email' => 'required|email:rfc,dns',
            'password' => 'required|string|min:6|max:64',
        ]);

        // Login throttle protection (5 attempts per minute)
        $this->checkRateLimit($request);

        $credentials = [
            'email' => strtolower(trim($request->email)),
            'password' => $request->password,
        ];

        // Attempt login
        if (!Auth::attempt($credentials, true)) {
            RateLimiter::hit($this->throttleKey($request), 60);

            return back()->withErrors(['email' => 'Invalid credentials.'])
                         ->withInput();
        }

        // 🔐 Prevent session fixation attacks
        $request->session()->regenerate();

        $user = Auth::user();

        // Check if banned
        if ($user->is_active == 0) {
            Auth::logout();
            return back()->withErrors(['email' => 'Your account has been banned.']);
        }

        // Super Admin Login
        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        // resident (Resident) Login
        if ($user->role === 'resident') {

            $assignment = ResidentAssignment::where('user_id', $user->id)->first();

            if (!$assignment) {
                Auth::logout();
                return back()->withErrors(['email' => 'You are not assigned to any society.']);
            }

            return redirect()->route('member.dashboard');
        }

        //  Unauthorized Role
        Auth::logout();
        return back()->withErrors(['email' => 'Unauthorized access.']);
    }



    // RATE LIMITER FUNCTION
    protected function checkRateLimit(Request $request)
    {
        $key = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors([
                'email' => 'Too many login attempts. Try again in 1 minute.',
            ]);
        }
    }

    protected function throttleKey(Request $request)
    {
        return Str::lower($request->email) . '|' . $request->ip();
    }
}
