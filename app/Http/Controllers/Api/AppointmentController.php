<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Employee;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\Payment;
use App\Models\Society;
use App\Models\User; // 🔔 Add this
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AppointmentController extends Controller
{
    // Approve Appointment & Generate Entry Token
    public function approve(Request $req, Appointment $appointment)
    {


        if ($appointment->society_id != 0) {
            // ❌ Guard already scanned → NEVER regenerate
            if ($appointment->entry_token && $appointment->entry_token_used == 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Entry already scanned by guard. New entry pass not allowed.',
                ], 422);
            }

            // ❌ Token already generated but not yet scanned → reuse
            if ($appointment->entry_token && $appointment->entry_token_used == 0) {
                return response()->json([
                    'status' => true,
                    'entry_token' => $appointment->entry_token,
                    'message' => 'Entry token already active.',
                ]);
            }


            // ❌ Employer already inside society via OTHER appointment
            $alreadyInside = Appointment::where('employer_id', $appointment->employer_id)
                ->where('society_id', $appointment->society_id)
                ->where('entry_token_used', 0)
                ->whereNotNull('entry_token')
                ->where('id', '!=', $appointment->id)
                ->exists();

            if ($alreadyInside) {
                return response()->json([
                    'status' => false,
                    'message' => 'Employer already has an active entry inside society.',
                ], 422);
            }

            // ✅ Generate token ONLY ONCE
            $appointment->entry_token = (string) Str::uuid();
            $appointment->entry_token_used = 0;

            // 🔔 Notify employer
            PushNotificationService::sendToUser(
                $appointment->employer_id,
                'New Job Approved',
                'Entry pass generated. Show QR at gate.',
                [
                    'appointment_id' => $appointment->id,
                    'entry_token' => $appointment->entry_token,
                ]
            );

        }

        // 💰 Payment
        $appointment->advance_amount =
            $appointment->total_amount * ($appointment->advance_percentage / 100);

        $appointment->final_amount =
            $appointment->total_amount - $appointment->advance_amount;

        $appointment->status = 'approved';
        $appointment->save();



        return response()->json([
            'status' => true,
            'entry_token' => $appointment->entry_token ?? null,
            'advance_amount' => $appointment->advance_amount,
            'final_amount' => $appointment->final_amount,
        ]);
    }




    // Guard Entry Scan
    // public function guardEntry(Request $req)
    // {
    //     $token = $req->token;

    //     $appt = Appointment::where('entry_token', $token)->first();
    //     if (!$appt) return response()->json(['error' => 'Invalid token'], 404);

    //     if ($appt->entry_token_used) return response()->json(['error' => 'Token already used']);

    //     if ($appt->advance_amount > 0 && !$appt->advance_paid)
    //         return response()->json(['error'=>'Advance pending'],402);

    //     $appt->entry_token_used = 1;
    //     $appt->guard_in_time = now();
    //     $appt->status = 'employee_in_society';
    //     $appt->save();

    //     // 🔔 Notify resident
    //     PushNotificationService::sendToUser(
    //         $appt->resident_id,
    //         "Worker Entered Society",
    //         "Your worker has passed the security gate.",
    //         ["appointment_id" => $appt->id]
    //     );

    //     return response()->json(['success'=>'Entry verified']);
    // }

    public function secureAppointmentEntry(Request $req)
    {
        // ---- VALIDATION ----
        $req->validate([
            'token' => 'required|string',
            'guard_id' => 'required|exists:users,id',
        ]);

        $token = $req->token;

        // ---- FETCH APPOINTMENT ----
        $appt = Appointment::where('entry_token', $token)->first();
        if (!$appt) {
            return response()->json(['status' => false, 'message' => 'Invalid token'], 404);
        }

        $guard = User::find($req->guard_id);

        // ---- SOCIETY MATCH CHECK (strong security) ----
        // if ($guard->society_id != $appt->society_id) {
        //     return response()->json(['status' => false, 'message' => 'Not your society'], 403);
        // }

        // ---- CHECK IF TOKEN USED ----
        if ($appt->entry_token_used) {
            return response()->json(['status' => false, 'message' => 'Token already used'], 400);
        }

        // ---- ADVANCE PAYMENT CHECK ----
        if ($appt->advance_amount > 0 && !$appt->advance_paid) {
            return response()->json(['status' => false, 'message' => 'Advance pending'], 402);
        }

        // ---- UPDATE ENTRY DETAILS ----
        $appt->entry_token_used = 1;
        $appt->guard_in_time = now();
        $appt->status = 'employee_in_society';
        $appt->guard_in_id = $guard->id ?? null; // only if column exists
        $appt->save();

        // ---- SEND PUSH NOTIFICATION ----
        try {
            PushNotificationService::sendToUser(
                $appt->resident_id,
                'Worker Entered Society',
                'Your worker has passed the security gate.',
                ['appointment_id' => $appt->id]
            );
        } catch (\Throwable $e) {
            \Log::warning('Push notification failed: ' . $e->getMessage());
        }

        // ---- AUDIT LOG ----
        try {
            Audit::log($guard->id, 'appointment_entry', 'appointments', $appt->id, null, [
                'entry_time' => $appt->guard_in_time,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Audit log failed: ' . $e->getMessage());
        }

        return response()->json([
            'status' => true,
            'message' => 'Appointment entry verified',
            'appointment' => $appt,
        ]);
    }

    public function secureAppointmentExit(Request $req)
    {
        // ---- VALIDATION ----
        $req->validate([
            'token' => 'required|string',
            'guard_id' => 'required|exists:users,id',
        ]);

        // ---- FETCH APPOINTMENT ----
        $appt = Appointment::where('exit_token', $req->token)->first();
        if (!$appt) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid exit token',
            ], 404);
        }

        $guard = User::find($req->guard_id);

        // ---- SOCIETY SECURITY CHECK ----
        // (Enable when society logic is final)
        // if ($guard->society_id != $appt->society_id) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Not your society'
        //     ], 403);
        // }

        // ---- ENTRY CHECK ----
        if (!$appt->entry_token_used || !$appt->guard_in_time) {
            return response()->json([
                'status' => false,
                'message' => 'Worker has not entered yet',
            ], 400);
        }

        // ---- FINAL PAYMENT CHECK ----
        if (!$appt->final_paid) {
            return response()->json([
                'status' => false,
                'message' => 'Final payment pending',
            ], 402);
        }

        // ---- DOUBLE EXIT CHECK ----
        if ($appt->exit_token_used) {
            return response()->json([
                'status' => false,
                'message' => 'Worker already exited',
            ], 400);
        }

        // ---- UPDATE EXIT DETAILS ----
        $appt->exit_token_used = 1;
        $appt->guard_out_time = now();
        $appt->guard_out_id = $guard->id ?? null; // only if column exists
        $appt->status = 'closed';
        $appt->save();

        // ---- PUSH NOTIFICATION (SAFE) ----
        try {
            if ($appt->resident_id && User::where('id', $appt->resident_id)->exists()) {
                PushNotificationService::sendToUser(
                    $appt->resident_id,
                    'Worker Left',
                    'Worker exited the society.',
                    ['appointment_id' => $appt->id]
                );
            }
        } catch (\Throwable $e) {
            \Log::warning('Exit notification failed: '.$e->getMessage());
        }

        // ---- AUDIT LOG (SAFE) ----
        try {
            Audit::log(
                $guard->id,
                'appointment_exit',
                'appointments',
                $appt->id,
                null,
                ['exit_time' => $appt->guard_out_time]
            );
        } catch (\Throwable $e) {
            \Log::warning('Exit audit failed: '.$e->getMessage());
        }

        return response()->json([
            'status' => true,
            'message' => 'Appointment exit verified',
            'appointment' => $appt,
        ]);
    }


    // Resident Start Job
    public function start(Request $req, Appointment $appointment)
    {
        $appointment->job_start_time = now();
        $appointment->status = 'in_progress';
        $appointment->save();

        // 🔔 Notify employer
        PushNotificationService::sendToUser(
            $appointment->employer_id,
            'Job Started',
            'Resident has started your job timer.',
            ['appointment_id' => $appointment->id]
        );

        return response()->json(['message' => 'Job started']);
    }

    // Resident End Job
    public function end(Request $req, Appointment $appointment)
    {
        $appointment->job_end_time = now();
        $appointment->status = 'waiting_resident_confirmation';
        $appointment->save();

        // 🔔 Notify employer
        PushNotificationService::sendToUser(
            $appointment->employer_id,
            'Job Completed',
            'Resident ended the job. Please wait for approval.',
            ['appointment_id' => $appointment->id]
        );

        return response()->json([
            'message' => 'Job ended',
            'total_minutes' => $appointment->totalMinutes(),
        ]);
    }

    // Resident Approve Job + Generate Exit Token
    public function confirm(Request $req, Appointment $appointment)
    {
        // ❌ Final payment not done
        if (!$appointment->final_paid) {
            return response()->json([
                'status' => false,
                'message' => 'Final payment pending',
            ], 402);
        }

        // ❌ Exit already completed
        if ($appointment->exit_token_used) {
            return response()->json([
                'status' => false,
                'message' => 'Worker already exited',
            ], 400);
        }

        // ✅ Exit token already generated → DO NOT regenerate
        if ($appointment->exit_token) {
            return response()->json([
                'status' => true,
                'message' => 'Exit token already generated',
                'exit_token' => $appointment->exit_token,
            ]);
        }

        // ✅ Generate exit token ONCE
        $appointment->exit_token = Str::uuid()->toString();
        $appointment->exit_token_used = 0;
        $appointment->status = 'completed';
        $appointment->resident_confirmation = 'approved';
        $appointment->save();


        //job application status update to completed
        $jobApplication = JobApplication::find($appointment->job_application_id);
        if ($jobApplication) {
            $jobApplication->status = 'completed';
            $jobApplication->completed_at = now();
            $jobApplication->save();
        }


        //job post status update to completed
        $jobPost = JobPost::find($jobApplication->job_id);
        if ($jobPost) {
            $jobPost->status = 'completed';
            $jobPost->completed_at = now();
            $jobPost->save();
        }

        // 🔔 Notify employer (SAFE)
        try {
            PushNotificationService::sendToUser(
                $appointment->employer_id,
                'Exit Pass Ready',
                'Your exit token has been generated.',
                ['exit_token' => $appointment->exit_token]
            );
        } catch (\Throwable $e) {
            \Log::warning('Exit token push failed: '.$e->getMessage());
        }

        return response()->json([
            'status' => true,
            'exit_token' => $appointment->exit_token,
        ]);
    }
}
