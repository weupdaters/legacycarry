<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ParkingSlot;
use App\Models\ParkingLog;
use App\Models\Pass;
use App\Helpers\Audit;

class ParkingController extends Controller
{
    // list slots for society
    public function listSlots($society_id)
    {
        $slots = ParkingSlot::where('society_id', $society_id)->get();
        return response()->json(['status' => true, 'slots' => $slots]);
    }

    // assign parking slot (guard)
    public function assignSlot(Request $request)
    {
        $request->validate([
            'pass_id'  => 'required|exists:passes,id',
            'slot_id'  => 'required|exists:parking_slots,id',
            'guard_id' => 'required|exists:users,id'
        ]);

        $pass = Pass::findOrFail($request->pass_id);
        $slot = ParkingSlot::findOrFail($request->slot_id);

        // mark slot occupied
        $slot->is_occupied = 1;
        $slot->current_pass_id = $pass->id;
        $slot->save();

        // create parking log
        $log = ParkingLog::create([
            'slot_id' => $slot->id,
            'pass_id' => $pass->id,
            'vehicle_number' => $pass->vehicle_number,
            'entered_by' => $request->guard_id,
            'entered_at' => now()
        ]);

        Audit::log($request->guard_id, 'assign_parking', 'parking_slots', $slot->id, null, ['pass_id' => $pass->id, 'log_id' => $log->id]);

        return response()->json(['status' => true, 'message' => 'Slot assigned', 'slot' => $slot, 'log' => $log]);
    }

    // vehicles currently inside (occupied slots)
    public function vehiclesInside($society_id)
    {
        $slots = ParkingSlot::where('society_id', $society_id)->where('is_occupied', 1)->with('currentPass')->get();
        return response()->json(['status' => true, 'occupied' => $slots]);
    }

    // parking history for society
    public function history($society_id)
    {
        $logs = ParkingLog::whereHas('slot', function($q) use ($society_id) {
            $q->where('society_id', $society_id);
        })->latest()->limit(200)->get();

        return response()->json(['status' => true, 'history' => $logs]);
    }
}
