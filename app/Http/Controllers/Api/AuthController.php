<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Models\UserCode;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Login via email/password or phone/OTP
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'nullable|email|exists:users,email',
            'phone' => 'nullable|string|exists:users,phone',
            'password' => 'nullable|string|min:6',
            'otp' => 'nullable|string',
            'device_token' => 'nullable|string',
            'fcm_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $errors = implode(' ', $validator->errors()->all());
            return response()->json([
                'status' => false,
                'message' => $errors,
            ], 200);
        }

        $deviceToken = $request->device_token ?? $request->fcm_token;

        // Email & Password login
        if ($request->filled('email') && $request->filled('password')) {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid email or password',
                ], 200);
            }

            if ($deviceToken) {
                $user->fcm_token = trim($deviceToken);
                $user->save();
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'user' => $user,
                'fcm_token' => $user->fcm_token,
                'token' => $token,
            ]);
        }

        // Phone & OTP login
        if ($request->filled('phone') && $request->filled('otp')) {
            $userCode = UserCode::where('phone', $request->phone)
                                ->where('purpose', 'login')
                                ->latest()
                                ->first();

            if (!$userCode) {
                return response()->json(['status' => false, 'message' => 'OTP not found'], 200);
            }

            if (now()->gt($userCode->expires_at)) {
                return response()->json(['status' => false, 'message' => 'OTP expired'], 200);
            }

            if ($userCode->attempts >= 5) {
                return response()->json(['status' => false, 'message' => 'Too many attempts'], 200);
            }

            if (!Hash::check($request->otp, $userCode->code_hash)) {
                $userCode->increment('attempts');
                return response()->json(['status' => false, 'message' => 'Invalid OTP'], 200);
            }

            // OTP is valid; delete after successful login
            $userCode->delete();

            $user = User::where('phone', $request->phone)->first();

            if (!$user) {
                return response()->json(['status' => false, 'message' => 'User not found'], 200);
            }

            //check user is active
            if ($user->is_active !== '1' && $user->is_active !== 1 && $user->is_active !== true) {
                return response()->json(['status' => false, 'message' => 'User is not active'], 200);
            }

            if ($deviceToken) {
                $user->fcm_token = trim($deviceToken);
                $user->save();
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token,
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Provide either email/password or phone/OTP',
        ], 200);
    }

    /**
     * Logout current token
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Make avatar full URL
        $user->avatar = $user->avatar
            ? asset('storage/' . $user->avatar)
            : null;

        // Role-based extra data
        if ($user->role === 'labor') {
            $employee = Employee::where('user_id', $user->id)->first();
            $user->employee_details = $employee;
        } else {
            $userDetail = UserDetail::where('user_id', $user->id)->first();
            $user->user_details = $userDetail;
        }

        return response()->json([
            'status' => true,
            'user' => $user,
            'fcm_token' => $user->fcm_token,
        ], 200);
    }
}

