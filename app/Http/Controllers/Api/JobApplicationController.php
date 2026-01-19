<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Employee;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class JobApplicationController extends Controller
{
    /**
     * Submit a new job application
     */
    public function submitApplication(Request $request)
    {
        // ✅ Validate request
        $validated = $request->validate([
            'job_id' => 'required|exists:job_posts,id',
            'user_id' => 'required|exists:users,id',
            'resident_id' => 'required|exists:users,id',
            'start_datetime' => 'required|date',
            'expected_cost' => 'required|numeric',
            'message_to_employer' => 'required|string',
            'certificate_photo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // check if user already applied for the job
        $existingApplication = JobApplication::where('job_id', $validated['job_id'])
            ->where('user_id', $validated['user_id'])
            ->first();


        if ($existingApplication) {
            return response()->json([
                'status' => false,
                'message' => 'You have already applied for this job.',
            ], 200);
        }


        // ✅ Handle optional file upload
        $certificatePath = null;
        if ($request->hasFile('certificate_photo')) {
            $certificatePath = $request->file('certificate_photo')->store('certificates', 'public');
        }

        // ✅ Store data
        $jobApplication = JobApplication::create([
            'job_id' => $validated['job_id'],
            'user_id' => $validated['user_id'],
            'resident_id' => $validated['resident_id'],
            'start_datetime' => $validated['start_datetime'],
            'expected_cost' => $validated['expected_cost'],
            'message_to_employer' => $validated['message_to_employer'],
            'certificate_photo' => $certificatePath,
        ]);

        // ✅ Return response
        return response()->json([
            'status' => true,
            'message' => 'Job application submitted successfully.',
            'application' => $jobApplication,
        ], 200);
    }

    /**
     * check aplied job by user id
     *
     */
    public function checkAppliedJob(Request $request)
    {
        // No validation error response — handle manually
        $jobExists = JobApplication::where('id', $request->job_id)->exists();
        $userExists = User::where('id', $request->user_id)->exists();

        // If job or user not exist → return false
        if (!$jobExists || !$userExists) {
            return response()->json(false);
        }

        // Check application
        $application = JobApplication::where('job_id', $request->job_id)
            ->where('user_id', $request->user_id)
            ->exists(); // <— returns true/false

        return response()->json($application);
    }


    /**
     * Get all job applications
     */
    public function getApplicationsByUserId($user_id)
    {
        $applications = JobApplication::with(['user', 'employee'])
            ->where('user_id', $user_id)
            ->latest()
            ->get()
            ->map(function ($application) {

                return [
                    'application_id' => $application->id,
                    'job_id' => $application->job_id,
                    'user_id' => $application->user_id,
                    'status' => $application->status,
                    'shortlisted' => $application->shortlisted,
                    'name' => $application->user->name,
                    'avatar' => $application->user->avatar,
                    'services' => isset($application->employee->services)
                                            ? implode(', ', $application->employee->services)
                                            : '',
                    'about' => $application->employee ? $application->employee->about : '',
                    'id_number' => $application->employee ? $application->employee->id_number : '',
                    'message_to_employer' => $application->message_to_employer,
                    'user_detils' => $application->user,
                ];
            });

        return response()->json([
            'status' => true,
            'applications' => $applications,
        ], 200);
    }




    /**
     * Get all applications for jobs posted by a specific user (employer)
     */
    public function getApplicationsResident($user_id)
    {
        $applications = JobApplication::with(['user', 'employee'])
            ->where('resident_id', $user_id)
            ->latest()
            ->get()
            ->map(function ($application) {

                $appointment = $application->appointment;

                // Defaults
                $paymentStage = 'none';
                $paymentAmount = 0;
                $paymentStatus = 'unpaid';

                if ($appointment) {

                    // 🔹 ADVANCE PENDING
                    if ($appointment->advance_amount > 0 && !$appointment->advance_paid) {
                        $paymentStage = 'advance';
                        $paymentAmount = $appointment->advance_amount;
                        $paymentStatus = 'pending';
                    }

                    // 🔹 FINAL PENDING (advance already paid)
                    elseif ($appointment->advance_paid && !$appointment->final_paid) {
                        $paymentStage = 'final';
                        $paymentAmount = $appointment->final_amount;
                        $paymentStatus = 'pending';
                    }

                    // 🔹 FULLY PAID
                    elseif ($appointment->final_paid) {
                        $paymentStage = 'completed';
                        $paymentAmount = $appointment->final_amount;
                        $paymentStatus = 'paid';
                    }
                }

                return [
                    'application_id' => $application->id,
                    'job_id' => $application->job_id,
                    'user_id' => $application->user_id,
                    'status' => $application->status,
                    'shortlisted' => $application->shortlisted,
                    'name' => $application->user->name,
                    'avatar' => $application->user->avatar,
                    'services' => isset($application->employee->services)
                                            ? implode(', ', $application->employee->services)
                                            : '',
                    'about' => $application->employee->about,
                    'id_number' => $application->employee->id_number,
                    'message_to_employer' => $application->message_to_employer,
                    // 💰 DERIVED PAYMENT INFO (NO PARAM USED)
                    'payment_stage' => $paymentStage,   // advance | final | completed | none
                    'payment_amount' => $paymentAmount,
                    'payment_status' => $paymentStatus,
                    'user_detils' => $application->user,
                ];
            });

        return response()->json([
            'status' => true,
            'applications' => $applications,
        ], 200);
    }

    public function oldgetApplicationsResident($user_id)
    {
        $applications = JobApplication::with(['user', 'employee', 'appointment'])
            ->where('resident_id', $user_id)
            ->latest()
            ->get()
            ->map(function ($application) {

                $appointment = $application->appointment;

                // Defaults
                $paymentStage = 'none';
                $paymentAmount = 0;
                $paymentStatus = 'unpaid';

                if ($appointment) {

                    // 🔹 ADVANCE PENDING
                    if ($appointment->advance_amount > 0 && !$appointment->advance_paid) {
                        $paymentStage = 'advance';
                        $paymentAmount = $appointment->advance_amount;
                        $paymentStatus = 'pending';
                    }

                    // 🔹 FINAL PENDING (advance already paid)
                    elseif ($appointment->advance_paid && !$appointment->final_paid) {
                        $paymentStage = 'final';
                        $paymentAmount = $appointment->final_amount;
                        $paymentStatus = 'pending';
                    }

                    // 🔹 FULLY PAID
                    elseif ($appointment->final_paid) {
                        $paymentStage = 'completed';
                        $paymentAmount = $appointment->final_amount;
                        $paymentStatus = 'paid';
                    }
                }

                return [
                    'application_id' => $application->id,
                    'job_id' => $application->job_id,
                    'user_id' => $application->user_id,
                    'status' => $application->status,
                    'shortlisted' => $application->shortlisted,

                    'name' => $application->user->name,
                    'avatar' => $application->user->avatar,

                    'services' => $application->employee?->services
                                        ? implode(', ', $application->employee->services)
                                        : '',

                    'about' => $application->employee?->about,
                    'id_number' => $application->employee?->id_number,

                    // 💰 DERIVED PAYMENT INFO (NO PARAM USED)
                    'payment_stage' => $paymentStage,   // advance | final | completed | none
                    'payment_amount' => $paymentAmount,
                    'payment_status' => $paymentStatus,

                    'appointment_status' => $appointment?->status,
                    'user_details' => $application->user,
                ];
            });

        return response()->json([
            'status' => true,
            'applications' => $applications,
        ], 200);
    }


    public function reschedule(Request $request, $id)
    {
        // Validate inputs
        $validated = $request->validate([
            'new_date' => 'required|date',
            'new_time' => 'required|date_format:H:i',
        ]);

        // Find application
        $application = JobApplication::findOrFail($id);

        // Combine date + time
        $newDateTime = $validated['new_date'] . ' ' . $validated['new_time'];

        // Update start_datetime
        $application->start_datetime = $newDateTime;
        $application->save();

        return response()->json([
            'status' => true,
            'message' => 'Application rescheduled successfully.',
            'application' => $application,
        ]);
    }


    public function resJobStatus(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:job_applications,id',
            'status' => 'nullable|in:cancel,hired,rejected,completed',
            'shortlisted' => 'nullable|boolean',
        ]);

        $application = JobApplication::findOrFail($validated['id']);

        /**
         * ⛔ Block status change if already final
         */
        if (
            isset($validated['status']) &&
            in_array($application->status, ['cancel', 'completed', 'hired'])
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Status cannot be changed because the application is already ' . $application->status,
            ], 403);
        }

        /**
         * ✅ CASE 1: ONLY SHORTLIST UPDATE
         * (status NOT passed)
         */
        if ($request->has('shortlisted') && empty($validated['status'])) {

            $application->shortlisted = $validated['shortlisted'];
            $application->save();

            return response()->json([
                'status' => true,
                'message' => 'Shortlist status updated successfully.',
                'application' => $application,
            ]);
        }

        /**
         * ✅ CASE 2: STATUS UPDATE
         */
        if (!empty($validated['status'])) {

            $application->status = $validated['status'];
            $application->save();

            // 🔄 Update Job Post status
            $jobPost = JobPost::find($application->job_id);

            if ($jobPost) {
                if ($validated['status'] === 'completed') {
                    $jobPost->status = 'completed';
                } elseif ($validated['status'] === 'cancel') {
                    $jobPost->status = 'cancel';
                } else {
                    $jobPost->status = 'active';
                }

                $jobPost->save();
            }

            /**
             * ⭐ AUTO CREATE APPOINTMENT ON HIRED
             */
            if ($validated['status'] === 'hired') {

                $resident = User::find($application->resident_id)->with('society')->first();
                $expectedCost = $application->expected_cost ?? 0;
                $advance = $expectedCost * 0.10;

                $appointment = Appointment::create([
                    'resident_id' => $application->resident_id,
                    'employer_id' => $application->user_id,
                    'society_id' => $resident->society->id,

                    'total_amount' => $expectedCost,
                    'advance_percentage' => 10,
                    'advance_amount' => $advance,
                    'final_amount' => $expectedCost - $advance,

                    'status' => 'pending',
                    'job_application_id' => $application->id,

                    'entry_token' => \Str::uuid(),
                    'entry_token_used' => 0,
                ]);

                $application->appointment_id = $appointment->id;
                $application->save();
            }

            return response()->json([
                'status' => true,
                'message' => 'Application status updated successfully.',
                'application' => $application,
            ]);
        }

        /**
         * ❌ Nothing to update
         */
        return response()->json([
            'status' => false,
            'message' => 'No valid update parameter provided.',
        ], 422);
    }





    public function filterJobsByStatus($employer_id, $status)
    {
        if (!in_array($status, ['active', 'upcoming', 'completed'])) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid status type.',
            ], 422);
        }

        $statusMap = [
            'active' => ['hired'],
            'upcoming' => ['applied'],
            'completed' => ['completed'],
        ];

        $applications = JobApplication::where('user_id', $employer_id)
            ->whereIn('status', $statusMap[$status])
            ->with(['jobPost', 'user', 'appointment'])
            ->latest()
            ->get()
            ->map(function ($application) {

                $appointment = $application->appointment;

                // ---------- DEFAULTS ----------
                $jobFlowState = 'none';
                $appointmentExists = (bool) $appointment;

                $canGuardEntry = false;
                $canStartJob = false;
                $canEndJob = false;
                $canResidentConfirm = false;
                $canExitJob = false;
                $isCompleted = false;
                $exitToken = null;



                // ---------- FLOW LOGIC ----------
                if (
                    in_array($application->status, ['hired', 'completed']) &&
                    $appointment
                ) {

                    if ($appointment->advance_paid == 0) {
                        $jobFlowState = 'advance_pending';
                        $canGuardEntry = true;
                    } elseif (!$appointment->entry_token_used) {
                        $jobFlowState = 'guard_entry_pending';
                        $canGuardEntry = true;
                    } elseif ($appointment->entry_token_used && is_null($appointment->job_start_time)) {
                        $jobFlowState = 'job_start_pending';
                        $canStartJob = true;
                    } elseif ($appointment->job_start_time && is_null($appointment->job_end_time)) {
                        $jobFlowState = 'job_in_progress';
                        $canEndJob = true;
                    } elseif ($appointment->resident_confirmation === 'pending') {
                        $jobFlowState = 'resident_confirmation_pending';
                        $canResidentConfirm = true;
                    } elseif (!$appointment->exit_token_used) {
                        $jobFlowState = 'exit_pending';
                        $canExitJob = true;
                        $exitToken = $appointment->exit_token; // show token only if NOT used
                    } elseif ($appointment->exit_token_used) {
                        $jobFlowState = 'completed';
                        $isCompleted = true;
                    }
                }

                return [
                    // 🔹 APPLICATION DATA
                    'application_id' => $application->id,
                    'job_id' => $application->job_id,
                    'status' => $application->status,

                    // 🔹 JOB POST DATA (FULL OBJECT)
                    'job_post' => $application->jobPost,

                    // 🔹 FLOW STATE
                    'job_flow_state' => $jobFlowState,
                    'appointment_exists' => $appointmentExists,
                    'exit_token' => $exitToken,

                    // 🔹 ACTION FLAGS
                    'can_guard_entry' => $canGuardEntry,
                    'can_start_job' => $canStartJob,
                    'can_end_job' => $canEndJob,
                    'can_resident_confirm' => $canResidentConfirm,
                    'can_exit_job' => $canExitJob,
                    'is_completed' => $isCompleted,

                    // 🔹 OPTIONAL RAW FIELDS
                    'job_start_time' => $appointment?->job_start_time,
                    'job_end_time' => $appointment?->job_end_time,
                ];
            });

        return response()->json([
            'status' => true,
            'type' => $status,
            'total' => $applications->count(),
            'data' => $applications,
        ]);
    }







}
