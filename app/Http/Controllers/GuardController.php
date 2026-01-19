<?php

namespace App\Http\Controllers;

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
            ->whereHas('details.society', function ($q) {
                $q->where('society_id', auth()->user()->assignment->society_id);
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
            return view('member.guards.partials.table', compact('guards'))->render();
        }

        return view('member.guards.index', compact('guards'));
    }


    /* =======================
        CREATE
    ======================= */
    public function create()
    {

        return view('member.guards.create');
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
                'society_id' => auth()->user()->assignment->society_id,
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
            ->route('member.guards.index')
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

        return view('member.guards.edit', compact('user'));
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
                    'society_id' => auth()->user()->assignment->society_id,
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
            ->route('member.guards.index')
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



        return view('member.guards.view', compact('user'));
    }

    /* =======================
        TOGGLE STATUS
    ======================= */
    public function toggleStatus(Request $request)
    {
        $guard = User::whereHas('assignment', function ($q) {
            $q->where('society_id', auth()->user()->assignment->society_id);
        })
            ->findOrFail($request->id);

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
        // ---------------- AUTH SOCIETY ----------------
        $societyId = auth()->user()->assignment->society_id ?? null;

        if (!$societyId) {
            abort(403, 'Society not assigned');
        }

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
        $phone = $request->get('phone');

        // ---------------- DATE RANGE ----------------
        if ($range === 'week') {
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

        // ================= VISITORS =================
        if (!$type || $type === 'visitor') {

            $passes = Pass::whereBetween('scanned_in_at', [$startDate, $endDate])
                    ->where('society_id', $societyId)
                    ->with([
                        'scannedInGuard:id,name',
                        'scannedOutGuard:id,name',
                        'createdByUser:id,name,phone',
                        'createdByUser.userDetails:id,user_id,floor_number,flat_id',
                        // ⚠️ id is REQUIRED here
                        'createdByUser.userDetails.flat:id,building_no,owner_name',
                        'society:id,name',
                    ])
                    ->get();



            foreach ($passes as $p) {

                $results->push([
                    'type' => 'Visitor',
                    'name' => $p->guest_name,
                    'to_phone' => $p->guest_phone ?? '-',
                    'resident_id' => optional($p->createdByUser)->id,
                    'building_no' => optional($p->createdByUser->userDetails->flat)->building_no ?? '-',
                    'floor_number' => optional($p->createdByUser->userDetails)->floor_number ?? '-',
                    'resident_name' => optional($p->createdByUser)->name,
                    'resident_phone' => optional($p->createdByUser)->phone ?? '-',
                    'society_id' => $p->society_id,
                    'society_name' => optional($p->society)->name,
                    'vehicle' => $p->vehicle_number ?? '-',
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

        // ================= WORKERS =================
        if (!$type || $type === 'worker') {

            $appointments = Appointment::whereBetween('guard_in_time', [$startDate, $endDate])
                ->where('society_id', $societyId)
                ->with([
                    'guardIn:id,name',
                    'guardOut:id,name',
                    'employer:id,name,phone',
                    'jobApplication.jobPost:id,job_title',
                    'jobApplication.resident:id,name,phone',
                    // resident details
                    'jobApplication.resident.userDetails:id,user_id,floor_number,flat_id',
                    // ⚠️ id REQUIRED to resolve flat_id → flats.id
                   'jobApplication.resident.userDetails.flat:id,building_no,owner_name',
                    'society:id,name',
                ])
                ->get();

            foreach ($appointments as $a) {
                $results->push([
                    'type' => 'Worker',
                    'name' => optional($a->employer)->name ?? 'Worker',
                    'to_phone' => optional($a->employer)->phone ?? '-',
                    'building_no' => optional($a->userDetails?->flat?->building_no)->building_no ?? '-',
                    'floor_number' => optional($a->userDetails?->floor_number)->floor_number ?? '-',
                    'resident_id' => optional($a->jobApplication?->resident)->id,
                    'resident_name' => optional($a->jobApplication?->resident)->name,
                    'resident_phone' => optional($a->jobApplication?->resident)->phone ?? '-',
                    'society_id' => $a->society_id,
                    'society_name' => optional($a->society)->name,
                    'vehicle' => '-',
                    'job' => optional($a->jobApplication?->jobPost)->job_title ?? '-',
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

        // ================= VEHICLES =================
        if (!$type || $type === 'vehicle') {

            $vehicles = VehicleLog::whereBetween('created_at', [$startDate, $endDate])
                    ->where('society_id', $societyId)
                    ->with(['vehicle:id,vehicle_number,user_id', 'vehicle.user:id,name,email,phone', 'society:id,name'])
                    ->get();


            foreach ($vehicles as $v) {
                $results->push([
                    'type' => 'Vehicle',
                    'name' => 'Vehicle Entry',
                    'to_phone' => '-',
                    'building_no' => optional($v->vehicle?->user?->userDetails?->flat?->building_no)->building_no ?? '-',
                    'floor_number' => optional($v->vehicle?->user?->userDetails?->floor_number)->floor_number ?? '-',
                    'vehicle' => $v->vehicle_number ?? '-',
                    'resident_id' => optional($v->vehicle?->user)->id,
                    'resident_name' => optional($v->vehicle?->user)->name,
                    'resident_phone' => optional($v->vehicle?->user)->phone ?? '-',
                    'society_id' => $v->society_id,
                    'society_name' => optional($v->society)->name,
                    'job' => '-',
                    'entry_time' => $v->direction === 'in' ? $v->created_at : null,
                    'exit_time' => $v->direction === 'out' ? $v->created_at : null,
                    'entry_guard_id' => $v->direction === 'in' ? optional($v->guard)->id : null,
                    'entry_guard' => $v->direction === 'in' ? optional($v->guard)->name : null,
                    'exit_guard_id' => $v->direction === 'out' ? optional($v->guard)->id : null,
                    'exit_guard' => $v->direction === 'out' ? optional($v->guard)->name : null,
                    'sort_time' => $v->created_at,
                ]);
            }
        }

        // ================= APPLY FILTERS =================
        $logs = $results->filter(function ($r) use ($name, $vehicle, $job, $guardId, $entryStatus, $phone) {


            //check in to_phone or resident_phone
            if ($phone && !str_contains(strtolower($r['to_phone']), strtolower($phone)) && !str_contains(strtolower($r['resident_phone']), strtolower($phone))) {
                return false;
            }


            if ($name && !str_contains(strtolower($r['name'] ?? ''), strtolower($name))) {
                return false;
            }

            if ($vehicle && !str_contains(strtolower($r['vehicle'] ?? ''), strtolower($vehicle))) {
                return false;
            }

            if ($job && !str_contains(strtolower($r['job'] ?? ''), strtolower($job))) {
                return false;
            }

            if ($guardId && !in_array($guardId, [$r['entry_guard_id'], $r['exit_guard_id']])) {
                return false;
            }

            if ($entryStatus === 'in' && empty($r['entry_time'])) {
                return false;
            }

            if ($entryStatus === 'out' && empty($r['exit_time'])) {
                return false;
            }

            return true;
        })->sortByDesc('sort_time')->values();

        // ---------------- GUARDS DROPDOWN ----------------
        $guards = User::where('role', 'guard')
            ->whereHas('assignment', fn ($q) => $q->where('society_id', $societyId))
            ->orderBy('name')
            ->get();

        return view('member.guards.pass-history', compact(
            'logs',
            'guards',
            'guardId',
            'range',
            'from',
            'to',
            'type',
            'name',
            'vehicle',
            'job',
            'entryStatus',
            'phone',
        ));
    }




}
