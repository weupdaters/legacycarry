<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Models\UserCode;
use App\Models\UserDetail;
use App\Models\UserDocument;
use App\Models\UserPaymentMethod;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    protected $sms;

    public function __construct(SmsService $sms)
    {
        $this->sms = $sms;
    }

    public function loginWithOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string',
            'device_token' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $phone = $request->phone;
        $otp = $request->otp;

        // Find user
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ]);
        }

        if ($user->is_active == 0) {
            return response()->json([
                'status' => false,
                'message' => 'User is inactive. Please contact support.',
            ]);
        }

        if ($user->deleted_at) {
            return response()->json([
                'status' => false,
                'message' => 'User account is deleted. Please contact support.',
            ]);
        }

        // Get latest OTP for login
        $userCode = UserCode::where('phone', $phone)
            ->where('purpose', 'login')
            ->latest()
            ->first();



        if (!$userCode) {
            return response()->json([
                'status' => false,
                'message' => 'OTP not found',
            ]);
        }

        // Check OTP expiry
        if (now()->greaterThan($userCode->expires_at)) {
            return response()->json([
                'status' => false,
                'message' => 'OTP expired',
            ]);
        }

        // Check max attempts
        if ($userCode->attempts >= 5) {
            return response()->json([
                'status' => false,
                'message' => 'Too many attempts. Please request a new OTP.',
            ]);
        }

        // Verify OTP
        if (!Hash::check($otp, $userCode->code_hash)) {
            $userCode->increment('attempts');

            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP',
            ]);
        }

        // OTP verified → delete it
        $userCode->delete();



        // ✅ Save device token (important)
        $user->fcm_token = $request->device_token;
        $user->save();

        // OPTIONAL (recommended): revoke old tokens
        $user->tokens()->delete();

        // Load role-based details
        if ($user->role === 'labor') {
            $user->employee_details = Employee::where('user_id', $user->id)->first();
            // save employees latitude and longitude if provided
            if ($request->has('latitude') && $request->has('longitude')) {
                $employee = Employee::where('user_id', $user->id)->first();
                if ($employee) {
                    $employee->latitude = $request->latitude;
                    $employee->longitude = $request->longitude;
                    $employee->save();
                }
            }
        } else {
            $user->user_details = UserDetail::where('user_id', $user->id)->first();
        }

        // Generate auth token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'role' => $user->role,
                'verification_status' => $user->verification_status,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'fcm_token' => $user->fcm_token,
                'details' => $user->role === 'labor'
                    ? $user->employee_details
                    : $user->user_details,
            ],
            'token' => $token,
        ]);
    }




    public function registerAndVerify(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|unique:users,phone|max:20',
            'email' => 'required|email|unique:users,email',
            'role' => ['required', Rule::in(['labor','customer','guard','super_admin'])],
            'is_active' => 'required|boolean',
            'otp' => 'required|string',
            'user_residential_status' => 'nullable|in:society,individual',
            'user_society_id' => 'nullable|exists:societies,id',

            // Employee-specific fields
            'experience_years' => 'required|string|max:50',
            'services' => 'required|array',
            'work_radius_km' => 'required|integer',
            'hourly_rate' => 'required|numeric',
            'availability' => 'required|string',
            'about' => 'nullable|string',
            'id_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'selfie' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            // Additional fields
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'gender' => 'nullable',
            'date_of_birth' => 'nullable|date_format:d-m-Y',
            'marital_status' => 'nullable',
            'occupation' => 'nullable|string|max:100',
            'floor_number' => 'nullable|string|max:10',
            'flat_id' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 200);
        }

        $phone = $request->phone;
        $otp = $request->otp;

        // Verify OTP if phone is provided
        if ($phone) {
            $userCode = UserCode::where('phone', $phone)
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

            if (!Hash::check($otp, $userCode->code_hash)) {
                $userCode->increment('attempts');
                return response()->json(['status' => false, 'message' => 'Invalid OTP'], 200);
            }

            // OTP is valid, delete code
            $userCode->delete();
        }

        $last = Employee::orderBy('id', 'desc')->first();
        $num = $last ? intval(substr($last->id_number, 3)) + 1 : 1;

        $idNumber = 'EMP' . str_pad($num, 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();

        try {
            // Create User
            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone ?? null,
                'email' => $request->email,
                'password' => Hash::make(Str::random(10)),
                'role' => $request->role,
                'user_society_id' => $request->user_society_id,
                'user_residential_status' => $request->user_residential_status ?? ($request->user_society_id ? 'society' : 'individual'),
                'is_active' => $request->is_active,
                'aadhaar_number' => $request->aadhaar_number,
            ]);

            // Handle file uploads
            $idProofPath = null;
            $selfiePath = null;

            if ($request->hasFile('id_proof')) {
                $idProofFolder = 'employees/id_proofs';
                if (!Storage::disk('public')->exists($idProofFolder)) {
                    Storage::disk('public')->makeDirectory($idProofFolder);
                }
                $idProofPath = $request->file('id_proof')->store($idProofFolder, 'public');
            }

            if ($request->hasFile('selfie')) {
                $selfieFolder = 'employees/selfies';
                if (!Storage::disk('public')->exists($selfieFolder)) {
                    Storage::disk('public')->makeDirectory($selfieFolder);
                }
                $selfiePath = $request->file('selfie')->store($selfieFolder, 'public');
            }

            // Only create Employee if role is 'labor'
            $employee = null;
            if ($request->role === 'labor') {
                $employee = Employee::create([
                    'user_id' => $user->id,
                    'experience_years' => $request->experience_years,
                    'services' => $request->services,
                    'work_radius_km' => $request->work_radius_km ?? 5,
                    'hourly_rate' => $request->hourly_rate ?? 0,
                    'availability' => $request->availability ?? 'Full-Time',
                    'about' => $request->about,
                    'id_number' => $idNumber,
                    'id_proof_path' => $idProofPath,
                    'selfie_path' => $selfiePath,
                    'phone_verified' => 1,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'address' => $request->address,
                    'emergency_contact_name' => $request->emergency_contact_name,
                    'emergency_contact_phone' => $request->emergency_contact_phone,
                    'gender' => $request->gender,
                    'date_of_birth' => $request->date_of_birth ? Carbon::createFromFormat('d-m-Y', $request->date_of_birth) : null,
                    'marital_status' => $request->marital_status,
                    'occupation' => $request->occupation,
                    'floor_number' => $request->floor_number,
                    'flat_id' => $request->flat_id,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'User registered and verified successfully',
                'user' => $user,
                'employee' => $employee,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Registration failed: ' . $e->getMessage(),
            ], 200);
        }
    }


    public function sendPhoneVerification(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'country_code' => 'required|string',
        ]);

        $code = rand(100000, 999999); // 6-digit code


        $userCode = UserCode::updateOrCreate(
            [
                'phone' => $request->phone,
                'purpose' => 'login',
            ],
            [
                'user_id' => auth()->id(), // optional if user logged in
                'country_code' => $request->country_code,
                'code_hash' => Hash::make($code),
                'expires_at' => Carbon::now()->addMinutes(10),
                'attempts' => 0,
            ]
        );
        // add coubntry code to mobile number
        $mobile = $request->country_code . $request->phone;
        $response = $this->sms->sendOtp($mobile, $code);

        // Here you can send SMS with $code using your preferred SMS service

        return response()->json([
            'status' => true,
            'message' => 'Verification code sent successfully',
            'code' => $code, // remove this in production!
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'experience_years' => 'nullable|string|max:50',
            'services' => 'nullable|array',
            'work_radius_km' => 'nullable|integer',
            'hourly_rate' => 'nullable|numeric',
            'availability' => 'nullable|string|max:50',
            'about' => 'nullable|string',
            'id_number' => 'required|string|max:100',
            'id_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            'selfie' => 'nullable|file|mimes:jpg,jpeg,png',
        ]);

        $data = $request->only([
            'user_id', 'experience_years', 'services', 'work_radius_km',
            'hourly_rate', 'availability', 'about', 'id_number',
        ]);

        // Handle file uploads
        if ($request->hasFile('id_proof')) {
            $data['id_proof_path'] = $request->file('id_proof')->store('id_proofs', 'public');
        }

        if ($request->hasFile('selfie')) {
            $data['selfie_path'] = $request->file('selfie')->store('selfies', 'public');
        }

        $employee = Employee::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Employee data saved successfully',
            'employee' => $employee,
        ]);
    }


    public function residentUsersCreate(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|unique:users,phone|max:20',
            'email' => 'required|email|unique:users,email',
            'role' => ['required', Rule::in(['customer','guard'])],
            'is_active' => 'required|boolean',
            'otp' => 'required|string',
            'flat_id' => 'nullable|exists:flats,id',
            'floor_number' => 'nullable|string|max:50',
            // UserDetails fields
            'society_id' => 'nullable|exists:societies,id',
            'user_residential_status' => 'nullable|in:society,individual',
            'full_name' => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable',
            'occupation' => 'nullable|string|max:100',
            'marital_status' => 'nullable',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 200);
        }

        $phone = $request->phone;
        $otp = $request->otp;

        // Verify OTP if phone is provided
        if ($phone) {
            $userCode = UserCode::where('phone', $phone)
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

            if (!Hash::check($otp, $userCode->code_hash)) {
                $userCode->increment('attempts');
                return response()->json(['status' => false, 'message' => 'Invalid OTP'], 200);
            }

            // OTP is valid, delete code
            $userCode->delete();
        }

        DB::beginTransaction();

        try {
            // Create User
            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone ?? null,
                'email' => $request->email,
                'password' => Hash::make(Str::random(10)),
                'role' => $request->role,
                'is_active' => $request->is_active,
                'user_society_id' => $request->society_id ?? 0,
                'user_residential_status' => $request->user_residential_status ?? ($request->society_id ? 'society' : 'individual'),
            ]);

            // Create UserDetails
            $userDetail = UserDetail::create([
                'user_id' => $user->id,
                'society_id' => $request->society_id ?? 0,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone ?? null,
                'address' => $request->address,
                'flat_id' => $request->flat_id,
                'floor_number' => $request->floor_number,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'occupation' => $request->occupation,
                'marital_status' => $request->marital_status,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'User registered and verified successfully',
                'user' => $user,
                'user_detail' => $userDetail,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Registration failed: ' . $e->getMessage(),
            ], 200);
        }
    }

    public function updateProfile(Request $request, $user_id)
    {
        $user = User::find($user_id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        /* -------------------------------------------------
           BASE VALIDATION (PARTIAL UPDATE)
        --------------------------------------------------*/
        $rules = [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user_id,
            'phone' => 'sometimes|nullable|string|max:25|unique:users,phone,' . $user_id,
            'language' => 'sometimes|string|max:50',
            'region' => 'sometimes|string|max:50',
            'avatar' => 'sometimes|image|mimes:jpg,jpeg,png',
        ];

        /* -------------------------------------------------
           LABOR ROLE → EMPLOYEES TABLE
        --------------------------------------------------*/
        if ($user->role === 'labor') {
            $rules = array_merge($rules, [
                'experience_years' => 'sometimes|string|max:50',
                'services' => 'sometimes',
                'work_radius_km' => 'sometimes|integer',
                'hourly_rate' => 'sometimes|numeric',
                'availability' => 'sometimes|string|max:50',
                'about' => 'sometimes|string',
                'id_number' => 'sometimes|string|max:100',
                'id_proof' => 'sometimes|file|mimes:jpg,jpeg,png,pdf|max:4096',
                'selfie' => 'sometimes|image|mimes:jpg,jpeg,png|max:4096',
                'latitude' => 'sometimes|numeric',
                'longitude' => 'sometimes|numeric',
                'address' => 'sometimes|string',
                'emergency_contact_name' => 'sometimes|string|max:100',
                'emergency_contact_phone' => 'sometimes|string|max:20',
                'gender' => 'sometimes',
                'date_of_birth' => 'sometimes|date',
                'marital_status' => 'sometimes',
                'occupation' => 'sometimes|string|max:100',
                'floor_number' => 'sometimes|string|max:10',
                'flat_id' => 'sometimes|string|max:10',
            ]);
        }
        /* -------------------------------------------------
           NON-LABOR → USER_DETAILS TABLE
        --------------------------------------------------*/ else {
            $rules = array_merge($rules, [
                'society_id' => 'sometimes|exists:societies,id',
                'full_name' => 'sometimes|string|max:100',
                'address' => 'sometimes|string|max:255',
                'flat_id' => 'sometimes|integer',
                'floor_number' => 'sometimes|integer',
                'date_of_birth' => 'sometimes|date',
                'gender' => 'sometimes',
                'occupation' => 'sometimes|string',
                'marital_status' => 'sometimes',
                'emergency_contact_name' => 'sometimes|string|max:100',
                'emergency_contact_phone' => 'sometimes|string|max:20',
            ]);
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {

            /* -------------------------------------------------
               AVATAR UPDATE
            --------------------------------------------------*/
            if ($request->hasFile('avatar')) {
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $user->avatar = $request->file('avatar')
                    ->store('uploads/avatars', 'public');
            }

            /* -------------------------------------------------
               UPDATE USERS (ONLY PROVIDED FIELDS)
            --------------------------------------------------*/
            $user->fill($request->only([
                'name',
                'email',
                'phone',
                'language',
                'region',
            ]));
            $user->save();

            /* -------------------------------------------------
               LABOR → EMPLOYEES
            --------------------------------------------------*/
            if ($user->role === 'labor') {

                $employeeData = $request->only([
                    'experience_years',
                    'services',
                    'work_radius_km',
                    'hourly_rate',
                    'availability',
                    'about',
                    'id_number',
                    'latitude',
                    'longitude',
                    'address',
                    'emergency_contact_name',
                    'emergency_contact_phone',
                    'gender',
                    'date_of_birth',
                    'marital_status',
                    'occupation',
                    'floor_number',
                    'flat_id',
                ]);

                if ($request->hasFile('id_proof')) {
                    $employeeData['id_proof_path'] =
                        $request->file('id_proof')->store('uploads/labor/id-proof', 'public');
                }

                if ($request->hasFile('selfie')) {
                    $employeeData['selfie_path'] =
                        $request->file('selfie')->store('uploads/labor/selfie', 'public');
                }

                $employee = Employee::updateOrCreate(
                    ['user_id' => $user->id],
                    $employeeData
                );

                DB::commit();

                return response()->json([
                    'message' => 'Labor profile updated successfully',
                    'data' => [
                        'user' => $user->fresh(),
                        'employee' => $employee,
                    ],
                ], 200);
            }

            /* -------------------------------------------------
               NON-LABOR → USER_DETAILS
            --------------------------------------------------*/
            $userDetail = UserDetail::updateOrCreate(
                ['user_id' => $user->id],
                $request->only([
                    'society_id',
                    'flat_id',
                    'floor_number',
                    'full_name',
                    'address',
                    'date_of_birth',
                    'gender',
                    'occupation',
                    'marital_status',
                    'emergency_contact_name',
                    'emergency_contact_phone',
                ])
            );

            DB::commit();

            return response()->json([
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => $user->fresh(),
                    'user_details' => $userDetail,
                ],
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Profile update failed'. $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    // Update Language & Region
    public function updateLanguageRegion(Request $request, $user_id)
    {
        $user = User::find($user_id);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'language' => 'required|string|max:50',
            'region' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->language = $request->language;
        $user->region = $request->region;
        $user->save();

        return response()->json([
            'message' => 'Language and region updated successfully',
            'data' => $user,
        ]);
    }

    public function tempdeleteAccount(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }

        // already deleted check
        if ($user->deleted_at) {
            return response()->json([
                'status' => false,
                'message' => 'User already deleted',
            ]);
        }

        $user->deleted_at = now();
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'User deleted successfully',
        ]);

    }
}
