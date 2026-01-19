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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        /**
         * 1. Find UserDetail using user_id
         */
        $userDetail = ResidentAssignment::where('user_id', $userId)->first();

        if (!$userDetail || !$userDetail->society_id) {
            return redirect()->route('login')->withErrors([
                'error' => 'You are not assigned to any society.',
            ]);
        }

        /**
         * 2. Get society_id
         */
        $societyId = $userDetail->society_id;

        /**
         * 3. EMPLOYEES LIST (role = customer)
         */
        $employees = UserDetail::where('society_id', $societyId)
            ->whereHas('user', function ($q) {
                $q->where('role', 'customer');
            })
            ->with('user')
            ->paginate(10);

        /**
         * 4. JOB COUNTS
         */
        $activeJobs = JobPost::where('society_id', $societyId)->where('status', 'active')->count();
        $inactiveJobs = JobPost::where('society_id', $societyId)->where('status', 'inactive')->count();
        $closedJobs = JobPost::where('society_id', $societyId)->where('status', 'closed')->count();
        $onHoldJobs = JobPost::where('society_id', $societyId)->where('status', 'on_hold')->count();

        /**
         * 5. TOTAL EMPLOYEES COUNT
         */
        $totalEmployees = UserDetail::where('society_id', $societyId)
            ->whereHas('user', function ($q) {
                $q->where('role', 'customer');
            })
            ->count();

        /**
         * 6. Load dashboard
         */
        return view('member.dashboard', compact(
            'totalEmployees',
            'employees',
            'activeJobs',
            'inactiveJobs',
            'closedJobs',
            'onHoldJobs'
        ));
    }


    public function Residents()
    {
        $userId = auth()->id();

        // Find UserDetail using user_id
        $userDetail = ResidentAssignment::where('user_id', $userId)->first();

        if (!$userDetail || !$userDetail->society_id) {
            return redirect()->route('login')->withErrors([
                'error' => 'You are not assigned to any society.',
            ]);
        }

        // Get society_id
        $societyId = $userDetail->society_id;

        // Fetch residents belonging to the same society
        $residents = UserDetail::where('society_id', $societyId)
            ->whereHas('user', function ($q) {
                $q->where('role', 'customer');
            })
            ->with('user')
            ->paginate(100);



        return view('member.residents.index', compact('residents'));

    }


    public function viewResident($id)
    {
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
        $request->validate([
            'status' => 'required|in:active,inactive,pending',
        ]);

        $vehicle = Vehicle::findOrFail($id);
        $vehicle->status = $request->status;
        $vehicle->save();

        return back()->with('success', 'Vehicle status updated');
    }





}
