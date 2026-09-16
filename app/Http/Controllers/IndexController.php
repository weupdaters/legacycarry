<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\ResidentAssignment;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\UserDocument;
use App\Models\UserPaymentMethod;
use App\Models\Vehicle;
use App\Models\Society;
use App\Models\Flat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $user = auth()->user();

        /**
         * 1. Get society_id for logged in member admin
         */
        $societyId = getSocietyId();

        if (!$societyId) {
            $assignment = ResidentAssignment::where('user_id', $userId)->first();
            $societyId = $assignment?->society_id ?? $user?->user_society_id ?? optional($user?->details)->society_id;
        }

        if (!$societyId) {
            return redirect()->route('login')->withErrors([
                'error' => 'You are not assigned to any society.',
            ]);
        }

        /**
         * 2. RESIDENTS QUERY (role = customer)
         */
        $residentsQuery = User::where('role', 'customer')
            ->where(function ($q) use ($societyId) {
                $q->where('user_society_id', $societyId)
                  ->orWhereHas('details', function ($dq) use ($societyId) {
                      $dq->where('society_id', $societyId);
                  })
                  ->orWhereHas('residentAssignment', function ($rq) use ($societyId) {
                      $rq->where('society_id', $societyId);
                  });
            });

        $totalEmployees = (clone $residentsQuery)->count();
        $employees = $residentsQuery->with(['details.flat', 'details.society', 'residentAssignment.flat'])->latest('id')->paginate(10);

        /**
         * 3. JOB COUNTS
         */
        $activeJobs = JobPost::where('society_id', $societyId)->where('status', 'active')->count();
        $inactiveJobs = JobPost::where('society_id', $societyId)->where('status', 'inactive')->count();
        $closedJobs = JobPost::where('society_id', $societyId)->where('status', 'closed')->count();
        $onHoldJobs = JobPost::where('society_id', $societyId)->where('status', 'on_hold')->count();

        $society = Society::find($societyId);

        /**
         * 4. Load dashboard
         */
        return view('member.dashboard', compact(
            'totalEmployees',
            'employees',
            'activeJobs',
            'inactiveJobs',
            'closedJobs',
            'onHoldJobs',
            'society'
        ));
    }


    public function Residents(Request $request)
    {
        if (!has_permission('resident_index')) {
            abort(403, 'Permission denied');
        }

        $userId = auth()->id();
        $user = auth()->user();

        $societyId = getSocietyId();

        if (!$societyId) {
            $assignment = ResidentAssignment::where('user_id', $userId)->first();
            $societyId = $assignment?->society_id ?? $user?->user_society_id ?? optional($user?->details)->society_id;
        }

        if (!$societyId) {
            return redirect()->route('login')->withErrors([
                'error' => 'You are not assigned to any society.',
            ]);
        }

        // Fetch residents belonging to the same society
        $residents = User::where('role', 'customer')
            ->where(function ($q) use ($societyId) {
                $q->where('user_society_id', $societyId)
                  ->orWhereHas('details', function ($dq) use ($societyId) {
                      $dq->where('society_id', $societyId);
                  })
                  ->orWhereHas('residentAssignment', function ($rq) use ($societyId) {
                      $rq->where('society_id', $societyId);
                  });
            })
            ->with(['details.flat', 'details.society', 'residentAssignment.flat'])
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('name', 'like', "%{$request->search}%")
                        ->orWhere('phone', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%");
                });
            })
            ->latest('id')
            ->paginate(100);

        return view('member.residents.index', compact('residents'));
    }


    public function viewResident($id)
    {
        if (!has_permission('resident_show')) {
            abort(403, 'Permission denied');
        }

        $loggedUserId = auth()->id();

        // Logged-in user's detail
        $loggedUser = ResidentAssignment::where('user_id', $loggedUserId)->first();

        if (!$loggedUser || !$loggedUser->society_id) {
            return redirect()->back()->with('error', 'You are not assigned to any society.');
        }

        // Load user with related details
        $user = User::with([
            'details',
            'details.society',
            'details.flat',
        ])->findOrFail($id);

        // Jobs created by user
        $jobs = JobPost::where('user_id', $id)
            ->latest()
            ->get();

        // Payment methods of user
        $payments = UserPaymentMethod::where('user_id', $user->id)
            ->latest()
            ->get();

        // Uploaded documents
        $documents = UserDocument::where('user_id', $user->id)
            ->latest()
            ->get();

        // ✅ ADD: Registered vehicles of user
        $vehicles = Vehicle::where('user_id', $user->id)
            ->latest()
            ->get();

        return view(
            'member.residents.view',
            compact('user', 'jobs', 'payments', 'documents', 'vehicles')
        );
    }



    public function updateVerification(Request $request, $id)
    {
        if (!has_permission('resident_edit')) {
            abort(403, 'Permission denied');
        }

        // Validate allowed values
        $request->validate([
            'verification_status' => 'required|in:unverified,pending,verified,rejected',
        ]);

        // Find user/employee
        $user = User::findOrFail($id);

        // Update field
        $user->verification_status = $request->verification_status;
        $user->save();

        // Return back with success message
        return back()->with('success', 'Verification status updated successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        if (!has_permission('resident_edit')) {
            abort(403, 'Permission denied');
        }

        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'remarks' => 'nullable|string|max:255',
        ]);

        $doc = UserDocument::findOrFail($id);

        $doc->status = $request->status;
        $doc->remarks = $request->remarks;
        $doc->save();

        return back()->with('success', 'Document status updated successfully.');
    }


    public function jobsIndex()
    {
        if (!has_permission('resident_edit')) {
            abort(403, 'Permission denied');
        }

        $loggedUserId = auth()->id();

        // Logged-in user's detail
        $loggedUser = UserDetail::where('user_id', $loggedUserId)->first();

        if (!$loggedUser || !$loggedUser->society_id) {
            return redirect()->back()->with('error', 'You are not assigned to any society.');
        }


        $jobs = JobPost::orderBy('id', 'desc')->paginate(10); // 10 per page
        return view('users.jobs.index', compact('jobs'));
    }



    public function ResiVehicleUpdateStatus(Request $request, $id)
    {
        if (!has_permission('resident_edit')) {
            abort(403, 'Permission denied');
        }

        $request->validate([
            'status' => 'required|in:active,inactive,pending',
        ]);

        $vehicle = Vehicle::findOrFail($id);
        $vehicle->status = $request->status;
        $vehicle->save();

        return back()->with('success', 'Vehicle status updated');
    }




    public function singlePage($slug)
    {
        $allowedPages = [
            'privacy_policy',
            'terms_and_conditions',
            'return_refund_policy',
        ];

        if (!in_array($slug, $allowedPages)) {
            abort(404);
        }

        $page = \App\Models\Setting::where('key', $slug)->first();

        if (!$page) {
            abort(404);
        }


        return view('pages.index', [
            'title' => $page->label,
            'content' => $page->value,
        ]);
    }

    /*--------------------------------------------------------
     | SHOW ADD + UPDATE BUILDINGS PAGE (MEMBER SIDE)
     --------------------------------------------------------*/
    public function manageFlats()
    {
        $userId = auth()->id();

        // Find Society assigned to this member
        $assignment = ResidentAssignment::where('user_id', $userId)->first();

        if (!$assignment || !$assignment->society_id) {
            return redirect()->back()->with('error', 'You are not assigned to any society.');
        }

        $society = Society::findOrFail($assignment->society_id);

        // Load existing flats to edit
        $flats = Flat::where('society_id', $society->id)->get();

        return view('member.societies.manageFlat', compact('society', 'flats'));
    }


    /*--------------------------------------------------------
     | STORE OR UPDATE MULTIPLE BUILDINGS (MEMBER SIDE)
     --------------------------------------------------------*/
    public function storeFlats(Request $request)
    {
        $request->validate([
            'society_id' => 'required|exists:societies,id',
            'flats'      => 'required|array|min:1',

            'flats.*.building_no' => 'required|string|max:100',
            'flats.*.owner_name'  => 'nullable|string|max:255',
            'flats.*.status'      => 'required|in:occupied,vacant',
        ]);

        $society_id = $request->society_id;

        // Security check: Ensure this member is assigned to this society
        $userId = auth()->id();
        $assignment = ResidentAssignment::where('user_id', $userId)
            ->where('society_id', $society_id)
            ->first();

        if (!$assignment) {
            abort(403, 'Unauthorized action.');
        }

        // Delete all old flats FIRST
        Flat::where('society_id', $society_id)->delete();

        // Recreate all submitted flats
        foreach ($request->flats as $flat) {
            Flat::create([
                'society_id'  => $society_id,
                'building_no' => $flat['building_no'],
                'owner_name'  => $flat['owner_name'] ?? null,
                'status'      => $flat['status'],
            ]);
        }

        return redirect()->route('member.flats.manage')
                         ->with('success', 'Buildings updated successfully!');
    }
}
