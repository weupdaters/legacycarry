<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Audit;
use App\Helpers\Notify;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ParkingLog;
use App\Models\Pass;
use App\Models\ScanHistory;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuardController extends Controller
{
    /**
     * Identify the token across Pass, Appointment, Vehicle.
     * Returns type and record snapshot.
     */

    private function identifyTokenRecord(string $requestToken)
    {
        // 1) Pass (visitor)
        $passRecord = Pass::where('token', $requestToken)->first();
        if ($passRecord) {
            return ['type' => 'pass', 'record' => $passRecord];
        }

        // 2) Appointment (worker) - match entry or exit tokens
        $appointmentRecord = Appointment::where('entry_token', $requestToken)
            ->orWhere('exit_token', $requestToken)
            ->first();
        if ($appointmentRecord) {
            return ['type' => 'appointment', 'record' => $appointmentRecord];
        }

        // 3) Vehicle (resident vehicle QR token)
        $vehicleRecord = Vehicle::where('qr_token', $requestToken)->first();
        if ($vehicleRecord) {
            return ['type' => 'vehicle', 'record' => $vehicleRecord];
        }

        return null;
    }


    /**
    * Endpoint: POST /api/guard/identify
    * Guard scans token first — returns type and details (no state change).
    */
    public function identify(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'guard_id' => 'required|exists:users,id',
        ]);

        $token = $request->token;
        $guard = User::find($request->guard_id);

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ VISITOR / DELIVERY PASS
        |--------------------------------------------------------------------------
        */
        $pass = Pass::where('token', $token)->first();
        if ($pass) {

            $canApproveEntry =
                !$pass->token_used &&
                now()->between($pass->valid_from, $pass->valid_until);

            $canApproveExit =
                $pass->token_used &&
                is_null($pass->scanned_out_at);

            return response()->json([
                'status' => true,
                'type' => $pass->pass_type, // visitor | delivery | staff | etc
                'can_approve_entry' => $canApproveEntry,
                'can_approve_exit' => $canApproveExit,
                'data' => [
                    'name' => $pass->guest_name,
                    'phone' => $pass->guest_phone,
                    'purpose' => $pass->purpose,
                    'otp_required' => $pass->pass_type === 'delivery',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ JOB APPOINTMENT (WORKER)
        |--------------------------------------------------------------------------
        */
        $appointment = Appointment::where('entry_token', $token)
            ->orWhere('exit_token', $token)
            ->first();

        if ($appointment) {

            $isEntryToken = $appointment->entry_token === $token;
            $isExitToken = $appointment->exit_token === $token;

            $canApproveEntry =
                $isEntryToken &&
                !$appointment->entry_token_used &&
                ($appointment->advance_amount == 0 || $appointment->advance_paid);

            $canApproveExit =
                $isExitToken &&
                !$appointment->exit_token_used &&
                $appointment->final_paid;

            return response()->json([
                'status' => true,
                'type' => 'worker',
                'can_approve_entry' => $canApproveEntry,
                'can_approve_exit' => $canApproveExit,
                'data' => [
                    'avatar' => optional($appointment->employer)->avatar,
                    'worker_name' => optional($appointment->employer)->name ?? 'Worker',
                    'job_type' => optional($appointment->jobApplication)->name ?? 'Service',
                    'payment_status' => $appointment->advance_paid
                        ? 'Advance Paid'
                        : 'Payment Pending',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ RESIDENT VEHICLE
        |--------------------------------------------------------------------------
        */
        $vehicle = Vehicle::where('qr_token', $token)->first();
        if ($vehicle) {

            $lastLog = VehicleLog::where('vehicle_id', $vehicle->id)
                ->latest()
                ->first();

            $isInside = $lastLog && $lastLog->direction === 'in';

            return response()->json([
                'status' => true,
                'type' => 'vehicle',
                'can_approve_entry' => !$isInside,
                'can_approve_exit' => $isInside,
                'data' => [
                    'vehicle_number' => $vehicle->vehicle_number,
                    'vehicle_type' => ucfirst($vehicle->vehicle_type),
                    'color' => $vehicle->color,
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | ❌ INVALID TOKEN
        |--------------------------------------------------------------------------
        */
        return response()->json([
            'status' => false,
            'message' => 'Invalid or expired token',
        ], 404);
    }



    /**
     * Approve entry for a token (unified).
     * POST /api/guard/approve-entry
     */
    public function approveEntry(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'guard_id' => 'required|integer|exists:users,id',
            'otp' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $requestToken = $request->input('token');
        $guardUser = User::where('id', $request->input('guard_id'))->where('role', 'guard')->first();

        if (!$guardUser) {
            return response()->json(['status' => false, 'message' => 'Invalid guard'], 403);
        }

        $providedOtp = $request->input('otp');
        $additionalNotes = $request->input('notes');

        $identified = $this->identifyTokenRecord($requestToken);

        if (!$identified) {
            ScanHistory::create([
                'society_id' => $guardUser->society_id ?? null,
                'guard_id' => $guardUser->id,
                'token' => $requestToken,
                'token_type' => 'pass',
                'subject_id' => null,
                'action' => 'scan_failed',
                'payload' => null,
            ]);
            return response()->json(['status' => false, 'message' => 'Invalid token'], 404);
        }

        $identifiedType = $identified['type'];
        $identifiedRecord = $identified['record'];

        DB::beginTransaction();
        try {
            if ($identifiedType === 'pass') {
                // visitor pass entry flow
                /** @var Pass $visitorPass */
                $visitorPass = $identifiedRecord;

                if ($visitorPass->token_used) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => 'Entry already used for this pass'], 400);
                }

                if (! now()->between($visitorPass->valid_from, $visitorPass->valid_until)) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => 'Pass is not valid at this time'], 400);
                }

                if ($visitorPass->pass_type === 'delivery') {
                    if (empty($providedOtp) || $providedOtp !== $visitorPass->otp) {
                        DB::rollBack();
                        return response()->json(['status' => false, 'message' => 'Invalid or missing OTP for delivery pass'], 403);
                    }
                }

                $visitorPass->token_used = 1;
                $visitorPass->scanned_in_by = $guardUser->id;
                $visitorPass->scanned_in_at = now();
                $visitorPass->save();

                // notify resident
                Notify::to($visitorPass->created_by, 'Visitor Arrived', 'Your visitor has entered the society.', ['pass_id' => $visitorPass->id]);

                // audit + history
                Audit::log($guardUser->id, 'approve_entry', 'passes', $visitorPass->id, null, ['scanned_in_at' => $visitorPass->scanned_in_at]);
                ScanHistory::create([
                    'society_id' => $visitorPass->society_id,
                    'guard_id' => $guardUser->id,
                    'token' => $requestToken,
                    'token_type' => 'pass',
                    'subject_id' => $visitorPass->id,
                    'action' => 'approve_entry',
                    'payload' => $visitorPass->toArray(),
                ]);

                DB::commit();
                return response()->json(['status' => true, 'message' => 'Visitor entry recorded', 'pass' => $visitorPass]);
            }

            if ($identifiedType === 'appointment') {
                /** @var Appointment $appointment */
                $appointment = $identifiedRecord;

                // only allow if entry token and not used
                if ($appointment->entry_token !== $requestToken) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => 'This token is not a valid entry token for this appointment'], 400);
                }

                if ($appointment->entry_token_used) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => 'Appointment entry already recorded'], 400);
                }

                if ($appointment->advance_amount > 0 && !$appointment->advance_paid) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => 'Advance payment pending for this appointment'], 402);
                }

                $appointment->entry_token_used = 1;
                $appointment->guard_in_time = now();
                $appointment->guard_in_id = $guardUser->id;
                $appointment->status = 'employee_in_society';
                $appointment->save();

                Notify::to($appointment->resident_id, 'Worker Entered', 'Worker verified at gate.', ['appointment_id' => $appointment->id]);

                Audit::log($guardUser->id, 'approve_entry', 'appointments', $appointment->id, null, ['guard_in_time' => $appointment->guard_in_time]);
                ScanHistory::create([
                    'society_id' => $appointment->society_id,
                    'guard_id' => $guardUser->id,
                    'token' => $requestToken,
                    'token_type' => 'appointment',
                    'subject_id' => $appointment->id,
                    'action' => 'approve_entry',
                    'payload' => $appointment->toArray(),
                ]);

                DB::commit();
                return response()->json(['status' => true, 'message' => 'Appointment entry recorded', 'appointment' => $appointment]);
            }

            if ($identifiedType === 'vehicle') {
                /** @var Vehicle $vehicle */
                $vehicle = $identifiedRecord;

                // check last log
                $lastVehicleLog = VehicleLog::where('vehicle_id', $vehicle->id)->orderBy('created_at', 'desc')->first();
                if ($lastVehicleLog && $lastVehicleLog->direction === 'in') {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => 'Vehicle already logged as inside'], 400);
                }

                $vehicleLog = VehicleLog::create([
                    'vehicle_id' => $vehicle->id,
                    'society_id' => $vehicle->society_id ?? $guardUser->society_id ?? null,
                    'guard_id' => $guardUser->id,
                    'direction' => 'in',
                    'vehicle_number' => $vehicle->vehicle_number,
                    'notes' => $additionalNotes ?? null,
                ]);

                // create parking log if you want to keep the same as parking slot (optional)
                // Audit and ScanHistory
                Audit::log($guardUser->id, 'vehicle_entry', 'vehicles', $vehicle->id, null, ['log_id' => $vehicleLog->id]);
                ScanHistory::create([
                    'society_id' => $vehicle->society_id ?? $guardUser->society_id ?? null,
                    'guard_id' => $guardUser->id,
                    'token' => $requestToken,
                    'token_type' => 'vehicle',
                    'subject_id' => $vehicle->id,
                    'action' => 'approve_entry',
                    'payload' => array_merge($vehicle->toArray(), ['vehicle_log_id' => $vehicleLog->id]),
                ]);

                DB::commit();
                return response()->json(['status' => true, 'message' => 'Vehicle entry recorded', 'vehicle_log' => $vehicleLog, 'vehicle' => $vehicle]);
            }

            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Unsupported token type'], 400);
        } catch (\Throwable $ex) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Server error', 'error' => $ex->getMessage()], 500);
        }
    }


    /**
     * Approve exit for a token (unified).
     * POST /api/guard/approve-exit
     */
    public function approveExit(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'guard_id' => 'required|integer|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $requestToken = $request->input('token');
        $guardUser = User::find($request->input('guard_id'));
        $additionalNotes = $request->input('notes');

        $identified = $this->identifyTokenRecord($requestToken);

        if (!$identified) {
            ScanHistory::create([
                'society_id' => $guardUser->society_id ?? null,
                'guard_id' => $guardUser->id,
                'token' => $requestToken,
                'token_type' => 'pass',
                'subject_id' => null,
                'action' => 'scan_failed',
                'payload' => null,
            ]);
            return response()->json(['status' => false, 'message' => 'Invalid token'], 404);
        }

        $identifiedType = $identified['type'];
        $identifiedRecord = $identified['record'];

        DB::beginTransaction();
        try {
            if ($identifiedType === 'pass') {
                /** @var Pass $visitorPass */
                $visitorPass = $identifiedRecord;

                if (! $visitorPass->token_used || ! $visitorPass->scanned_in_at) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => 'Visitor not recorded as inside'], 400);
                }

                if ($visitorPass->scanned_out_at) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => 'Exit already recorded'], 400);
                }

                $visitorPass->scanned_out_by = $guardUser->id;
                $visitorPass->scanned_out_at = now();
                $visitorPass->save();

                Notify::to($visitorPass->created_by, 'Visitor Left', 'Your visitor has exited the society.', ['pass_id' => $visitorPass->id]);

                Audit::log($guardUser->id, 'approve_exit', 'passes', $visitorPass->id, null, ['scanned_out_at' => $visitorPass->scanned_out_at]);
                ScanHistory::create([
                    'society_id' => $visitorPass->society_id,
                    'guard_id' => $guardUser->id,
                    'token' => $requestToken,
                    'token_type' => 'pass',
                    'subject_id' => $visitorPass->id,
                    'action' => 'approve_exit',
                    'payload' => $visitorPass->toArray(),
                ]);

                DB::commit();
                return response()->json(['status' => true, 'message' => 'Visitor exit recorded', 'pass' => $visitorPass]);
            }

            if ($identifiedType === 'appointment') {
                /** @var Appointment $appointment */
                $appointment = $identifiedRecord;

                // only allow if exit token and not used
                if ($appointment->exit_token !== $requestToken) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => 'This token is not a valid exit token for this appointment'], 400);
                }

                if ($appointment->exit_token_used) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => 'Appointment exit already recorded'], 400);
                }

                if (! $appointment->final_paid) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => 'Final payment pending for this appointment'], 402);
                }

                $appointment->exit_token_used = 1;
                $appointment->guard_out_time = now();
                $appointment->guard_out_id = $guardUser->id;
                $appointment->status = 'closed';
                $appointment->save();

                Audit::log($guardUser->id, 'approve_exit', 'appointments', $appointment->id, null, ['guard_out_time' => $appointment->guard_out_time]);
                ScanHistory::create([
                    'society_id' => $appointment->society_id,
                    'guard_id' => $guardUser->id,
                    'token' => $requestToken,
                    'token_type' => 'appointment',
                    'subject_id' => $appointment->id,
                    'action' => 'approve_exit',
                    'payload' => $appointment->toArray(),
                ]);

                DB::commit();
                return response()->json(['status' => true, 'message' => 'Appointment exit recorded', 'appointment' => $appointment]);
            }

            if ($identifiedType === 'vehicle') {
                /** @var Vehicle $vehicle */
                $vehicle = $identifiedRecord;

                $lastVehicleLog = VehicleLog::where('vehicle_id', $vehicle->id)->orderBy('created_at', 'desc')->first();
                if (!$lastVehicleLog || $lastVehicleLog->direction === 'out') {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => 'Vehicle is not recorded as inside'], 400);
                }

                $vehicleLog = VehicleLog::create([
                    'vehicle_id' => $vehicle->id,
                    'society_id' => $vehicle->society_id ?? $guardUser->society_id ?? null,
                    'guard_id' => $guardUser->id,
                    'direction' => 'out',
                    'vehicle_number' => $vehicle->vehicle_number,
                    'notes' => $additionalNotes ?? null,
                ]);

                Audit::log($guardUser->id, 'vehicle_exit', 'vehicles', $vehicle->id, null, ['vehicle_log_id' => $vehicleLog->id]);
                ScanHistory::create([
                    'society_id' => $vehicle->society_id ?? $guardUser->society_id ?? null,
                    'guard_id' => $guardUser->id,
                    'token' => $requestToken,
                    'token_type' => 'vehicle',
                    'subject_id' => $vehicle->id,
                    'action' => 'approve_exit',
                    'payload' => array_merge($vehicle->toArray(), ['vehicle_log_id' => $vehicleLog->id]),
                ]);

                DB::commit();
                return response()->json(['status' => true, 'message' => 'Vehicle exit recorded', 'vehicle_log' => $vehicleLog]);
            }

            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Unsupported token type'], 400);
        } catch (\Throwable $ex) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Server error', 'error' => $ex->getMessage()], 500);
        }
    }


    /**
     * Report: active items inside the society
     * GET /api/guard/report/inside/{society_id}
     */
    public function reportActiveInside($societyId)
    {
        // Active visitor passes: token_used = 1 and scanned_out_at is null and within validity
        $activeVisitorPasses = Pass::where('society_id', $societyId)
            ->where('token_used', 1)
            ->whereNull('scanned_out_at')
            ->get();

        // Active appointments (employee_in_society or in_progress)
        $activeAppointments = Appointment::where('society_id', $societyId)
            ->whereIn('status', ['employee_in_society', 'in_progress'])
            ->get();

        // Active vehicles: last vehicle_log direction = 'in' and no later 'out'
        $vehicleIdsInside = VehicleLog::select('vehicle_id')
            ->where('society_id', $societyId)
            ->groupBy('vehicle_id')
            ->havingRaw('MAX(created_at) = (SELECT MAX(created_at) FROM vehicle_logs vl2 WHERE vl2.vehicle_id = vehicle_logs.vehicle_id)')
            ->pluck('vehicle_id')->toArray();

        // Because the havingRaw approach might be tricky across DBs, do simpler: fetch latest per vehicle
        $vehicleIdsInside = [];
        $latestVehicleLogs = VehicleLog::where('society_id', $societyId)
            ->orderBy('vehicle_id')
            ->orderByDesc('created_at')
            ->get()
            ->unique('vehicle_id');

        foreach ($latestVehicleLogs as $latestVehicleLog) {
            if ($latestVehicleLog->direction === 'in') {
                $vehicleIdsInside[] = $latestVehicleLog->vehicle_id;
            }
        }

        $activeVehicles = Vehicle::whereIn('id', $vehicleIdsInside)->get();

        return response()->json([
            'status' => true,
            'count' => [
                'visitors' => $activeVisitorPasses->count(),
                'employees' => $activeAppointments->count(),
                'vehicles' => $activeVehicles->count(),
            ],
            'data' => [
                'visitors' => $activeVisitorPasses,
                'employees' => $activeAppointments,
                'vehicles' => $activeVehicles,
            ],
        ]);
    }

    /**
     * Guard can fetch history (scan_histories) for the society
     * GET /api/guard/history/{society_id}?limit=200
     */
    public function scanHistory($societyId, Request $request)
    {
        $limit = intval($request->query('limit', 200));
        $limit = $limit > 1000 ? 1000 : $limit;

        $historyRecords = ScanHistory::where('society_id', $societyId)
            ->latest()
            ->limit($limit)
            ->get();

        return response()->json(['status' => true, 'history' => $historyRecords]);
    }

    public function activePassesInside($societyId)
    {
        $results = collect();

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ VISITORS (PASSES)
        |--------------------------------------------------------------------------
        */
        $visitorPasses = Pass::where('society_id', $societyId)
            ->where('token_used', 1)
            ->whereNull('scanned_out_at')
            ->with(['scannedInGuard:id,name'])
            ->get();

        foreach ($visitorPasses as $pass) {
            $results->push([
                'entity_type' => 'visitor',
                'entity_id' => $pass->id,

                'type' => 'visitor',
                'name' => $pass->guest_name,
                'phone' => $pass->guest_phone,
                'purpose' => $pass->purpose,
                'time_in' => optional($pass->scanned_in_at)->format('h:i A'),
                'guard' => [
                    'id' => optional($pass->scannedInGuard)->id,
                    'name' => optional($pass->scannedInGuard)->name,
                ],
                'vehicle_number' => $pass->vehicle_number,
                'sort_time' => $pass->scanned_in_at,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ WORKERS (APPOINTMENTS)
        |--------------------------------------------------------------------------
        */
        $appointments = Appointment::where('society_id', $societyId)
            ->where('status', 'employee_in_society')
            ->with([
                'guardIn:id,name',
                'employer:id,name',
                'jobApplication.jobPost:id,job_title',
            ])
            ->get();

        foreach ($appointments as $appt) {
            $results->push([
                'entity_type' => 'worker',
                'entity_id' => $appt->id,

                'type' => 'worker',
                'name' => optional($appt->employer)->name ?? 'Worker',
                'phone' => null,
                'purpose' => optional($appt->jobApplication?->jobPost)->job_title ?? 'Service',
                'time_in' => optional($appt->guard_in_time)->format('h:i A'),
                'guard' => [
                    'id' => optional($appt->guardIn)->id,
                    'name' => optional($appt->guardIn)->name,
                ],
                'vehicle_number' => null,
                'sort_time' => $appt->guard_in_time,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ UNKNOWN / MANUAL VEHICLES
        |--------------------------------------------------------------------------
        */
        $vehicleLogs = VehicleLog::where('society_id', $societyId)
            ->latest('created_at')
            ->get()
            ->unique('vehicle_id');

        foreach ($vehicleLogs as $log) {
            if ($log->direction !== 'in') {
                continue;
            }

            $guard = User::find($log->guard_id);

            $results->push([
                'entity_type' => 'vehicle',
                'entity_id' => $log->id,

                'type' => 'vehicle',
                'name' => 'Unknown Vehicle',
                'phone' => null,
                'purpose' => 'Vehicle Entry',
                'time_in' => optional($log->created_at)->format('h:i A'),
                'guard' => [
                    'id' => optional($guard)->id,
                    'name' => optional($guard)->name,
                ],
                'vehicle_number' => $log->vehicle_number,
                'sort_time' => $log->created_at,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 🔀 SORT BY ENTRY TIME
        |--------------------------------------------------------------------------
        */
        $finalData = $results
            ->sortByDesc('sort_time')
            ->values()
            ->map(function ($item) {
                unset($item['sort_time']);
                return $item;
            });

        return response()->json([
            'status' => true,
            'count' => $finalData->count(),
            'data' => $finalData,
        ]);
    }


    public function insideDetail($type, $id)
    {
        /*
        |--------------------------------------------------------------------------
        | VISITOR DETAIL
        |--------------------------------------------------------------------------
        */
        if ($type === 'visitor') {
            $pass = Pass::with([
                'scannedInGuard:id,name',
                'scannedOutGuard:id,name',
            ])->find($id);

            if (!$pass) {
                return response()->json(['status' => false, 'message' => 'Visitor not found'], 404);
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'type' => 'visitor',
                    'name' => $pass->guest_name,
                    'phone' => $pass->guest_phone,
                    'purpose' => $pass->purpose,
                    'vehicle_number' => $pass->vehicle_number,
                    'entry' => [
                        'time' => optional($pass->scanned_in_at)->format('h:i A'),
                        'guard' => optional($pass->scannedInGuard)->name,
                    ],
                    'exit' => [
                        'time' => optional($pass->scanned_out_at)->format('h:i A'),
                        'guard' => optional($pass->scannedOutGuard)->name,
                    ],
                    'status' => $pass->scanned_out_at ? 'exited' : 'inside',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | WORKER DETAIL
        |--------------------------------------------------------------------------
        */
        if ($type === 'worker') {
            $appt = Appointment::with([
                'guardIn:id,name',
                'guardOut:id,name',
                'employer:id,name',
                'jobApplication.jobPost:id,job_title',
            ])->find($id);

            if (!$appt) {
                return response()->json(['status' => false, 'message' => 'Worker not found'], 404);
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'type' => 'worker',
                    'name' => optional($appt->employer)->name ?? 'Worker',
                    'purpose' => optional($appt->jobApplication?->jobPost)->job_title ?? 'Service',
                    'entry' => [
                        'time' => optional($appt->guard_in_time)->format('h:i A'),
                        'guard' => optional($appt->guardIn)->name,
                    ],
                    'exit' => [
                        'time' => optional($appt->guard_out_time)->format('h:i A'),
                        'guard' => optional($appt->guardOut)->name,
                    ],
                    'status' => $appt->status,
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | VEHICLE DETAIL
        |--------------------------------------------------------------------------
        */
        if ($type === 'vehicle') {
            $log = VehicleLog::with('vehicle')->find($id);

            if (!$log) {
                return response()->json(['status' => false, 'message' => 'Vehicle log not found'], 404);
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'type' => 'vehicle',
                    'vehicle_number' => $log->vehicle_number,
                    'entry' => [
                        'time' => optional($log->created_at)->format('h:i A'),
                    ],
                    'status' => $log->direction === 'in' ? 'inside' : 'exited',
                ],
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Invalid type',
        ], 400);
    }




    public function passLogs(Request $request, $societyId)
    {
        $from = $request->query('from');
        $to = $request->query('to');

        $query = Pass::where('society_id', $societyId)
            ->whereNotNull('scanned_in_at')
            ->whereNotNull('scanned_out_at');

        if ($from && $to) {
            $query->whereBetween('scanned_in_at', [$from, $to]);
        }

        $logs = $query->latest('scanned_in_at')->get();

        $data = $logs->map(function ($pass) {
            return [
                'name' => $pass->guest_name,
                'purpose' => $pass->purpose,
                'time_in' => optional($pass->scanned_in_at)->format('h:i A'),
                'time_out' => optional($pass->scanned_out_at)->format('h:i A'),
                'vehicle_number' => $pass->vehicle_number,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }


    public function passDetail($passId)
    {
        $pass = Pass::with([
            'scannedInGuard:id,name',
            'scannedOutGuard:id,name',
        ])->find($passId);

        if (!$pass) {
            return response()->json([
                'status' => false,
                'message' => 'Pass not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'visitor' => [
                    'name' => $pass->guest_name,
                    'phone' => $pass->guest_phone,
                    'purpose' => $pass->purpose,
                ],
                'vehicle' => [
                    'vehicle_number' => $pass->vehicle_number,
                ],
                'entry' => [
                    'time' => optional($pass->scanned_in_at)->format('h:i A'),
                    'guard' => optional($pass->scannedInGuard)->name,
                ],
                'exit' => [
                    'time' => optional($pass->scanned_out_at)->format('h:i A'),
                    'guard' => optional($pass->scannedOutGuard)->name,
                ],
                'status' => $pass->scanned_out_at ? 'exited' : 'inside',
            ],
        ]);
    }

    public function guardAddVehicle(Request $request)
    {
        $request->validate([
            'pass_id' => 'required|exists:passes,id',
            'guard_id' => 'required|exists:users,id',
            'vehicle_number' => 'required|string',
            'vehicle_type' => 'nullable|string',
        ]);

        $pass = Pass::find($request->pass_id);
        $guard = User::find($request->guard_id);

        if (!$pass->token_used || $pass->scanned_out_at) {
            return response()->json([
                'status' => false,
                'message' => 'Visitor is not inside',
            ], 400);
        }

        $vehicle = Vehicle::create([
            'vehicle_number' => $request->vehicle_number,
            'vehicle_type' => $request->vehicle_type ?? 'unknown',
            'status' => 'visitor',
        ]);

        VehicleLog::create([
            'vehicle_id' => $vehicle->id,
            'society_id' => $pass->society_id,
            'guard_id' => $guard->id,
            'direction' => 'in',
            'vehicle_number' => $vehicle->vehicle_number,
            'notes' => 'Manual entry by guard',
        ]);

        $pass->vehicle_number = $vehicle->vehicle_number;
        $pass->save();

        return response()->json([
            'status' => true,
            'message' => 'Vehicle added and entry recorded',
        ]);
    }


    public function allLogHistory(Request $request, $societyId)
    {
        $range = $request->query('range', 'today'); // today | week | custom
        $search = $request->query('search');
        $from = $request->query('from');
        $to = $request->query('to');

        // ------------------------------------------------
        // DATE RANGE
        // ------------------------------------------------
        if ($range === 'today') {
            $startDate = now()->startOfDay();
            $endDate = now()->endOfDay();
        } elseif ($range === 'week') {
            $startDate = now()->startOfWeek();
            $endDate = now()->endOfWeek();
        } elseif ($range === 'custom' && $from && $to) {
            $startDate = \Carbon\Carbon::parse($from)->startOfDay();
            $endDate = \Carbon\Carbon::parse($to)->endOfDay();
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Invalid date range',
            ], 400);
        }

        $results = collect();

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ VISITOR LOGS
        |--------------------------------------------------------------------------
        */
        $visitorLogs = Pass::where('society_id', $societyId)
            ->whereBetween('scanned_in_at', [$startDate, $endDate])
            ->with([
                'scannedInGuard:id,name',
                'scannedOutGuard:id,name',
            ])
            ->get();

        foreach ($visitorLogs as $pass) {
            $results->push([
                'type' => 'visitor',
                'entity_id' => $pass->id,
                'name' => $pass->guest_name,
                'is_resident' => false,
                'employer' => null,
                'worker_type' => null,
                'vehicle' => $pass->vehicle_number,
                'entry_time' => optional($pass->scanned_in_at)->format('d M Y, h:i A'),
                'entry_guard' => optional($pass->scannedInGuard)->name,
                'exit_time' => optional($pass->scanned_out_at)->format('d M Y, h:i A'),
                'exit_guard' => optional($pass->scannedOutGuard)->name,
                'sort_time' => $pass->scanned_in_at,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ WORKER LOGS (APPOINTMENTS)
        |--------------------------------------------------------------------------
        */
        $workerLogs = Appointment::where('society_id', $societyId)
            ->whereBetween('guard_in_time', [$startDate, $endDate])
            ->with([
                'guardIn:id,name',
                'guardOut:id,name',
                'employer:id,name',
                'jobApplication.jobPost:id,job_title',
            ])
            ->get();

        foreach ($workerLogs as $appt) {
            $results->push([
                'type' => 'worker',
                'entity_id' => $appt->id,
                'name' => optional($appt->employer)->name ?? 'Worker',
                'is_resident' => false,
                'employer' => optional($appt->employer)->name,
                'worker_type' => optional($appt->jobApplication?->jobPost)->job_title,
                'vehicle' => null,
                'entry_time' => optional($appt->guard_in_time)->format('d M Y, h:i A'),
                'entry_guard' => optional($appt->guardIn)->name,
                'exit_time' => optional($appt->guard_out_time)->format('d M Y, h:i A'),
                'exit_guard' => optional($appt->guardOut)->name,
                'sort_time' => $appt->guard_in_time,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ VEHICLE LOGS (UNKNOWN / MANUAL)
        |--------------------------------------------------------------------------
        */
        $vehicleLogs = VehicleLog::where('society_id', $societyId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('vehicle')
            ->get();

        foreach ($vehicleLogs as $log) {
            $guard = User::find($log->guard_id);

            $results->push([
                'type' => 'vehicle',
                'entity_id' => $log->id,
                'name' => 'Vehicle',
                'is_resident' => false,
                'employer' => null,
                'worker_type' => null,
                'vehicle' => $log->vehicle_number,
                'entry_time' => $log->direction === 'in'
                    ? $log->created_at->format('d M Y, h:i A')
                    : null,
                'entry_guard' => $log->direction === 'in'
                    ? optional($guard)->name
                    : null,
                'exit_time' => $log->direction === 'out'
                    ? $log->created_at->format('d M Y, h:i A')
                    : null,
                'exit_guard' => $log->direction === 'out'
                    ? optional($guard)->name
                    : null,
                'sort_time' => $log->created_at,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 🔍 SEARCH FILTER (NAME / VEHICLE / JOB)
        |--------------------------------------------------------------------------
        */
        if ($search) {
            $results = $results->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['name'] ?? ''), strtolower($search))
                    || str_contains(strtolower($item['vehicle'] ?? ''), strtolower($search))
                    || str_contains(strtolower($item['worker_type'] ?? ''), strtolower($search));
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 🔀 SORT + CLEAN
        |--------------------------------------------------------------------------
        */
        $finalData = $results
            ->sortByDesc('sort_time')
            ->values()
            ->map(function ($item) {
                unset($item['sort_time']);
                return $item;
            });

        return response()->json([
            'status' => true,
            'count' => $finalData->count(),
            'data' => $finalData,
        ]);
    }


    /* old data below not wokring

    ----------------------------------------------------------------------------

    end */




    // public function parkingHistory($society_id)
    // {
    //     $logs = ParkingLog::whereHas('slot', fn ($q) => $q->where('society_id', $society_id))
    //         ->latest()->limit(200)->get();

    //     return response()->json(['status' => true,'history' => $logs]);
    // }


    //list of inside fo socitey of resident list all show if search phone number or name
    public function residentsInside(Request $request, $society_id)
    {
        $search = $request->query('search');

        $query = User::query()
            ->where('role', 'customer')

            // ✅ Society filter from user_details
            ->whereHas('details', function ($q) use ($society_id) {
                $q->where('society_id', $society_id);
            });

        // // ✅ Inside society filter
        // ->whereHas('residentAssignments', function ($q) use ($society_id) {
        //     $q->where('society_id', $society_id)
        //     ->where('is_inside', 1);
        // });

        // 🔍 Search by name or phone
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                ->orWhere('floor_number', 'LIKE', "%{$search}%")
                ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        $residents = $query
            ->with('details') // include flat/floor info
            ->get();

        return response()->json([
            'status' => true,
            'count' => $residents->count(),
            'residents' => $residents,
        ]);
    }

    public function filterPasses(Request $request, $society_id)
    {
        $status = $request->query('status', 'all'); // all | active | upcoming | expired
        $search = $request->query('search');

        $query = Pass::where('society_id', $society_id)
            ->whereDate('created_at', now()->toDateString());

        /* 🔍 Search by visitor name or vehicle number */
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('visitor_name', 'LIKE', "%{$search}%")
                ->orWhere('vehicle_number', 'LIKE', "%{$search}%");
            });
        }

        /* 🕒 Status filter */
        if ($status === 'active') {
            $query->where('scanned_in_at', '!=', null)
                ->whereNull('scanned_out_at');
        }

        if ($status === 'upcoming') {
            $query->whereNull('scanned_in_at')
                ->where('valid_from', '>', now());
        }

        if ($status === 'expired') {
            $query->where(function ($q) {
                $q->where('valid_until', '<', now())
                ->orWhereNotNull('scanned_out_at');
            });
        }

        $passes = $query->latest()->get();

        return response()->json([
            'status' => true,
            'count' => $passes->count(),
            'passes' => $passes,
        ]);
    }


}//
