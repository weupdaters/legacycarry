<?php

namespace App\Http\Controllers\Admin;

use App\Exports\GlobalExport;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Guard;
use App\Models\JobApplication;
use App\Models\JobPost;
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
        $job = JobPost::with(['user', 'applications.user'])->findOrFail($id);
        return view('admin.jobs.view', compact('job'));
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
