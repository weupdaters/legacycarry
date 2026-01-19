<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuardShift;
use App\Models\GuardShiftLog;
use App\Models\GuardShiftRequest;
use App\Models\Society;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GuardShiftController extends Controller
{
    public function index(Request $request)
    {
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

        $societies = Society::orderBy('name')->get();

        return view(
            'admin.guard_shift.index',
            compact('shifts', 'statusFilter', 'societies')
        );
    }

    /* ===========================
     |  CREATE
     ===========================*/
    public function create(Request $request)
    {
        $societies = Society::orderBy('name')->get();
        $guards = collect();

        if ($request->filled('society_id')) {
            $guards = User::where('role', 'guard')
                ->whereHas(
                    'userDetails',
                    fn ($q) =>
                    $q->where('society_id', $request->society_id)
                )
                ->orderBy('name')
                ->get();
        }

        return view('admin.guard_shift.assign', compact('societies', 'guards'));
    }

    /* ===========================
     |  STORE
     ===========================*/
    public function store(Request $request)
    {
        $request->validate([
            'society_id' => 'required|exists:societies,id',
            'guard_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($q) use ($request) {
                    $q->where('role', 'guard')
                      ->whereExists(function ($sub) use ($request) {
                          $sub->selectRaw(1)
                              ->from('user_details')
                              ->whereColumn('user_details.user_id', 'users.id')
                              ->where('user_details.society_id', $request->society_id);
                      });
                }),
            ],
            'gate' => 'required|string',
            'start_shift_date' => 'required|date',
            'end_shift_date' => 'required|date|after_or_equal:start_shift_date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        GuardShift::create([
            'society_id' => $request->society_id,
            'guard_id' => $request->guard_id,
            'gate' => $request->gate,
            'start_shift_date' => $request->start_shift_date,
            'end_shift_date' => $request->end_shift_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => 'assigned',
            'assigned_by' => auth()->id(),
        ]);

        return redirect()->route('shift.guards.index')
            ->with('success', 'Guard shift assigned successfully.');
    }

    /* ===========================
     |  EDIT
     ===========================*/
    public function edit($id)
    {
        $shift = GuardShift::findOrFail($id);
        $societies = Society::orderBy('name')->get();

        $guards = User::where('role', 'guard')
            ->whereHas(
                'userDetails',
                fn ($q) =>
                $q->where('society_id', $shift->society_id)
            )
            ->orderBy('name')
            ->get();

        return view('admin.guard_shift.edit', compact('shift', 'societies', 'guards'));
    }

    /* ===========================
     |  UPDATE
     ===========================*/
    public function update(Request $request, $id)
    {
        $shift = GuardShift::findOrFail($id);

        $request->validate([
            'guard_id' => 'required|exists:users,id',
            'gate' => 'required|string',
            'start_shift_date' => 'required|date',
            'end_shift_date' => 'required|date|after_or_equal:start_shift_date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'status' => 'required|in:assigned,active,completed,cancelled',
        ]);

        $shift->update([
            'guard_id' => $request->guard_id,
            'gate' => $request->gate,
            'start_shift_date' => $request->start_shift_date,
            'end_shift_date' => $request->end_shift_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => $request->status,
        ]);

        GuardShiftLog::create([
            'guard_shift_id' => $shift->id,
            'action' => 'updated',
            'action_by' => auth()->id(),
            'notes' => 'Shift updated',
        ]);

        return redirect()->route('shift.guards.index')
            ->with('success', 'Shift updated successfully.');
    }

    /* ===========================
     |  DELETE
     ===========================*/
    public function destroy($id)
    {
        $shift = GuardShift::findOrFail($id);

        GuardShiftLog::create([
            'guard_shift_id' => $shift->id,
            'action' => 'deleted',
            'action_by' => auth()->id(),
            'notes' => 'Shift deleted',
        ]);

        $shift->delete();

        return back()->with('success', 'Shift deleted successfully.');
    }

    /* ===========================
     |  AJAX: GUARDS BY SOCIETY
     ===========================*/
    public function getGuardsBySociety(Request $request)
    {
        if (! $request->filled('society_id')) {
            return response()->json([]);
        }

        return User::where('role', 'guard')
            ->whereHas(
                'userDetails',
                fn ($q) =>
                $q->where('society_id', $request->society_id)
            )
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /* ===========================
     |  SHIFT DETAIL + LOG
     ===========================*/
    public function show($shiftId)
    {
        $shift = GuardShift::with([
            'guardUser',
            'assignedByUser',
            'logs.actionBy',
            'requests.requestedBy',
        ])->findOrFail($shiftId);


        return view('admin.guard_shift.show', compact('shift'));
    }


    /* ===========================
     |  GUARD: REQUEST CHANGE
     ===========================*/
    public function requestChange(Request $request, $shiftId)
    {
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
