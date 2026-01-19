<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VehicleController extends Controller
{
    // Add new vehicle (Resident)
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'vehicle_number' => 'required|string|max:50|unique:vehicles,vehicle_number',
            'vehicle_type' => 'nullable|in:car,bike,truck,other',
            'color' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('id', $request->user_id)
            ->where('role', 'customer') // resident
            ->with('details')
            ->first();

        if (! $user) {
            return response()->json([
                'message' => 'Only residents/customers can register vehicles',
            ], 403);
        }

        // 🚫 MAX 3 VEHICLES RULE
        $vehicleCount = Vehicle::where('user_id', $user->id)->count();

        if ($vehicleCount >= 3) {
            return response()->json([
                'message' => 'Maximum 3 vehicles allowed per resident',
            ], 422);
        }

        $qr_token = 'VEH-' . strtoupper(Str::random(10));

        $vehicle = Vehicle::create([
            'user_id' => $user->id,
            'society_id' => $user->details->society_id,
            'vehicle_number' => $request->vehicle_number,
            'vehicle_type' => $request->vehicle_type ?? 'car',
            'vehicle_color' => $request->color,
            'qr_token' => $qr_token,
            'status' => 'pending', // default
        ]);

        return response()->json([
            'message' => 'Vehicle registered successfully (Pending approval)',
            'data' => $vehicle,
        ], 200);
    }

    // List all vehicles of a resident
    public function userVehicles($user_id)
    {
        $vehicles = Vehicle::where('user_id', $user_id)->get();
        return response()->json(['data' => $vehicles]);
    }

    // Record IN/OUT using QR Token (Gate Scan)
    public function scan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_token' => 'required|string|exists:vehicles,qr_token',
            'guard_id' => 'nullable|exists:users,id',
            'type' => 'required|in:in,out',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $vehicle = Vehicle::where('qr_token', $request->qr_token)->first();

        // store optional image
        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads/vehicle_scans', 'public');
        }

        if ($request->type === 'in') {
            // create log entry
            $log = VehicleLog::create([
                'vehicle_id' => $vehicle->id,
                'guard_id' => $request->guard_id,
                'entry_image' => $path,
                'status' => 'in',
                'entry_time' => now(),
                'notes' => $request->notes,
            ]);
            return response()->json(['message' => 'Vehicle entry recorded', 'data' => $log]);
        } else {
            // mark exit
            $log = VehicleLog::where('vehicle_id', $vehicle->id)->where('status', 'in')->latest()->first();
            if (! $log) {
                return response()->json(['message' => 'No active entry found for this vehicle'], 404);
            }

            $log->exit_time = now();
            $log->status = 'out';
            if ($path) {
                $log->exit_image = $path;
            }
            if ($request->filled('notes')) {
                $log->notes = $request->notes;
            }

            $log->save();

            return response()->json(['message' => 'Vehicle exit recorded', 'data' => $log]);
        }
    }
}
