<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssignedShift;
use App\Models\ShiftChangeRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller
{
    // 1. Assign shift (admin action)
    public function assignShift(Request $r)
    {
        $r->validate([
            'society_id' => 'required|integer',
            'guard_user_id' => 'required|exists:users,id',
            'assigned_by' => 'required|exists:users,id', // admin id
            'gate_name' => 'nullable|string',
            'shift_date' => 'required|date',
            'shift_start' => 'required|date_format:H:i',
            'shift_end' => 'required|date_format:H:i',
            'notes' => 'nullable|string',
        ]);

        // Optional: check overlapping shifts for this guard at same date/time
        $overlap = AssignedShift::where('guard_user_id', $r->guard_user_id)
            ->where('shift_date', $r->shift_date)
            ->where(function ($q) use ($r) {
                $q->whereBetween('shift_start', [$r->shift_start, $r->shift_end])
                  ->orWhereBetween('shift_end', [$r->shift_start, $r->shift_end])
                  ->orWhere(function ($q2) use ($r) {
                      $q2->where('shift_start', '<', $r->shift_start)
                         ->where('shift_end', '>', $r->shift_end);
                  });
            })->exists();

        if ($overlap) {
            return response()->json(['status' => false,'message' => 'Guard has overlapping shift on this date/time'], 422);
        }

        $shift = AssignedShift::create($r->only([
            'society_id','guard_user_id','assigned_by','gate_name','shift_date','shift_start','shift_end','notes',
        ]));

        return response()->json(['status' => true,'message' => 'Shift assigned','shift' => $shift]);
    }

    // 2. List shifts for society (filter by tab: assigned/upcoming/completed)
    public function listShifts(Request $r, $societyId)
    {
        $tab = $r->query('tab', 'assigned'); // assigned|upcoming|completed|all
        $query = AssignedShift::where('society_id', $societyId)
            ->with(['guard:id,name','assigner:id,name'])
            ->orderBy('shift_date', 'desc')->orderBy('shift_start', 'desc');

        if ($tab === 'assigned') {
            $query->where('status', 'assigned');
        } elseif ($tab === 'upcoming') {
            $query->whereIn('status', ['assigned','upcoming'])
                  ->where('shift_date', '>=', Carbon::today());
        } elseif ($tab === 'completed') {
            $query->where('status', 'completed');
        } elseif ($tab === 'in_progress') {
            $query->where('status', 'in_progress');
        }

        $shifts = $query->get()->map(function ($s) {
            return [
                'id' => $s->id,
                'guard' => ['id' => $s->guard->id ?? null, 'name' => $s->guard->name ?? null],
                'gate_name' => $s->gate_name,
                'shift_date' => $s->shift_date->toDateString(),
                'shift_start' => $s->shift_start,
                'shift_end' => $s->shift_end,
                'status' => $s->status,
                'notes' => $s->notes,
                'assigned_by' => ['id' => $s->assigner->id ?? null, 'name' => $s->assigner->name ?? null],
            ];
        });

        return response()->json(['status' => true,'count' => $shifts->count(),'data' => $shifts]);
    }

    // 3. Shift detail
    public function shiftDetail($id)
    {
        $s = AssignedShift::with([
            'guard:id,name,phone',
            'assigner:id,name',
            'changeRequests.requester:id,name',
            'changeRequests.decisionByUser:id,name',
        ])->find($id);

        if (!$s) {
            return response()->json(['status' => false,'message' => 'Shift not found'], 404);
        }

        return response()->json(['status' => true,'data' => [
            'id' => $s->id,
            'society_id' => $s->society_id,
            'guard' => $s->guard ? ['id' => $s->guard->id,'name' => $s->guard->name,'phone' => $s->guard->phone] : null,
            'gate_name' => $s->gate_name,
            'shift_date' => $s->shift_date->toDateString(),
            'shift_start' => $s->shift_start,
            'shift_end' => $s->shift_end,
            'status' => $s->status,
            'notes' => $s->notes,
            'assigned_by' => $s->assigner ? ['id' => $s->assigner->id,'name' => $s->assigner->name] : null,
            'change_requests' => $s->changeRequests->map(function ($cr) {
                return [
                    'id' => $cr->id,
                    'request_by' => $cr->requester ? ['id' => $cr->requester->id,'name' => $cr->requester->name] : null,
                    'requested_shift_date' => $cr->requested_shift_date,
                    'requested_shift_start' => $cr->requested_shift_start,
                    'requested_shift_end' => $cr->requested_shift_end,
                    'reason' => $cr->reason,
                    'status' => $cr->status,
                    'decision_by' => $cr->decisionByUser ? ['id' => $cr->decisionByUser->id,'name' => $cr->decisionByUser->name] : null,
                    'decision_comment' => $cr->decision_comment,
                    'created_at' => $cr->created_at->toDateTimeString(),
                ];
            }),
        ]]);
    }

    // 4. Guard or admin creates a change request
    public function requestShiftChange(Request $r, $shiftId)
    {
        $r->validate([
            'request_by' => 'required|exists:users,id',
            'requested_shift_date' => 'nullable|date',
            'requested_shift_start' => 'nullable|date_format:H:i',
            'requested_shift_end' => 'nullable|date_format:H:i',
            'reason' => 'nullable|string',
        ]);

        $shift = AssignedShift::find($shiftId);
        if (!$shift) {
            return response()->json(['status' => false,'message' => 'Shift not found'], 404);
        }

        $req = ShiftChangeRequest::create([
            'assigned_shift_id' => $shift->id,
            'request_by' => $r->request_by,
            'requested_shift_date' => $r->requested_shift_date,
            'requested_shift_start' => $r->requested_shift_start,
            'requested_shift_end' => $r->requested_shift_end,
            'reason' => $r->reason,
        ]);

        return response()->json(['status' => true,'message' => 'Request submitted','request' => $req]);
    }

    // 5. Admin approve or reject a request
    public function decideShiftRequest(Request $r, $requestId)
    {
        $r->validate([
            'decision_by' => 'required|exists:users,id',
            'status' => 'required|in:approved,rejected',
            'decision_comment' => 'nullable|string',
        ]);

        $req = ShiftChangeRequest::find($requestId);
        if (!$req) {
            return response()->json(['status' => false,'message' => 'Request not found'], 404);
        }

        $req->status = $r->status;
        $req->decision_by = $r->decision_by;
        $req->decision_comment = $r->decision_comment;
        $req->save();

        if ($r->status === 'approved') {
            // apply changes to assigned shift (if provided)
            $shift = AssignedShift::find($req->assigned_shift_id);
            if ($shift) {
                DB::transaction(function () use ($shift, $req) {
                    if ($req->requested_shift_date) {
                        $shift->shift_date = $req->requested_shift_date;
                    }
                    if ($req->requested_shift_start) {
                        $shift->shift_start = $req->requested_shift_start;
                    }
                    if ($req->requested_shift_end) {
                        $shift->shift_end = $req->requested_shift_end;
                    }
                    $shift->status = 'assigned';
                    $shift->save();
                });
            }
        }

        return response()->json(['status' => true,'message' => 'Decision saved','request' => $req]);
    }

    // 6. Mark shift completed (guard or admin)
    public function markShiftComplete(Request $r, $shiftId)
    {
        $r->validate(['by_user' => 'required|exists:users,id']);
        $shift = AssignedShift::find($shiftId);
        if (!$shift) {
            return response()->json(['status' => false,'message' => 'Shift not found'], 404);
        }

        $shift->status = 'completed';
        $shift->save();

        return response()->json(['status' => true,'message' => 'Shift marked completed','shift' => $shift]);
    }

    // 7. Cancel shift
    public function cancelShift(Request $r, $shiftId)
    {
        $r->validate(['by_user' => 'required|exists:users,id','reason' => 'nullable|string']);
        $shift = AssignedShift::find($shiftId);
        if (!$shift) {
            return response()->json(['status' => false,'message' => 'Shift not found'], 404);
        }

        $shift->status = 'cancelled';
        $shift->notes = $r->reason ?? $shift->notes;
        $shift->save();

        return response()->json(['status' => true,'message' => 'Shift cancelled','shift' => $shift]);
    }
}
