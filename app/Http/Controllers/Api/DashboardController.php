<?php

use Illuminate\Http\Request;

class DashboarsController extends Controller
{
    public function guardCreate(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min(6)',
            'society_id' => 'required|integer',
        ]);

        $guard = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'guard',
            'society_id' => $request->society_id,
        ]);

        return response()->json(['status' => true, 'message' => 'Guard created', 'guard' => $guard]);
    }


    public function guardEdit($id)
    {
        $guard = User::where('role', 'guard')->find($id);
        if (!$guard) {
            return response()->json(['status' => false,'message' => 'Guard not found'], 404);
        }
        return response()->json(['status' => true,'guard' => $guard]);
    }


    public function guardUpdate(Request $request, $id)
    {
        $guard = User::where('role', 'guard')->find($id);
        if (!$guard) {
            return response()->json(['status' => false,'message' => 'Guard not found'], 404);
        }

        $guard->name = $request->name ?? $guard->name;
        $guard->phone = $request->phone ?? $guard->phone;

        if ($request->filled('password')) {
            $guard->password = Hash::make($request->password);
        }

        $guard->save();

        return response()->json(['status' => true,'message' => 'Guard updated','guard' => $guard]);
    }



    /*
    |--------------------------------------------------------------------------
    |  B) GUARD DASHBOARD
    |--------------------------------------------------------------------------
    */


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
                ->where('valid_until', '>=', now());
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



    public function logs($society_id)
    {
        $logs = AuditLog::where('society_id', $society_id)
            ->latest()
            ->limit(200)
            ->get();

        return response()->json(['status' => true,'logs' => $logs]);
    }



    /*
    |--------------------------------------------------------------------------
    |  C) PASS SCANNING
    |--------------------------------------------------------------------------
    */

    public function scanEntry(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'guard_id' => 'required|exists:users,id',
            'otp' => 'nullable|string',
        ]);

        $pass = Pass::where('token', $request->token)->first();
        if (!$pass) {
            return response()->json(['status' => false,'message' => 'Invalid token'], 404);
        }

        $guard = User::find($request->guard_id);
        // if ($guard->society_id != $pass->society_id)
        //     return response()->json(['status'=>false,'message'=>'Not your society'],403);

        if (now()->lt($pass->valid_from) || now()->gt($pass->valid_until)) {
            return response()->json(['status' => false,'message' => 'Pass expired or not valid yet'], 400);
        }

        if ($pass->pass_type === 'delivery') {
            if (!$request->otp || $request->otp != $pass->otp) {
                return response()->json(['status' => false,'message' => 'Invalid OTP'], 403);
            }
        }

        if ($pass->token_used) {
            return response()->json(['status' => false,'message' => 'Entry already used'], 400);
        }

        $pass->token_used = 1;
        $pass->scanned_in_by = $guard->id;
        $pass->scanned_in_at = now();
        $pass->save();

        Notify::to($pass->created_by, 'Visitor Arrived', 'Your visitor has entered the society.', ['pass_id' => $pass->id]);

        Audit::log($guard->id, 'scan_entry', 'passes', $pass->id, null, [
            'scanned_in_at' => $pass->scanned_in_at,
        ]);

        return response()->json(['status' => true,'message' => 'Entry recorded','pass' => $pass]);
    }


    public function scanExit(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'guard_id' => 'required|exists:users,id',
        ]);

        $pass = Pass::where('token', $request->token)->first();
        if (!$pass) {
            return response()->json(['status' => false,'message' => 'Invalid token'], 404);
        }

        $guard = User::find($request->guard_id);
        // if ($guard->society_id != $pass->society_id)
        //     return response()->json(['status'=>false,'message'=>'Not your society'],403);

        // 🚫 Not entered yet
        if (!$pass->token_used || !$pass->scanned_in_at) {
            return response()->json(['status' => false,'message' => 'Visitor is not inside'], 400);
        }

        // 🚫 Already exited
        if ($pass->scanned_out_at) {
            return response()->json(['status' => false,'message' => 'Exit already recorded'], 400);
        }

        // ✅ EXIT
        $pass->scanned_out_by = $guard->id;
        $pass->scanned_out_at = now();
        $pass->save();

        Notify::to(
            $pass->created_by,
            'Visitor Left',
            'Your visitor has exited the society.',
            ['pass_id' => $pass->id]
        );

        Audit::log(
            $guard->id,
            'scan_exit',
            'passes',
            $pass->id,
            null,
            ['scanned_out_at' => $pass->scanned_out_at]
        );

        return response()->json([
            'status' => true,
            'message' => 'Exit recorded successfully',
            'pass' => $pass,
        ]);
    }




    /*
    |--------------------------------------------------------------------------
    |  D) JOB APPOINTMENT SCANNING (WORKERS)
    |--------------------------------------------------------------------------
    */

    public function jobEntry(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'guard_id' => 'required|exists:users,id',
        ]);

        $appt = Appointment::where('entry_token', $request->token)->first();
        if (!$appt) {
            return response()->json(['status' => false,'message' => 'Invalid entry token'], 404);
        }

        $guard = User::find($request->guard_id);
        if ($guard->society_id != $appt->society_id) {
            return response()->json(['status' => false,'message' => 'Not your society'], 403);
        }

        if ($appt->advance_amount > 0 && !$appt->advance_paid) {
            return response()->json(['status' => false,'message' => 'Advance payment pending'], 402);
        }

        if ($appt->entry_token_used) {
            return response()->json(['status' => false,'message' => 'Already entered'], 400);
        }

        $appt->entry_token_used = 1;
        $appt->guard_in_time = now();
        $appt->guard_in_id = $guard->id;
        $appt->status = 'employee_in_society';
        $appt->save();

        Notify::to($appt->resident_id, 'Worker Entered', 'Worker verified at gate.', ['appointment_id' => $appt->id]);

        Audit::log($guard->id, 'job_entry', 'appointments', $appt->id, null, [
            'guard_in_time' => $appt->guard_in_time,
        ]);

        return response()->json(['status' => true,'message' => 'Job entry verified','appointment' => $appt]);
    }


    public function jobExit(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'guard_id' => 'required|exists:users,id',
        ]);

        $appt = Appointment::where('exit_token', $request->token)->first();
        if (!$appt) {
            return response()->json(['status' => false,'message' => 'Invalid exit token'], 404);
        }

        $guard = User::find($request->guard_id);
        // if ($guard->society_id != $appt->society_id)
        //     return response()->json(['status'=>false,'message'=>'Not your society'],403);

        if (!$appt->final_paid) {
            return response()->json(['status' => false,'message' => 'Final payment pending'], 402);
        }

        if ($appt->exit_token_used) {
            return response()->json(['status' => false,'message' => 'Already exited'], 400);
        }

        $appt->exit_token_used = 1;
        $appt->guard_out_time = now();
        $appt->guard_out_id = $guard->id;
        $appt->status = 'closed';
        $appt->save();

        if ($pass->created_by && User::where('id', $pass->created_by)->exists()) {
            Notify::to(
                $pass->created_by,
                'Visitor Left',
                'Your visitor exited the society.',
                ['pass_id' => $pass->id]
            );
        }

        Audit::log($guard->id, 'job_exit', 'appointments', $appt->id, null, [
            'guard_out_time' => $appt->guard_out_time,
        ]);

        return response()->json(['status' => true,'message' => 'Exit recorded','appointment' => $appt]);
    }


    public function appointmentDetails($id)
    {
        $a = Appointment::with(['resident','employer','jobApplication','guardIn','guardOut'])->find($id);
        if (!$a) {
            return response()->json(['status' => false,'message' => 'Not found'], 404);
        }

        return response()->json(['status' => true,'appointment' => $a]);
    }


    public function activeAppointments($society_id)
    {
        $a = Appointment::where('society_id', $society_id)
            ->whereIn('status', ['employee_in_society','in_progress'])
            ->get();

        return response()->json(['status' => true,'active' => $a]);
    }



    /*
    |--------------------------------------------------------------------------
    |  E) PARKING
    |--------------------------------------------------------------------------
    */

    public function parkingSlots($society_id)
    {
        $slots = ParkingSlot::where('society_id', $society_id)->get();
        return response()->json(['status' => true,'slots' => $slots]);
    }


    public function assignParkingSlot(Request $r)
    {
        $r->validate([
            'pass_id' => 'required|exists:passes,id',
            'slot_id' => 'required|exists:parking_slots,id',
            'guard_id' => 'required|exists:users,id',
        ]);

        $pass = Pass::find($r->pass_id);
        $slot = ParkingSlot::find($r->slot_id);
        $guard = User::find($r->guard_id);

        if ($guard->society_id != $slot->society_id) {
            return response()->json(['status' => false,'message' => 'Not your society'], 403);
        }

        $slot->is_occupied = 1;
        $slot->current_pass_id = $pass->id;
        $slot->save();

        $log = ParkingLog::create([
            'slot_id' => $slot->id,
            'pass_id' => $pass->id,
            'vehicle_number' => $pass->vehicle_number,
            'entered_by' => $guard->id,
            'entered_at' => now(),
        ]);

        Audit::log($guard->id, 'assign_slot', 'parking_slots', $slot->id, null, [
            'pass_id' => $pass->id,'log_id' => $log->id,
        ]);

        return response()->json(['status' => true,'message' => 'Slot assigned','slot' => $slot,'log' => $log]);
    }


    public function vehiclesInside($society_id)
    {
        $slots = ParkingSlot::where('society_id', $society_id)
            ->where('is_occupied', 1)
            ->with('currentPass')
            ->get();

        return response()->json(['status' => true,'occupied' => $slots]);
    }
}
