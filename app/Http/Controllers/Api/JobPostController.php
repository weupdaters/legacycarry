<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class JobPostController extends Controller
{
    /**
     * ✅ Get all job posts
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $matchSkill = $request->query('match_skill'); // 1 or null

        // 🔹 Employee profile
        $employee = Employee::where('user_id', $userId)
            ->select('services', 'latitude', 'longitude', 'work_radius_km')
            ->first();

        $employeeServices = $employee && !empty($employee->services)
        ? (array) $employee->services
        : [];

        // 🔹 Jobs
        $jobsQuery = JobPost::with('user')
            ->where('status', 'active');

        /**
         * 🔹 Distance calculation
         */


        if ($employee && $employee->latitude && $employee->longitude) {

            $jobsQuery->selectRaw('
            job_posts.*,
            (
                6371 * acos(
                    cos(radians(?))
                    * cos(radians(job_posts.latitude))
                    * cos(radians(job_posts.longitude) - radians(?))
                    + sin(radians(?))
                    * sin(radians(job_posts.latitude))
                )
            ) AS distance_km
        ', [
                $employee->latitude,
                $employee->longitude,
                $employee->latitude,
            ]);
            // result of qery


            if ($employee->work_radius_km) {
                $jobsQuery->having('distance_km', '<=', $employee->work_radius_km);
            }

            $jobsQuery->orderBy('distance_km', 'asc');
        } else {
            $jobsQuery->selectRaw('job_posts.*, NULL AS distance_km')
              ->latest();
        }

        $jobs = $jobsQuery->get();



        /**
         * 🔹 Applied jobs
         */
        $appliedJobIds = DB::table('job_applications')
            ->where('user_id', $userId)
            ->pluck('job_id')
            ->toArray();

        /**
         * 🔹 Transform + skill match
         */
        $jobs = $jobs->filter(function ($job) use (
            $employeeServices,
            $appliedJobIds,
            $matchSkill
        ) {

            $jobSkills = $job->skills_required
                ? json_decode($job->skills_required, true)
                : [];

            $job->skill_matched = count(array_intersect(
                array_map('strtolower', $employeeServices),
                array_map('strtolower', $jobSkills)
            )) > 0 ? 1 : 0;

            $job->applied = in_array($job->id, $appliedJobIds);
            $job->user_details = $job->user;
            unset($job->user);

            if (isset($job->distance_km)) {
                $job->distance_km = round($job->distance_km, 2);
            }

            // ✅ FILTER HERE
            if ($matchSkill === '1') {
                return $job->skill_matched === 1;
            }

            return true; // return all if not requested
        })->values();

        return response()->json([
            'status' => true,
            'data' => $jobs,
        ], 200);
    }



    public function residentJobByID($id, $status = null, $pay_type = null)
    {
        $query = JobPost::where('user_id', $id);

        // Filter by status
        if (!empty($status)) {
            $query->where('status', $status);
        }

        // Filter by pay type (full_day, part_time, hourly)
        if (!empty($pay_type)) {
            $query->where('pay_type', $pay_type);
        }

        $jobs = $query->get();

        return response()->json([
            'status' => true,
            'data' => $jobs,
        ], 200);
    }


    /**
     * ✅ Get a single job post
     */
    public function show($id)
    {
        $job = JobPost::find($id);

        if (!$job) {
            return response()->json([
                'status' => false,
                'message' => 'Job not found',
            ], 404);
        }

        // check to token of api is valid
        $userId = auth()->id();

        $jobApplication = null;
        $jobApplicationStatus = 'not_applied';

        if ($userId) {
            $jobApplication = JobApplication::where('job_id', $id)
                ->first();

            if ($jobApplication) {
                $jobApplicationStatus = $jobApplication->status;
            }
        }

        return response()->json([
            'status' => true,
            'job_application_status' => $jobApplicationStatus,
            'data' => $job,
        ]);
    }




    /**
     * ✅ Create a new job post
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_title' => 'required|string|max:255',
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
            'user_id' => 'required|exists:users,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $errors = implode(' ', $validator->errors()->all());
            return response()->json([
                'status' => false,
                'message' => $errors,
            ], 422);
        }

        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $societyId = $user->details->society_id ?? null;
        if (!$societyId) {
            return response()->json([
                'status' => false,
                'message' => 'User does not belong to any society.',
            ], 400);
        }

        $validated = $validator->validated();
        $validated['society_id'] = $societyId;

        // ✅ Convert arrays to JSON if necessary
        if (isset($validated['skills_required'])) {
            $validated['skills_required'] = json_encode($validated['skills_required']);
        }
        if (isset($validated['documents_required'])) {
            $validated['documents_required'] = json_encode($validated['documents_required']);
        }

        $job = JobPost::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Job created successfully!',
            'data' => $job,
        ], 201);
    }

    /**
     * ✅ Update an existing job post
     */
    public function update(Request $request, $id)
    {
        $job = JobPost::find($id);

        if (!$job) {
            return response()->json([
                'status' => false,
                'message' => 'Job not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'job_title' => 'sometimes|required|string|max:255',
            'location' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'job_type' => 'sometimes|required|in:full_day,part_time,hourly',
            'workers_required' => 'nullable|integer|min:1',
            'skills_required' => 'nullable|array',
            'tools_provided' => 'nullable|boolean',
            'documents_required' => 'nullable|array',
            'safety_instructions' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'shift' => 'nullable|in:morning,afternoon,evening,night',
            'pay_type' => 'nullable|in:per_hour,per_day,fixed',
            'pay_amount' => 'nullable|numeric|min:0',
            'advance_payment' => 'nullable|boolean',
            'advance_amount' => 'nullable|numeric|min:0',
            'user_id' => 'nullable|exists:users,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $errors = implode(' ', $validator->errors()->all());
            return response()->json([
                'status' => false,
                'message' => $errors,
            ], 422);
        }

        $validated = $validator->validated();

        // ✅ Convert arrays to JSON if necessary
        if (isset($validated['skills_required'])) {
            $validated['skills_required'] = json_encode($validated['skills_required']);
        }
        if (isset($validated['documents_required'])) {
            $validated['documents_required'] = json_encode($validated['documents_required']);
        }

        $job->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Job updated successfully!',
            'data' => $job,
        ], 200);
    }

    /**
     * ✅ Delete a job post
     */
    public function destroy($id)
    {
        $job = JobPost::find($id);

        if (!$job) {
            return response()->json([
                'status' => false,
                'message' => 'Job not found.',
            ], 404);
        }

        $job->delete();

        return response()->json([
            'status' => true,
            'message' => 'Job deleted successfully!',
        ], 200);
    }

    /**
     * ✅ Change job status and approved_by
     */
    public function changestatus(Request $request, $id)
    {
        $job = JobPost::find($id);

        if (!$job) {
            return response()->json([
                'status' => false,
                'message' => 'Job not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:draft,active,closed,complete,pending',
            'approved_by' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            $errors = implode(' ', $validator->errors()->all());
            return response()->json([
                'status' => false,
                'message' => $errors,
            ], 422);
        }

        $job->status = $request->status;
        $job->approved_by = $request->approved_by;
        $job->save();

        return response()->json([
            'status' => true,
            'message' => 'Job status updated successfully!',
            'data' => $job,
        ], 200);
    }

    /**
     * ✅ Search jobs by keyword in title or location
     */
    public function searchJobs(Request $request)
    {
        $keyword = $request->input('keyword');

        $jobs = JobPost::where('job_title', 'LIKE', "%{$keyword}%")
                       ->Where('status', 'active')
                       ->get();

        return response()->json([
            'status' => true,
            'data' => $jobs,
        ], 200);
    }

    /**
     * ✅ Get jobs by user ID
     */
    public function jobsByUserId(Request $request, $user_id)
    {
        $keyword = $request->input('keyword');
        $jobs = JobPost::where('job_title', 'LIKE', "%{$keyword}%")
        ->where('user_id', $user_id)->get();

        return response()->json([
            'status' => true,
            'data' => $jobs,
        ], 200);
    }


    public function filterJobs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'keyword' => 'nullable|string|max:255',
            'min_pay' => 'nullable|numeric|min:0',
            'max_pay' => 'nullable|numeric|min:0',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => implode(' ', $validator->errors()->all()),
            ], 422);
        }

        $query = JobPost::query()
            ->where('status', 'active');

        /* 🔍 Keyword search */
        // if ($request->filled('keyword')) {
        //     $query->where('job_title', 'LIKE', '%' . $request->keyword . '%');
        // }

        // /* 💰 Pay range filter */
        // if ($request->filled('min_pay')) {
        //     $query->where('pay_amount', '>=', (float) $request->min_pay);
        // }

        if ($request->has('min_pay') && $request->min_pay !== '') {
            $query->whereRaw('CAST(pay_amount AS DECIMAL(10,2)) >= ?', [
                (float) $request->min_pay,
            ]);
        }

        if ($request->has('max_pay') && $request->max_pay !== '') {
            $query->whereRaw('CAST(pay_amount AS DECIMAL(10,2)) <= ?', [
                (float) $request->max_pay,
            ]);
        }


        if ($request->filled('max_pay')) {
            $query->where('pay_amount', '<=', (float) $request->max_pay);
        }

        /* 🛠 Skill filter (JSON column) */
        if ($request->has('skills')) {
            $query->where(function ($q) use ($request) {
                foreach ($request->skills as $skill) {
                    $q->orWhere('skills_required', 'LIKE', '%"' . $skill . '"%');
                }
            });
        }

        /* 📅 Date filter */
        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        $jobs = $query->latest()->get();

        return response()->json([
            'status' => true,
            'count' => $jobs->count(),
            'data' => $jobs,
        ], 200);
    }

}
