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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class JobPostController extends Controller
{
    /**
     * Resident Dashboard Summary
     * Active jobs, applications, completed jobs, balance
     */

    private function getGoogleDistance($originLat, $originLng, $destLat, $destLng)
    {
        $apiKey = env('GOOGLE_MAPS_API_KEY');

        if (!empty($apiKey)) {
            try {
                $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$originLat,$originLng&destinations=$destLat,$destLng&mode=driving&key=$apiKey";

                $response = @file_get_contents($url);
                if ($response) {
                    $data = json_decode($response, true);

                    if (
                        isset($data['rows'][0]['elements'][0]['distance']['value'])
                    ) {
                        return $data['rows'][0]['elements'][0]['distance']['value'] / 1000; // km
                    }
                }
            } catch (\Throwable $e) {
                // Fallback to Haversine
            }
        }

        return $this->calculateHaversineDistance($originLat, $originLng, $destLat, $destLng);
    }

    private function calculateHaversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        if (!$lat1 || !$lon1 || !$lat2 || !$lon2) {
            return null;
        }

        $earthRadius = 6371; // Radius of the earth in km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 2);
    }


    public function residentDashboard($userId)
    {
        // -------------------------------
        // ACTIVE JOBS (Posted by resident)
        // -------------------------------
        $activeJobs = JobPost::where('user_id', $userId)
            ->where('status', 'active')
            ->count();

        // -------------------------------
        // TOTAL JOBS
        // -------------------------------
        $totalJobs = JobPost::where('user_id', $userId)->count();

        // -------------------------------
        // APPLICATIONS ON RESIDENT JOBS
        // -------------------------------
        $applications = JobApplication::whereHas('jobPost', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->count();

        // -------------------------------
        // HIRED WORKERS
        // -------------------------------
        $hiredJobs = JobApplication::whereHas('jobPost', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->where('status', 'hired')
            ->count();

        // -------------------------------
        // COMPLETED JOBS
        // -------------------------------
        $completedJobs = JobApplication::whereHas('jobPost', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->where('status', 'completed')
            ->count();

        // -------------------------------
        // BALANCE CALCULATION
        // -------------------------------
        $appointments = DB::table('appointments')
            ->join('job_applications', 'appointments.job_application_id', '=', 'job_applications.id')
            ->join('job_posts', 'job_applications.job_id', '=', 'job_posts.id')
            ->where('job_posts.user_id', $userId)
            ->select(
                'appointments.advance_amount',
                'appointments.final_amount',
                'appointments.advance_paid',
                'appointments.final_paid'
            )
            ->get();

        $totalAmount = 0;
        $paidAmount = 0;
        $pendingAmount = 0;

        foreach ($appointments as $appt) {
            $jobTotal = ($appt->advance_amount ?? 0) + ($appt->final_amount ?? 0);
            $totalAmount += $jobTotal;

            if ($appt->advance_paid) {
                $paidAmount += $appt->advance_amount ?? 0;
            } else {
                $pendingAmount += $appt->advance_amount ?? 0;
            }

            if ($appt->final_paid) {
                $paidAmount += $appt->final_amount ?? 0;
            } else {
                $pendingAmount += $appt->final_amount ?? 0;
            }
        }

        return response()->json([
            'status' => true,
            'data' => [
                'jobs' => [
                    'total' => $totalJobs,
                    'active' => $activeJobs,
                    'completed' => $completedJobs,
                ],
                'applications' => [
                    'total' => $applications,
                    'hired' => $hiredJobs,
                ],
                'balance' => [
                    'total_amount' => round($totalAmount, 2),
                    'paid_amount' => round($paidAmount, 2),
                    'pending_amount' => round($pendingAmount, 2),
                ],
            ],
        ]);
    }

    /**
     * ✅ Get all job posts
     */
    public function index(Request $request, $user_id = null)
    {
        $userId = $user_id ?? $request->query('user_id') ?? Auth::id();

        $matchSkill = $request->query('match_skill');

        // Employee profile
        $employee = null;
        if ($userId) {
            $employee = Employee::where('user_id', $userId)
                ->select('services', 'latitude', 'longitude', 'work_radius_km')
                ->first();
        }

        $employeeServices = [];
        if ($employee && !empty($employee->services)) {
            if (is_array($employee->services)) {
                $employeeServices = $employee->services;
            } else {
                $decoded = json_decode($employee->services, true);
                $employeeServices = is_array($decoded) ? $decoded : array_map('trim', explode(',', (string) $employee->services));
            }
        }

        // Jobs
        $jobsQuery = JobPost::with('user')
            ->where('status', 'active');

        $jobs = $jobsQuery->get();

        /**
         * Applied Jobs
         */
        $appliedJobIds = [];
        if ($userId) {
            $appliedJobIds = DB::table('job_applications')
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->pluck('job_id')
                ->toArray();
        }

        /**
         * Distance Calculation using Google API / Haversine fallback
         */
        foreach ($jobs as $job) {

            $job->distance_km = null;

            if (
                $employee &&
                $employee->latitude &&
                $employee->longitude &&
                $job->latitude &&
                $job->longitude
            ) {

                $distance = $this->getGoogleDistance(
                    $employee->latitude,
                    $employee->longitude,
                    $job->latitude,
                    $job->longitude
                );

                if ($distance !== null) {
                    $job->distance_km = round($distance, 2);
                }
            }
        }

        /**
         * Filter by work radius (only if coordinates exist and distance is measured)
         */
        if ($employee && !empty($employee->work_radius_km) && $employee->latitude && $employee->longitude) {
            $jobs = $jobs->filter(function ($job) use ($employee) {
                if ($job->distance_km !== null) {
                    return $job->distance_km <= $employee->work_radius_km;
                }
                return true;
            });
        }

        /**
         * Transform + skill match
         */
        $jobs = $jobs->filter(function ($job) use (
            $employeeServices,
            $appliedJobIds,
            $matchSkill
        ) {
            $jobSkills = [];
            if (!empty($job->skills_required)) {
                if (is_array($job->skills_required)) {
                    $jobSkills = $job->skills_required;
                } else {
                    $decoded = json_decode($job->skills_required, true);
                    $jobSkills = is_array($decoded) ? $decoded : array_map('trim', explode(',', (string) $job->skills_required));
                }
            }

            $jobTitle = (string) $job->job_title;

            $matched = false;
            if (!empty($employeeServices)) {
                foreach ($employeeServices as $empSkill) {
                    $empSkillLower = strtolower(trim((string) $empSkill));
                    if ($empSkillLower === '') continue;

                    // Check skills_required array
                    foreach ($jobSkills as $jobSkill) {
                        $jobSkillLower = strtolower(trim((string) $jobSkill));
                        if ($jobSkillLower === '') continue;

                        if (
                            $empSkillLower === $jobSkillLower ||
                            str_contains($jobSkillLower, $empSkillLower) ||
                            str_contains($empSkillLower, $jobSkillLower)
                        ) {
                            $matched = true;
                            break 2;
                        }
                    }

                    // Check job_title string
                    if (str_contains(strtolower($jobTitle), $empSkillLower)) {
                        $matched = true;
                        break;
                    }
                }
            }

            $job->skill_matched = $matched ? 1 : 0;
            $job->applied = in_array($job->id, $appliedJobIds);

            $job->user_details = $job->user;
            unset($job->user);

            if ($matchSkill === '1' || $matchSkill === 1 || $matchSkill === true) {
                return $job->skill_matched === 1;
            }

            return true;

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
        $job = JobPost::with(['user.employee', 'user.documents'])->find($id);

        if (!$job) {
            return response()->json([
                'status' => false,
                'message' => 'Job not found',
            ], 404);
        }

        $authUserId = auth()->id();

        $jobApplication = null;
        $jobApplicationStatus = 'not_applied';

        if ($authUserId) {
            $jobApplication = JobApplication::where('job_id', $id)
                ->where('user_id', $authUserId)
                ->first();
        }

        if (!$jobApplication) {
            $jobApplication = JobApplication::where('job_id', $id)->latest()->first();
        }

        if ($jobApplication) {
            $jobApplicationStatus = $jobApplication->status;
        }

        $resident = $job->user ?? User::find($job->user_id);

        $resident_details = null;
        if ($resident) {
            $resident_details = [
                'id' => $resident->id,
                'name' => $resident->name,
                'email' => $resident->email,
                'phone' => $resident->phone,
                'avatar' => $resident->avatar,
                'avatar_url' => $resident->avatar_url,
            ];
        }

        $certificate_photo_url = null;
        if ($jobApplication && $jobApplication->certificate_photo) {
            $certificate_photo_url = str_starts_with($jobApplication->certificate_photo, 'http')
                ? $jobApplication->certificate_photo
                : asset('storage/' . $jobApplication->certificate_photo);
        }

        return response()->json([
            'status' => true,
            'job_application_status' => $jobApplicationStatus,
            'certificate_photo' => $certificate_photo_url,
            'certificate_photo_url' => $certificate_photo_url,
            'certificate_path' => $jobApplication?->certificate_photo,
            'job_application' => $jobApplication,
            'data' => $job,
            'resident_details' => $resident_details,
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

        if ($user->user_residential_status == 'society') {
            $societyId = $user->details->society_id ?? null;
            if (!$societyId) {
                return response()->json([
                    'status' => false,
                    'message' => 'User does not belong to any society.',
                ], 400);
            }
        } else {
            $societyId = 0;
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
            'work_radius' => 'nullable|numeric|min:0',
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

        // get employee latitude and longitude auth user
        $userId = auth()->id();
        $employee = Employee::where('user_id', $userId)
            ->select('latitude', 'longitude')
            ->first();

        //work radius filter
        if ($request->filled('work_radius')) {
            // check if employee has latitude and longitude
            if (!$employee || !$employee->latitude || !$employee->longitude) {
                return response()->json([
                    'status' => false,
                    'message' => 'Employee location not found.',
                ], 200);
            }

            // Assuming you have latitude and longitude of the job posts stored in the database
            $userLatitude = $employee->latitude;
            $userLongitude = $employee->longitude;
            $workRadius = (float) $request->work_radius;

            // Haversine formula to calculate distance
            $query->selectRaw('job_posts.*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance', [
                $userLatitude,
                $userLongitude,
                $userLatitude,
            ])->having('distance', '<=', $workRadius);
        }

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
