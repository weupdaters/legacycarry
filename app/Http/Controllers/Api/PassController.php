<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Audit;
use App\Helpers\Notify;
use App\Http\Controllers\Controller;
use App\Models\Pass;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PassController extends Controller
{
    /**
     * Resident creates a new pass
     */
    public function create(Request $request)
    {
        $request->validate([
            'society_id' => 'required|integer',
            'created_by' => 'required|exists:users,id',
            'pass_type' => 'required|in:visitor,vehicle,delivery,staff,Maintenance',
            'guest_name' => 'nullable|string',
            'guest_phone' => 'nullable|string',
            'vehicle_number' => 'nullable|string',
            'vehicle_type' => 'nullable|string',
            'vehicle_owner_name' => 'nullable|string',
            'purpose' => 'nullable|string',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date',
        ]);

        // Generate secure token
        $token = Str::uuid()->toString();

        // Delivery passes require OTP
        $otp = $request->pass_type === 'delivery'
            ? rand(100000, 999999)
            : null;

        $pass = Pass::create([
            'society_id' => $request->society_id,
            'created_by' => $request->created_by,
            'pass_type' => $request->pass_type,
            'guest_name' => $request->guest_name,
            'guest_phone' => $request->guest_phone,
            'vehicle_number' => $request->vehicle_number,
            'vehicle_type' => $request->vehicle_type,
            'vehicle_owner_name' => $request->vehicle_owner_name,
            'purpose' => $request->purpose,
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until,
            'token' => $token,
            'otp' => $otp,
        ]);

        // Notify resident
        Notify::to($pass->created_by, 'Pass Created', 'Your pass is successfully created.', [
            'pass_id' => $pass->id,
            'token' => $token,
        ]);

        // Log action
        Audit::log($request->created_by, 'create_pass', 'passes', $pass->id, null, $pass->toArray());

        return response()->json([
            'status' => true,
            'message' => 'Pass created successfully',
            'pass' => $pass,
        ]);
    }


    /**
     * Resident views their pass list
     */
    public function myPasses($user_id)
    {
        $passes = Pass::where('created_by', $user_id)
                        ->orderBy('id', 'desc')
                        ->get();

        return response()->json([
            'status' => true,
            'passes' => $passes,
        ]);
    }


    /**
     * Get pass details
     */
    public function show($id)
    {
        $pass = Pass::find($id);

        if (!$pass) {
            return response()->json(['status' => false, 'message' => 'Pass not found'], 404);
        }

        return response()->json([
            'status' => true,
            'pass' => $pass,
        ]);
    }
}
