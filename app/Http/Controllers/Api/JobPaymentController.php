<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobPost;
use App\Models\JobApplication;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class JobPaymentController extends Controller
{
    
    public function pay(Request $request, $job_id, $application_id)
    {
        // -----------------------------
        // VALIDATE JOB & APPLICATION
        // -----------------------------
        $job = JobPost::find($job_id);
        if (!$job) {
            return response()->json(['status' => false, 'message' => 'Job not found'], 404);
        }
    
        $application = JobApplication::find($application_id);
        if (!$application) {
            return response()->json(['status' => false, 'message' => 'Application not found'], 404);
        }
    
        if ($application->job_id !== $job->id) {
            return response()->json([
                'status' => false,
                'message' => 'Application does not belong to this job'
            ], 400);
        }
    
        if ($application->status !== 'hired') {
            return response()->json([
                'status' => false,
                'message' => 'Only hired applications can be paid'
            ], 400);
        }
    
        // -----------------------------
        // REQUEST VALIDATION
        // -----------------------------
        $data = $request->validate([
            'payment_type' => ['required', Rule::in(['advance', 'final'])],
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => ['required', Rule::in(['UPI','card','cash','wallet','netbanking'])],
            'notes' => 'nullable|string|max:500',
        ]);
        
        $payment = Payment::where('job_application_id', $application->id)->where('job_post_id', $job->id)->where('payment_type', $data['payment_type'])->first();
        if ($payment && $payment->status === 'success') {
            return response()->json([
                'status' => false,
                'message' => ucfirst($data['payment_type']) . ' payment already completed.',
                'payment' => $payment
            ], 200);
        }

    
    
        // -----------------------------
        // FINAL REQUIRES ADVANCE
        // -----------------------------
        if ($data['payment_type'] === 'final') {
            $advancePaid = Payment::where('job_application_id', $application->id)
                ->where('payment_type', 'advance')
                ->where('status', 'success')
                ->exists();
    
            if (!$advancePaid) {
                return response()->json([
                    'status' => false,
                    'message' => 'Advance payment must be completed before final payment.'
                ],200);
            }
        }
        
        
   
        // -----------------------------
        // TRANSACTION START
        // -----------------------------
        DB::beginTransaction();
    
        try {
    
            // CREATE PAYMENT
            $payment = Payment::create([
                'payment_id' => $this->generatePaymentId(),
                'job_post_id' => $job->id,
                'job_application_id' => $application->id,
                'payer_id' => $job->user_id,
                'payee_id' => $application->user_id,
                'amount' => $data['amount'],
                'currency' => 'INR',
                'payment_method' => $data['payment_method'],
                'payment_reference' => $this->generatePaymentId(),
                'payment_type' => $data['payment_type'],
                'target_account' => $data['payment_type'] === 'advance' ? 'admin' : 'employer',
                'notes' => $data['notes'] ?? null,
                'status' => 'success',
                'paid_at' => now(),
            ]);
    
            // FETCH ALL PAYMENTS (ADVANCE + FINAL)
            $payments = Payment::where('job_application_id', $application->id)
                ->orderBy('paid_at')
                ->get();
    
            // SUMMARY
            $summary = [
                'advance_paid' => $payments->where('payment_type', 'advance')->sum('amount'),
                'final_paid' => $payments->where('payment_type', 'final')->sum('amount'),
                'total_paid' => $payments->sum('amount'),
            ];
    
            DB::commit();
    
            return response()->json([
                'status' => true,
                'message' => ucfirst($data['payment_type']) . ' payment recorded successfully.',
                'latest_payment' => $payment,
                'payment_summary' => $summary,
                'payments' => $payments
            ], 200);
    
        } catch (QueryException $e) {
    
            DB::rollBack();
    
            if ($e->errorInfo[1] == 1062) {
                return response()->json([
                    'status' => false,
                    'message' => 'This payment reference has already been used.'
                ], 422);
            }
    
            \Log::error('Payment error: ' . $e->getMessage());
    
            return response()->json([
                'status' => false,
                'message' => 'Payment failed. Try again.'
            ], 500);
        }
    }
    
    

    private function generatePaymentId(): string
    {
        $timestamp = now()->format('YmdHis');
        $random = Str::upper(Str::random(6));
        $paymentId = "PAY_{$timestamp}_{$random}";

        while (Payment::where('payment_id', $paymentId)->exists()) {
            $random = Str::upper(Str::random(6));
            $paymentId = "PAY_{$timestamp}_{$random}";
        }

        return $paymentId;
    }

    public function getPaymentDetails(Request $request, string $paymentId)
    {
        $payment = Payment::where('payment_id', $paymentId)
                         ->first();

        if (!$payment) {
            return response()->json(['status' => false, 'message' => 'Payment not found.'], 200);
        }

        // $user = $request->user();
        // if (!in_array($user->id, [$payment->payer_id, $payment->payee_id])) {
        //     return response()->json(['status' => false, 'message' => 'Unauthorized to view this payment.'], 200);
        // }

        return response()->json(['status' => true, 'payment' => $payment], 200);
    }

    public function getJobPayments(Request $request, JobPost $job)
    {
        $user = $request->user();

        if ($job->user_id !== $user->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized to view payments for this job.'], 200);
        }

        $payments = Payment::with(['application', 'payee'])
                          ->where('job_post_id', $job->id)
                          ->orderBy('created_at', 'desc')
                          ->get();

        return response()->json([
            'status' => true,
            'payments' => $payments,
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount')
        ], 200);
    }

    public function getMyPayments(Request $request)
    {
        $user = $request->user();

        $payments = Payment::with(['job', 'payer'])
                          ->where('payee_id', $user->id)
                          ->orderBy('created_at', 'desc')
                          ->paginate(10);

        return response()->json([
            'status' => true,
            'payments' => $payments,
            'total_earnings' => Payment::where('payee_id', $user->id)
                                     ->where('status', 'success')
                                     ->sum('amount')
        ], 200);
    }
}