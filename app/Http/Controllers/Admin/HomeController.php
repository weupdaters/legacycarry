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
use App\Models\Amenity;
use App\Models\AmenityBooking;
use App\Models\Poll;
use App\Models\PollVote;
use App\Models\Ticket;
use App\Models\Report;
use App\Models\ApiActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

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

        // 4. AMENITIES, POLLS, REPORTS & TICKETS
        $totalAmenities = Amenity::count();
        $totalAmenityBookings = AmenityBooking::count();
        $pendingAmenityBookings = AmenityBooking::where('status', 'pending')->count();
        $recentAmenityBookings = AmenityBooking::with(['amenity', 'society', 'user'])->latest()->take(5)->get();

        $totalPolls = Poll::count();
        $activePolls = Poll::where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->count();
        $totalPollVotes = PollVote::count();
        $recentPolls = Poll::with('society')->withCount('votes')->latest()->take(4)->get();

        $totalReports = Report::count();
        $pendingReports = Report::where('status', 'pending')->count();
        $recentReports = Report::with('user')->latest()->take(5)->get();

        $totalTickets = Ticket::count();
        $pendingTickets = Ticket::whereIn('status', ['open', 'in_progress'])->count();
        $recentTickets = Ticket::with(['society', 'user'])->latest()->take(5)->get();

        // 5. LISTS
        $recentJobs = JobPost::latest()->take(5)->get();
        $recentResidents = User::whereIn('role', ['customer', 'resident'])->latest()->take(5)->get();
        $recentNotices = class_exists(SocietyNotice::class) ? SocietyNotice::latest()->take(5)->get() : collect([]);
        $employees = User::with('employee')->latest()->paginate(10);
        $totalNotices = class_exists(SocietyNotice::class) ? SocietyNotice::count() : 0;
        $totalPasses = class_exists(Pass::class) ? Pass::count() : 0;

        // 6. CALCULATED PERFORMANCE (PERCENTAGES)
        $occupancyRate = $totalGuards > 0 ? ($activeGuardsOnShift / $totalGuards) * 100 : 0;
        $resolutionRate = $totalJobs > 0 ? ($closedJobs / $totalJobs) * 100 : 0;
        $verificationRate = $totalResidents > 0 ? (($totalResidents - $pendingVerifications) / $totalResidents) * 100 : 0;

        // 7. REAL-TIME LIVE USERS & API ACTIVITY TRACKER
        $activeOnlineUserIds = Cache::get('active_online_user_ids', []);
        $liveUsers = User::where(function ($q) use ($activeOnlineUserIds) {
            if (!empty($activeOnlineUserIds)) {
                $q->whereIn('id', $activeOnlineUserIds);
            }
            $q->orWhere('last_seen_at', '>=', now()->subMinutes(5));
        })->with('details.flat', 'details.society')->latest('last_seen_at')->take(8)->get();

        $liveUsersCount = $liveUsers->count();
        $recentApiActivities = ApiActivityLog::with('user')->latest('id')->take(10)->get();

        // 8. DEEP ANALYTICS (FOR CHARTS)
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
            'roleDistribution', 'jobTypeDistribution', 'societyDistribution', 'societyGrowth',
            'totalAmenities', 'totalAmenityBookings', 'pendingAmenityBookings', 'recentAmenityBookings',
            'totalPolls', 'activePolls', 'totalPollVotes', 'recentPolls',
            'totalReports', 'pendingReports', 'recentReports',
            'totalTickets', 'pendingTickets', 'recentTickets',
            'liveUsers', 'liveUsersCount', 'recentApiActivities'
        ));
    }

    /**
     * Real-time Live Activity Feed for Dashboard Auto-Refresh (GA4 style)
     */
    public function liveFeed()
    {
        $activeOnlineUserIds = Cache::get('active_online_user_ids', []);
        $liveUsers = User::where(function ($q) use ($activeOnlineUserIds) {
            if (!empty($activeOnlineUserIds)) {
                $q->whereIn('id', $activeOnlineUserIds);
            }
            $q->orWhere('last_seen_at', '>=', now()->subMinutes(5));
        })->with('details.flat', 'details.society')->latest('last_seen_at')->take(10)->get();

        $recentApiActivities = ApiActivityLog::with('user')->latest('id')->take(15)->get();

        return response()->json([
            'count' => $liveUsers->count(),
            'users' => $liveUsers->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'role' => ucfirst($u->role ?? 'User'),
                    'society' => $u->details->society->name ?? ($u->Society->name ?? 'Standalone'),
                    'flat' => $u->details->flat->flat_no ?? '',
                    'last_seen' => $u->last_seen_at ? $u->last_seen_at->diffForHumans() : 'Just now',
                    'last_endpoint' => $u->last_active_endpoint ?? 'API Active',
                ];
            }),
            'activities' => $recentApiActivities->map(function ($act) {
                return [
                    'id' => $act->id,
                    'user_name' => $act->user->name ?? ($act->user_name ?: ('Mobile User #' . ($act->user_id ?? 'Guest'))),
                    'endpoint' => $act->endpoint,
                    'method' => $act->method,
                    'device_type' => $act->device_type,
                    'status_code' => $act->status_code,
                    'response_time_ms' => $act->response_time_ms,
                    'time_ago' => $act->created_at ? $act->created_at->diffForHumans() : 'Just now',
                ];
            }),
        ]);
    }
}
