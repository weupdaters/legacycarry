<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\GuardShift;
use App\Models\GuardShiftRequest;
use App\Models\Pass;
use App\Models\Society;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\Vehicle;
use App\Models\VehicleLog;
use App\Models\VehiclePass;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GuardController extends Controller
{
    /* =======================
        LIST / SEARCH
    ======================= */
    public function index(Request $request)
    {
        $guards = User::with(['details.society'])
            ->where('role', 'guard')
            ->when($request->society_id !== null && $request->society_id !== '', function ($q) use ($request) {
                $q->whereHas('details', function ($qq) use ($request) {
                    $qq->where('society_id', $request->society_id);
                });
            })
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($qq) use ($request) {
                    $qq->where('name', 'like', "%{$request->search}%")
                       ->orWhere('email', 'like', "%{$request->search}%")
                       ->orWhere('phone', 'like', "%{$request->search}%");
                });
            })
            ->when($request->status !== null && $request->status !== '', function ($q) use ($request) {
                $q->where('is_active', $request->status == 'Active' ? 1 : 0);
            })
            ->latest()
            ->paginate(10);

        if ($request->ajax()) {
            return view('admin.guards.partials.table', compact('guards'))->render();
        }

        $societies = Society::orderBy('name')->get();

        return view('admin.guards.index', compact('guards', 'societies'));
    }


    /* =======================
        CREATE
    ======================= */
    public function create()
    {
        $societies = Society::orderBy('name')->get();
        return view('admin.guards.create', compact('societies'));
    }

    /* =======================
        STORE
    ======================= */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|min:6',

            'society_id' => 'required|integer',
            'full_name' => 'required|string|max:100',
            'gender' => 'nullable|in:Male,Female,Other',
            'dob' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        DB::transaction(function () use ($request) {

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => 'guard',
                'verification_status' => 'pending',
                'is_active' => 1,
            ]);

            UserDetail::create([
                'user_id' => $user->id,
                'society_id' => $request->society_id,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'date_of_birth' => $request->dob,
                'gender' => $request->gender,
                'occupation' => 'Security Guard',
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
            ]);
        });

        return redirect()
            ->route('guards.index')
            ->with('success', 'Guard added successfully');
    }

    /* =======================
        EDIT
    ======================= */
    public function edit($id)
    {
        $user = User::with('details', 'details.society')
            ->where('role', 'guard')
            ->findOrFail($id);

        $societies = Society::orderBy('name')->get();

        return view('admin.guards.edit', compact('user', 'societies'));
    }

    /* =======================
        UPDATE
    ======================= */
    public function update(Request $request, $id)
    {
        $user = User::with('details', 'details.society')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,

            'society_id' => 'required|integer',
            'full_name' => 'required|string|max:100',
            'gender' => 'nullable|in:Male,Female,Other',
            'dob' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'is_active' => 'required|boolean',
        ]);

        DB::transaction(function () use ($request, $user) {

            // update users table
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'is_active' => $request->is_active,
            ]);

            // update or create user_details
            UserDetail::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'society_id' => $request->society_id,
                    'full_name' => $request->full_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'date_of_birth' => $request->dob,
                    'gender' => $request->gender,
                    'occupation' => 'Security Guard',
                    'emergency_contact_name' => $request->emergency_contact_name,
                    'emergency_contact_phone' => $request->emergency_contact_phone,
                ]
            );
        });

        return redirect()
            ->route('guards.index')
            ->with('success', 'Guard updated successfully');
    }

    /* =======================
        VIEW
    ======================= */
    public function view($id)
    {
        $user = User::with(['details', 'details.society', 'details.flat'])
            ->where('role', 'guard')
            ->findOrFail($id);



        return view('admin.guards.view', compact('user'));
    }

    /* =======================
        TOGGLE STATUS
    ======================= */
    public function toggleStatus(Request $request)
    {
        $guard = User::findOrFail($request->id);
        $guard->is_active = !$guard->is_active;
        $guard->save();

        return response()->json(['success' => true]);
    }

    /* =======================
        DELETE
    ======================= */
    public function destroy($id)
    {
        UserDetail::where('user_id', $id)->delete();
        User::where('id', $id)->delete();

        return response()->json(['success' => true]);
    }

    public function allGuardHistory(Request $request)
    {
        // ---------------- FILTER INPUTS ----------------
        $range = $request->get('range', 'today');
        $from = $request->get('from');
        $to = $request->get('to');
        $type = $request->get('type');
        $name = $request->get('name');
        $vehicle = $request->get('vehicle');
        $job = $request->get('job');
        $guardId = $request->get('guard_id');
        $entryStatus = $request->get('entry_status');
        $societyId = $request->get('society_id');
        $phone = $request->get('phone');



        // ---------------- DATE RANGE ----------------
        if ($range === 'today') {
            $startDate = now()->startOfDay();
            $endDate = now()->endOfDay();
        } elseif ($range === 'week') {
            $startDate = now()->startOfWeek();
            $endDate = now()->endOfWeek();
        } elseif ($range === 'month') {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        } elseif ($range === 'custom' && $from && $to) {
            $startDate = Carbon::parse($from)->startOfDay();
            $endDate = Carbon::parse($to)->endOfDay();
        } else {
            $startDate = now()->startOfDay();
            $endDate = now()->endOfDay();
        }

        $results = collect();

        // ---------------- VISITORS ----------------
        if (!$type || $type === 'visitor') {

            //add created_at to pass model
            $passes = Pass::whereBetween('scanned_in_at', [$startDate, $endDate])
                ->with(['scannedInGuard:id,name', 'scannedOutGuard:id,name', 'createdByUser:id,name,email,phone', 'society:id,name'])
                ->get();

            foreach ($passes as $p) {
                $results->push([
                    'type' => 'Visitor',
                    'name' => $p->guest_name,
                    'to_phone' => $p->guest_phone ?? '-',
                    'resident_id' => optional($p->createdByUser)->id,
                    'resident_name' => optional($p->createdByUser)->name,
                    'resident_phone' => optional($p->createdByUser)->phone ?? '-',
                    'society_id' => optional($p->society)->id,
                    'society_name' => optional($p->society)->name,
                    'vehicle' => $p->vehicle_number,
                    'job' => '-',
                    'entry_time' => $p->scanned_in_at,
                    'exit_time' => $p->scanned_out_at,
                    'entry_guard_id' => optional($p->scannedInGuard)->id,
                    'entry_guard' => optional($p->scannedInGuard)->name,
                    'exit_guard_id' => optional($p->scannedOutGuard)->id,
                    'exit_guard' => optional($p->scannedOutGuard)->name,
                    'sort_time' => $p->scanned_in_at,
                ]);
            }
        }

        // ---------------- WORKERS ----------------
        if (!$type || $type === 'worker') {
            $appointments = Appointment::whereBetween('guard_in_time', [$startDate, $endDate])
                ->with(['guardIn:id,name', 'guardOut:id,name', 'employer:id,name,email,phone', 'jobApplication.jobPost:id,job_title', 'society:id,name'])
                ->get();

            foreach ($appointments as $a) {
                $results->push([
                    'type' => 'Worker',
                    'resident_name' => optional($a->jobApplication?->resident)->name,
                    'resident_id' => optional($a->jobApplication?->resident)->id,
                    'resident_phone' => optional($a->jobApplication?->resident)->phone,
                    'name' => optional($a->employer)->name ?? 'Worker',
                    'to_phone' => optional($a->employer)->phone ?? '-',
                    'society_id' => optional($a->society)->id,
                    'society_name' => optional($a->society)->name,
                    'vehicle' => '-',
                    'job' => optional($a->jobApplication?->jobPost)->job_title,
                    'entry_time' => $a->guard_in_time,
                    'exit_time' => $a->guard_out_time,
                    'entry_guard_id' => optional($a->guardIn)->id,
                    'entry_guard' => optional($a->guardIn)->name,
                    'exit_guard_id' => optional($a->guardOut)->id,
                    'exit_guard' => optional($a->guardOut)->name,
                    'sort_time' => $a->guard_in_time,
                ]);
            }
        }

        // ---------------- VEHICLES ----------------
        if (!$type || $type === 'vehicle') {
            $vehicles = VehicleLog::whereBetween('created_at', [$startDate, $endDate])
                ->with(['vehicle:id,vehicle_number,user_id', 'vehicle.user:id,name,email,phone', 'society:id,name'])
                ->get();


            foreach ($vehicles as $v) {
                $guard = User::find($v->guard_id);

                $results->push([
                    'type' => 'Vehicle',
                    'name' => 'Vehicle Entry',
                    'to_phone' => '-',
                    'vehicle' => $v->vehicle_number,
                    'resident_id' => optional($v->vehicle->user)->id,
                    'resident_name' => optional($v->vehicle->user)->name,
                    'society_id' => optional($v->society)->id,
                    'society_name' => optional($v->society)->name,
                    'resident_phone' => optional($v->vehicle->user)->phone,
                    'job' => '-',
                    'entry_time' => $v->direction === 'in' ? $v->created_at : null,
                    'exit_time' => $v->direction === 'out' ? $v->created_at : null,
                    'entry_guard_id' => $v->direction === 'in' ? $guard?->id : null,
                    'entry_guard' => $v->direction === 'in' ? $guard?->name : null,
                    'exit_guard_id' => $v->direction === 'out' ? $guard?->id : null,
                    'exit_guard' => $v->direction === 'out' ? $guard?->name : null,
                    'sort_time' => $v->created_at,
                ]);
            }
        }

        // ---------------- APPLY FILTERS ----------------
        $results = $results->filter(function ($r) use (
            $name,
            $vehicle,
            $job,
            $guardId,
            $entryStatus,
            $societyId,
            $phone
        ) {



            // ---------------- FILTERS ----------------
            //check in to_phone or resident_phone
            if ($phone && !str_contains(strtolower($r['to_phone']), strtolower($phone)) && !str_contains(strtolower($r['resident_phone']), strtolower($phone))) {
                return false;
            }

            if ($name && !str_contains(strtolower($r['name']), strtolower($name))) {
                return false;
            }
            if ($phone && !str_contains(strtolower($r['resident_phone']), strtolower($phone))) {
                return false;
            }
            if ($vehicle && !str_contains(strtolower($r['vehicle']), strtolower($vehicle))) {
                return false;
            }
            if ($job && !str_contains(strtolower($r['job']), strtolower($job))) {
                return false;
            }

            if ($guardId && !in_array($guardId, [
                $r['entry_guard_id'],
                $r['exit_guard_id'],
            ])) {
                return false;
            }

            if ($entryStatus === 'in' && !$r['entry_time']) {
                return false;
            }
            if ($entryStatus === 'out' && !$r['exit_time']) {
                return false;
            }
            if ($societyId && $r['society_id'] !== $societyId) {
                return false;
            }

            return true;
        });



        $logs = $results->sortByDesc('sort_time')->values();

        // ---------------- GUARDS LIST FOR DROPDOWN ----------------
        $guards = User::where('role', 'guard')->orderBy('name')->get();
        $societies = Society::orderBy('name')->get();



        return view('admin.guards.pass-history', compact(
            'logs',
            'guards',
            'guardId',
            'phone',
            'range',
            'from',
            'to',
            'type',
            'name',
            'vehicle',
            'job',
            'entryStatus',
            'societyId',
            'societies'
        ));
    }


}
