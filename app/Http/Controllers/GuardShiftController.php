<?php

namespace App\Http\Controllers;

use App\Models\GuardShift;
use App\Models\GuardShiftLog;
use App\Models\GuardShiftRequest;
use App\Models\Society;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;

class GuardShiftController extends Controller
{
    /* ===========================
     |  ADMIN: ASSIGN SHIFT FORM
     ===========================*/
    public function create()
    {
        if (!has_permission('guard_shift_create')) {
            abort(403, 'Permission denied');
        }

        $societyId = auth()->user()->assignment?->society_id;

        // Default empty collection
        $guards = collect();

        if ($societyId) {
            $guards = User::where('role', 'guard')
                ->whereHas('userDetails', function ($q) use ($societyId) {
                    $q->where('society_id', $societyId);
                })
                ->orderBy('name')
                ->get();
        }

        return view('member.guard_shift.assign', compact('guards'));
    }


    /* ===============================
     * STORE ASSIGNED SHIFT
     * =============================== */
    public function store(Request $request)
    {
        if (!has_permission('guard_shift_create')) {
            abort(403, 'Permission denied');
        }

        $society = getSocietyId();
        $request->validate([
            'guard_id' => 'required|exists:users,id',
            'gate' => 'required',
            'start_shift_date' => 'required|date',
            'end_shift_date' => 'required|date|after_or_equal:start_shift_date',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $guardShift = GuardShift::create([
            'society_id' => auth()->user()->assignment?->society_id,
            'guard_id' => $request->guard_id,
            'assigned_by' => auth()->id(),
            'gate' => $request->gate,
            'start_shift_date' => $request->start_shift_date,
            'end_shift_date' => $request->end_shift_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => 'assigned',
        ]);

        GuardShiftLog::create([
            'guard_shift_id' => $guardShift->id,
            'action' => 'assigned',
            'action_by' => auth()->id(),
            'notes' => 'Shift assigned',
        ]);
        return redirect()
            ->route('member.shift.guards.index')
            ->with('success', 'Shift assigned successfully');
    }

    /* ===========================
     |  LIST SHIFTS
     ===========================*/
    public function index(Request $request)
    {
        if (!has_permission('guard_shift_index')) {
            abort(403, 'Permission denied');
        }

        $statusFilter = $request->query('status', 'assigned');

        $shifts = GuardShift::with(['guardUser', 'assignedByUser'])
            ->when(
                $statusFilter !== 'all',
                fn ($q) =>
                $q->where('status', $statusFilter)
            )

            ->when($request->filled('date'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->whereDate('start_shift_date', '<=', $request->date)
                        ->whereDate('end_shift_date', '>=', $request->date);
                });
            })

            ->when($request->filled('guard'), function ($q) use ($request) {
                $q->whereHas('guardUser', function ($g) use ($request) {
                    $g->where('name', 'like', '%' . $request->guard . '%');
                });
            })

            ->orderBy('start_shift_date', 'desc')
            ->get();

        return view(
            'member.guard_shift.index',
            compact('shifts', 'statusFilter')
        );
    }


    /* ===========================
     |  SHIFT DETAIL + LOG
     ===========================*/
    public function show($shiftId)
    {
        if (!has_permission('guard_shift_show')) {
            abort(403, 'Permission denied');
        }

        $shift = GuardShift::with([
            'guardUser',
            'assignedByUser',
            'logs.actionBy',
            'requests.requestedBy',
        ])->findOrFail($shiftId);


        return view('member.guard_shift.show', compact('shift'));
    }


    /* ===========================
     |  GUARD: REQUEST CHANGE
     ===========================*/
    public function requestChange(Request $request, $shiftId)
    {
        if (!has_permission('guard_shift_edit')) {
            abort(403, 'Permission denied');
        }

        $request->validate([
            'new_date' => 'nullable|date',
            'new_start' => 'nullable',
            'new_end' => 'nullable',
            'reason' => 'required|string',
        ]);

        GuardShiftRequest::create([
            'guard_shift_id' => $shiftId,
            'requested_by' => auth()->id(),
            'new_date' => $request->new_date,
            'new_start' => $request->new_start,
            'new_end' => $request->new_end,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        GuardShiftLog::create([
            'guard_shift_id' => $shiftId,
            'action' => 'change_requested',
            'action_by' => auth()->id(),
            'notes' => $request->reason,
        ]);

        return back()->with('success', 'Shift change request submitted');
    }

    /* ===========================
     |  ADMIN: APPROVE / REJECT
     ===========================*/
    public function decide(Request $request, $requestId)
    {
        if (!has_permission('guard_shift_edit')) {
            abort(403, 'Permission denied');
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $shiftRequest = GuardShiftRequest::findOrFail($requestId);
        $shiftRequest->status = $request->status;
        $shiftRequest->save();

        if ($request->status === 'approved') {
            $guardShift = GuardShift::find($shiftRequest->guard_shift_id);

            if ($shiftRequest->new_date) {
                $guardShift->shift_date = $shiftRequest->new_date;
            }
            if ($shiftRequest->new_start) {
                $guardShift->start_time = $shiftRequest->new_start;
            }
            if ($shiftRequest->new_end) {
                $guardShift->end_time = $shiftRequest->new_end;
            }

            $guardShift->save();
        }

        GuardShiftLog::create([
            'guard_shift_id' => $shiftRequest->guard_shift_id,
            'action' => 'change_' . $request->status,
            'action_by' => auth()->id(),
            'notes' => 'Request ' . $request->status,
        ]);

        return back()->with('success', 'Shift change decision saved');
    }
}
