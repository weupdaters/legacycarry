<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\Society;
use App\Models\Resident;
use App\Models\SocietyNotice;
use App\Models\Pass;
use App\Models\Service;
use App\Models\Payment;
use App\Models\GuardShift;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // 1. FUNDAMENTAL COUNTERS
        $totalResidents = User::whereIn('role', ['customer', 'resident'])->count();
        $totalGuards = User::where('role', 'guard')->count();
        $totalLabors = User::where('role', 'labor')->count();
        $totalServices = Service::count();
        $totalSocieties = Society::count();
        $totalEmployees = User::where('role', 'employee')->count();
        $newEmployees = Employee::where('created_at', '>=', now()->subDays(30))->count();
        $resignedEmployees = User::where('role', 'employee')->where('is_active', 0)->count();

        // 2. OPERATIONAL STATUS
        $totalJobs = JobPost::count();
        $activeJobs = JobPost::where('status', 'active')->count();
        $inactiveJobs = JobPost::where('status', 'inactive')->count();
        $closedJobs = JobPost::where('status', 'closed')->count();
        $onHoldJobs = JobPost::where('status', 'on_hold')->count();
        $jobApplicants = JobApplication::count();
        $activeGuardsOnShift = class_exists(GuardShift::class) ? GuardShift::where('status', 'active')->count() : 0;
        $pendingVerifications = User::where('verification_status', 'pending')->count();

        // 3. FINANCIALS
        $totalRevenue = class_exists(Payment::class) ? Payment::sum('amount') : 0;
        $recentPayments = class_exists(Payment::class) ? Payment::latest()->take(5)->with('payer')->get() : collect([]);
        $revenueGoal = 1000000;
        $revenuePercent = ($totalRevenue / $revenueGoal) * 100;

        // 4. LISTS
        $recentJobs = JobPost::latest()->take(5)->get();
        $recentResidents = User::whereIn('role', ['customer', 'resident'])->latest()->take(5)->get();
        $recentNotices = class_exists(SocietyNotice::class) ? SocietyNotice::latest()->take(5)->get() : collect([]);
        $employees = User::with('employee')->latest()->paginate(10);
        $totalNotices = class_exists(SocietyNotice::class) ? SocietyNotice::count() : 0;
        $totalPasses = class_exists(Pass::class) ? Pass::count() : 0;

        // 5. CALCULATED PERFORMANCE (PERCENTAGES)
        $occupancyRate = $totalGuards > 0 ? ($activeGuardsOnShift / $totalGuards) * 100 : 0;
        $resolutionRate = $totalJobs > 0 ? ($closedJobs / $totalJobs) * 100 : 0;
        $verificationRate = $totalResidents > 0 ? (($totalResidents - $pendingVerifications) / $totalResidents) * 100 : 0;

        // 6. DEEP ANALYTICS (FOR CHARTS)
        $roleDistribution = User::select('role', DB::raw('count(*) as total'))->groupBy('role')->get();
        $jobTypeDistribution = JobPost::select('job_type', DB::raw('count(*) as total'))->groupBy('job_type')->get();
        $societyDistribution = DB::table('societies')->select('name', DB::raw('count(*) as total'))->groupBy('name')->take(6)->get();
        
        $societyGrowth = DB::table('users')
            ->join('societies', 'users.user_society_id', '=', 'societies.id')
            ->select('societies.name', DB::raw('count(*) as total'))
            ->groupBy('societies.name')
            ->orderBy('total', 'desc')
            ->take(6)
            ->get();

        return view('admin.dashboard', compact(
            'totalSocieties', 'totalResidents', 'totalGuards', 'totalLabors', 'totalServices',
            'totalEmployees', 'newEmployees', 'resignedEmployees', 'totalJobs', 'activeJobs',
            'inactiveJobs', 'closedJobs', 'onHoldJobs', 'jobApplicants', 'totalNotices',
            'totalPasses', 'totalRevenue', 'employees', 'recentJobs', 'recentNotices',
            'recentPayments', 'recentResidents', 'pendingVerifications', 'activeGuardsOnShift',
            'revenuePercent', 'occupancyRate', 'resolutionRate', 'verificationRate',
            'roleDistribution', 'jobTypeDistribution', 'societyDistribution', 'societyGrowth'
        ));
    }
}
