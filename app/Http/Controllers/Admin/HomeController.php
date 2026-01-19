<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use App\Models\JobApplication;
use App\Models\JobPost;


class HomeController extends Controller
{
    public function index()
    {
        // USERS
        $totalEmployees = User::whereHas('employee')->count();
        $newEmployees   = User::whereHas('employee')
                            ->whereDate('created_at', '>=', now()->subDays(30))
                            ->count();

        $resignedEmployees = Employee::where('availability', 'Full-Time')->count();

        // JOBS
        $jobApplicants = JobApplication::count();
        $activeJobs     = JobPost::where('status', 'active')->count();
        $inactiveJobs   = JobPost::where('status', 'inactive')->count();
        $closedJobs     = JobPost::where('status', 'completed')->count();
        $onHoldJobs     = JobPost::where('status', 'on_hold')->count();

        // EMPLOYEE LIST
        $employees = User::with('employee')->paginate(10);

        return view('admin.dashboard', compact(
            'totalEmployees',
            'newEmployees',
            'resignedEmployees',
            'jobApplicants',
            'activeJobs',
            'inactiveJobs',
            'closedJobs',
            'onHoldJobs',
            'employees'
        ));
    }
}
