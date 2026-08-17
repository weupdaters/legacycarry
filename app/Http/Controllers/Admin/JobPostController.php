<?php

namespace App\Http\Controllers\Admin;

use App\Exports\GlobalExport;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Guard;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\Payment;
use App\Models\Society;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class JobPostController extends Controller
{
    /**
     * Show all jobs in table view
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $jobType = $request->job_type;
        $payType = $request->pay_type;
        $status = $request->status;
        $appsMin = $request->apps_min;
        $appsMax = $request->apps_max;

        $jobs = JobPost::withCount('applications')
            ->when($search, function ($q) use ($search) {
                $q->where('id', $search)
                ->orWhere('job_title', 'like', "%$search%")
                ->orWhere('location', 'like', "%$search%");
            })
            ->when($jobType, fn ($q) => $q->where('job_type', $jobType))
            ->when($payType, fn ($q) => $q->where('pay_type', $payType))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($appsMin, fn ($q) => $q->having('applications_count', '>=', (int)$appsMin))
            ->when($appsMax, fn ($q) => $q->having('applications_count', '<=', (int)$appsMax))
            ->orderBy('id', 'DESC')
            ->paginate(10);

        // AJAX Request → Return ONLY the table HTML
        if ($request->ajax()) {
            return view('admin.jobs.index', compact('jobs'))->renderSections()['table'];
        }

        return view('admin.jobs.index', compact('jobs'));
    }



    /**
     * Show create form
     */
    public function create()
    {
        return view('jobs.create');
    }


    public function view($id)
    {
        $job = JobPost::with([
            'user.employee',
            'user.documents',
            'applications.user.employee',
            'applications.appointment.resident',
            'applications.appointment.employer',
        ])->findOrFail($id);

        $documents = $job->user && $job->user->documents ? $job->user->documents : collect();

        // Get all application IDs
        $applicationIds = $job->applications->pluck('id')->toArray();

        // Fetch all payment logs for this job or its applications
        $payments = Payment::with(['payer', 'payee'])
            ->where(function ($q) use ($id, $applicationIds) {
                $q->where('job_post_id', $id);
                if (!empty($applicationIds)) {
                    $q->orWhereIn('job_application_id', $applicationIds);
                }
            })
            ->latest()
            ->get();

        // Build unified chronological activity timeline
        $timeline = collect();

        // 1. Job Created / Posted
        if ($job->created_at) {
            $timeline->push([
                'type' => 'job_created',
                'title' => 'Job Posted',
                'description' => 'Job "' . $job->job_title . '" was posted with budget of ₹' . number_format($job->pay_amount, 2) . ' (' . ucfirst(str_replace('_', ' ', $job->pay_type)) . ').',
                'user' => $job->user ? $job->user->name : 'Resident',
                'user_role' => 'Resident',
                'icon' => 'bi-briefcase-fill',
                'badge' => 'Posted',
                'badge_class' => 'bg-primary',
                'timestamp' => $job->created_at,
            ]);
        }

        // 2. Applications, Shortlisting, Hires, and Appointments
        foreach ($job->applications as $app) {
            $applicantName = $app->user ? $app->user->name : 'Worker #' . $app->user_id;

            // Application Received
            if ($app->created_at) {
                $timeline->push([
                    'type' => 'application_received',
                    'title' => 'Worker Applied: ' . $applicantName,
                    'description' => 'Applied with expected rate of ₹' . number_format($app->expected_cost, 2) . ($app->message_to_employer ? '. Message: "' . $app->message_to_employer . '"' : ''),
                    'user' => $applicantName,
                    'user_role' => 'Worker / Applicant',
                    'icon' => 'bi-person-check-fill',
                    'badge' => 'Applied',
                    'badge_class' => 'bg-info text-dark',
                    'timestamp' => $app->created_at,
                ]);
            }

            // Hired or Accepted
            if (in_array($app->status, ['hired', 'accepted', 'approved']) || $app->shortlisted) {
                $timeline->push([
                    'type' => 'application_hired',
                    'title' => 'Worker Hired: ' . $applicantName,
                    'description' => 'Resident hired ' . $applicantName . ' for this service.',
                    'user' => $job->user ? $job->user->name : 'Resident',
                    'user_role' => 'Resident',
                    'icon' => 'bi-person-fill-check',
                    'badge' => ucfirst($app->status),
                    'badge_class' => 'bg-success',
                    'timestamp' => $app->updated_at ?? $app->created_at,
                ]);
            }

            // Appointment Milestones
            if ($app->appointment) {
                $appt = $app->appointment;

                // Appointment created
                if ($appt->created_at) {
                    $timeline->push([
                        'type' => 'appointment_scheduled',
                        'title' => 'Appointment Booked',
                        'description' => 'Appointment #' . $appt->id . ' established. Total: ₹' . number_format($appt->total_amount, 2) . ' (Advance: ₹' . number_format($appt->advance_amount, 2) . ', Final: ₹' . number_format($appt->final_amount, 2) . ').',
                        'user' => 'System',
                        'user_role' => 'System',
                        'icon' => 'bi-calendar-check-fill',
                        'badge' => 'Scheduled',
                        'badge_class' => 'bg-primary-subtle text-primary',
                        'timestamp' => $appt->created_at,
                    ]);
                }

                // Guard In Time (Security Entry)
                if ($appt->guard_in_time) {
                    $timeline->push([
                        'type' => 'guard_in',
                        'title' => 'Gate Check-In (Entry)',
                        'description' => $applicantName . ' entered society premises. Entry Token verified by security guard.',
                        'user' => 'Security Guard',
                        'user_role' => 'Security',
                        'icon' => 'bi-shield-check',
                        'badge' => 'Gate IN',
                        'badge_class' => 'bg-warning text-dark',
                        'timestamp' => $appt->guard_in_time,
                    ]);
                }

                // Job Started
                if ($appt->job_start_time) {
                    $timeline->push([
                        'type' => 'job_started',
                        'title' => 'Work Started',
                        'description' => $applicantName . ' commenced service execution at resident unit.',
                        'user' => $applicantName,
                        'user_role' => 'Worker',
                        'icon' => 'bi-play-circle-fill',
                        'badge' => 'Started',
                        'badge_class' => 'bg-info',
                        'timestamp' => $appt->job_start_time,
                    ]);
                }

                // Job Ended
                if ($appt->job_end_time) {
                    $timeline->push([
                        'type' => 'job_ended',
                        'title' => 'Work Finished',
                        'description' => $applicantName . ' concluded service work.',
                        'user' => $applicantName,
                        'user_role' => 'Worker',
                        'icon' => 'bi-stop-circle-fill',
                        'badge' => 'Work Ended',
                        'badge_class' => 'bg-secondary',
                        'timestamp' => $appt->job_end_time,
                    ]);
                }

                // Resident Confirmed
                if ($appt->resident_confirmation) {
                    $timeline->push([
                        'type' => 'resident_confirmed',
                        'title' => 'Resident Confirmed Work',
                        'description' => 'Resident confirmed satisfactory completion of service.',
                        'user' => $job->user ? $job->user->name : 'Resident',
                        'user_role' => 'Resident',
                        'icon' => 'bi-patch-check-fill',
                        'badge' => 'Confirmed',
                        'badge_class' => 'bg-success',
                        'timestamp' => $appt->updated_at,
                    ]);
                }

                // Guard Out Time (Security Exit)
                if ($appt->guard_out_time) {
                    $timeline->push([
                        'type' => 'guard_out',
                        'title' => 'Gate Check-Out (Exit)',
                        'description' => $applicantName . ' safely exited society premises. Exit token verified.',
                        'user' => 'Security Guard',
                        'user_role' => 'Security',
                        'icon' => 'bi-door-open-fill',
                        'badge' => 'Gate OUT',
                        'badge_class' => 'bg-dark',
                        'timestamp' => $appt->guard_out_time,
                    ]);
                }
            }

            // Application Completed
            if ($app->completed_at) {
                $timeline->push([
                    'type' => 'application_completed',
                    'title' => 'Service Completed for ' . $applicantName,
                    'description' => 'Application concluded with completed status.',
                    'user' => 'System',
                    'user_role' => 'System',
                    'icon' => 'bi-flag-fill',
                    'badge' => 'Completed',
                    'badge_class' => 'bg-success',
                    'timestamp' => $app->completed_at,
                ]);
            }
        }

        // 3. Payment Transactions
        foreach ($payments as $payment) {
            $paymentTypeLabel = ucfirst($payment->payment_type ?? ($payment->notes ? $payment->notes : 'payment'));
            $payerName = $payment->payer ? $payment->payer->name : 'Payer';
            $payeeName = $payment->payee ? $payment->payee->name : 'Payee';
            $isSuccess = in_array($payment->status, ['success', 'paid', 'completed']);

            $timeline->push([
                'type' => 'payment_' . ($payment->status ?? 'log'),
                'title' => 'Payment: ₹' . number_format($payment->amount, 2) . ' (' . $paymentTypeLabel . ')',
                'description' => 'From: ' . $payerName . ' ➔ To: ' . $payeeName . ' via ' . strtoupper($payment->payment_method ?? 'ONLINE') . ($payment->payment_reference ? ' [Ref: ' . $payment->payment_reference . ']' : '') . '. Status: ' . ucfirst($payment->status),
                'user' => $payerName,
                'user_role' => 'Payment Transaction',
                'icon' => 'bi-cash-stack',
                'badge' => ucfirst($payment->status),
                'badge_class' => $isSuccess ? 'bg-success' : ($payment->status === 'pending' ? 'bg-warning text-dark' : 'bg-danger'),
                'timestamp' => $payment->paid_at ?? $payment->created_at,
            ]);
        }

        // 4. Job Overall Completed
        if ($job->status === 'completed' || $job->completed_at) {
            $timeline->push([
                'type' => 'job_completed',
                'title' => 'Job Completed Successfully',
                'description' => 'Job post #' . $job->id . ' has been completely fulfilled and marked as completed.',
                'user' => 'System',
                'user_role' => 'System',
                'icon' => 'bi-trophy-fill',
                'badge' => 'Finished',
                'badge_class' => 'bg-success',
                'timestamp' => $job->completed_at ?? $job->updated_at,
            ]);
        }

        // Sort chronologically (oldest to latest)
        $timeline = $timeline->sortBy('timestamp')->values();

        return view('admin.jobs.view', compact('job', 'documents', 'payments', 'timeline'));
    }



    /**
     * Store a new job (Web)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'address' => 'nullable|string',
            'job_type' => 'required|in:full_day,part_time,hourly',
            'workers_required' => 'required|integer|min:1',
            'skills_required' => 'nullable|array',
            'tools_provided' => 'required|boolean',
            'documents_required' => 'nullable|array',
            'safety_instructions' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'shift' => 'required|in:morning,afternoon,evening,night',
            'pay_type' => 'required|in:per_hour,per_day,fixed',
            'pay_amount' => 'required|numeric|min:0',
            'advance_payment' => 'required|boolean',
            'advance_amount' => 'nullable|numeric|min:0',
        ]);

        // Convert arrays to JSON
        $validated['skills_required'] = $request->skills_required ? json_encode($request->skills_required) : null;
        $validated['documents_required'] = $request->documents_required ? json_encode($request->documents_required) : null;

        // Attach job to logged-in user
        $validated['user_id'] = auth()->id();

        JobPost::create($validated);

        return redirect()
            ->route('jobs.index')
            ->with('success', 'Job created successfully!');
    }

    /**
     * Show single job details
     */
    public function show($id)
    {
        $job = JobPost::findOrFail($id);
        $is_applied = JobApplication::where('job_post_id', $id)
            ->where('user_id', auth()->id())
            ->exists();
        return view('admin.jobs.show', compact('job', 'is_applied'));
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $job = JobPost::findOrFail($id);
        return view('admin.jobs.edit', compact('job'));
    }

    /**
     * Update Job (Web)
     */
    public function update(Request $request, $id)
    {
        $job = JobPost::findOrFail($id);

        $validated = $request->validate([
            'job_title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'address' => 'nullable|string',
            'job_type' => 'required|in:full_day,part_time,hourly',
            'workers_required' => 'required|integer|min:1',
            'skills_required' => 'nullable|array',
            'tools_provided' => 'required|boolean',
            'documents_required' => 'nullable|array',
            'safety_instructions' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'shift' => 'required|in:morning,afternoon,evening,night',
            'pay_type' => 'required|in:per_hour,per_day,fixed',
            'pay_amount' => 'required|numeric|min:0',
            'advance_payment' => 'required|boolean',
            'advance_amount' => 'nullable|numeric|min:0',
            'status' => 'nullable|string',
        ]);

        // Convert arrays to JSON
        $validated['skills_required'] = $request->skills_required ? json_encode($request->skills_required) : null;
        $validated['documents_required'] = $request->documents_required ? json_encode($request->documents_required) : null;

        $job->update($validated);

        return redirect()
            ->route('admin.jobs.index')
            ->with('success', 'Job updated successfully!');
    }

    /**
     * Delete job
     */
    public function applications($jobId)
    {
        $job = JobPost::findOrFail($jobId);

        $query = $job->applications()->with('user');

        // SEARCH
        if (request('search')) {
            $search = request('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                ->orWhere('phone', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%");
            });
        }

        // STATUS FILTER
        if (request('status')) {
            $query->where('status', request('status'));
        }

        $applications = $query->paginate(10);

        // AJAX → return only the section HTML
        if (request()->ajax()) {
            $sections = view('admin.jobs.applications', compact('applications', 'job'))->renderSections();
            return $sections['applicationsTable'];
        }

        return view('admin.jobs.applications', compact('applications', 'job'));
    }




}
