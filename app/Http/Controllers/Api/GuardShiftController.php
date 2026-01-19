<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GuardShift;
use App\Models\GuardShiftRequest;
use Illuminate\Http\Request;

class GuardShiftController extends Controller
{
    /**
     * 1️⃣ Society-wise assigned guard shifts
     * GET /api/guard-societies/{societyId}/shifts
     */
    public function societyShifts($societyId)
    {
        $shifts = GuardShift::with('guardUser')
            ->where('society_id', $societyId)
            ->orderBy('shift_date')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $shifts,
        ]);
    }

    /**
     * Shift Detail
     * GET /api/shifts/{id}
     */
    public function show($id)
    {
        $shift = GuardShift::with([
            'guardUser',
            'assignedBy',
            'logs.actionBy',
            'requests.requestedBy',
        ])->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $shift,
        ]);
    }

    /**
     * Guard → Request Shift Change
     * POST /api/shifts/{id}/request-change
     */
    public function requestChange(Request $request, $shiftId)
    {
        $request->validate([
            'new_date' => 'nullable|date',
            'new_start' => 'nullable',
            'new_end' => 'nullable',
            'reason' => 'required|string',
        ]);

        $req = GuardShiftRequest::create([
            'guard_shift_id' => $shiftId,
            'requested_by' => auth()->id(),
            'new_date' => $request->new_date,
            'new_start' => $request->new_start,
            'new_end' => $request->new_end,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Shift change request submitted',
            'data' => $req,
        ], 201);
    }

    /**
     * Admin → Approve / Reject Request
     * POST /api/shift-requests/{id}/decide
     */
    public function decide(Request $request, $requestId)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $shiftRequest = GuardShiftRequest::findOrFail($requestId);
        $shiftRequest->update(['status' => $request->status]);

        if ($request->status === 'approved') {
            $shift = GuardShift::find($shiftRequest->guard_shift_id);

            if ($shiftRequest->new_date) {
                $shift->shift_date = $shiftRequest->new_date;
            }
            if ($shiftRequest->new_start) {
                $shift->start_time = $shiftRequest->new_start;
            }
            if ($shiftRequest->new_end) {
                $shift->end_time = $shiftRequest->new_end;
            }
            $shift->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Decision saved',
        ]);
    }

    /**
     * 5️⃣ Logged-in guard – my shifts
     * GET /api/my-shifts
     */
    public function myShifts()
    {
        $shifts = GuardShift::with('assignedBy')
            ->where('guard_id', auth()->id())
            ->orderBy('shift_date')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $shifts,
        ]);
    }
}
