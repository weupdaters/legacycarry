<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class JobPaymentController extends Controller
{
    public function pay(Request $request, $job_id, $application_id)
    {
        // -----------------------------
        // VALIDATE JOB & APPLICATION
        // -----------------------------
        $job = JobPost::find($job_id);
        if (!$job) {
            return response()->json(['status' => false,'message' => 'Job not found'], 404);
        }

        $application = JobApplication::find($application_id);
        if (!$application) {
            return response()->json(['status' => false,'message' => 'Application not found'], 404);
        }

        if ($application->job_id !== $job->id) {
            return response()->json([
                'status' => false,
                'message' => 'Application does not belong to this job',
            ], 400);
        }

        if ($application->status !== 'hired') {
            return response()->json([
                'status' => false,
                'message' => 'Only hired applications can be paid',
            ], 400);
        }

        // -----------------------------
        // REQUEST VALIDATION
        // -----------------------------
        $data = $request->validate([
            'payment_type' => ['required',Rule::in(['advance','final'])],
            'amount' => 'required|numeric|min:1',
            'payment_method' => ['required',Rule::in(['UPI','card','wallet','netbanking'])],
            'notes' => 'nullable|string|max:500',
        ]);

        // -----------------------------
        // PREVENT DUPLICATE SUCCESS
        // -----------------------------
        $alreadyPaid = Payment::where('job_application_id', $application->id)
            ->where('job_post_id', $job->id)
            ->where('payment_type', $data['payment_type'])
            ->where('status', 'success')
            ->first();

        if ($alreadyPaid) {
            return response()->json([
                'status' => false,
                'message' => ucfirst($data['payment_type']).' payment already completed.',
                'payment' => $alreadyPaid,
            ], 200);
        }

        // -----------------------------
        // FINAL NEEDS ADVANCE FIRST
        // -----------------------------
        if ($data['payment_type'] === 'final') {

            $advancePaid = Payment::where('job_application_id', $application->id)
                ->where('payment_type', 'advance')
                ->where('status', 'success')
                ->exists();

            if (!$advancePaid) {
                return response()->json([
                    'status' => false,
                    'message' => 'Advance payment must be completed before final payment.',
                ], 200);
            }
        }

        // -----------------------------
        // GET CASHFREE CONFIG FROM ADMIN BACKEND
        // -----------------------------
        $cfConfig = Setting::getCashfreeConfig();

        if (empty($cfConfig['app_id']) || empty($cfConfig['secret_key'])) {
            return response()->json([
                'status' => false,
                'message' => 'Cashfree API credentials are not configured in Admin Settings.',
            ], 500);
        }

        // -----------------------------
        // START TRANSACTION
        // -----------------------------
        DB::beginTransaction();

        try {

            // UNIQUE ORDER ID
            $orderId = 'JOB'.$job->id.'APP'.$application->id.Str::upper(Str::random(6));

            $custPhone = !empty($application->user?->phone) ? preg_replace('/[^0-9]/', '', $application->user->phone) : '9999999999';
            if (strlen($custPhone) < 10) {
                $custPhone = '9999999999';
            }

            $returnUrl = url('/cashfree-return?order_id={order_id}');
            if ($cfConfig['is_live'] && str_starts_with($returnUrl, 'http://')) {
                $returnUrl = preg_replace('/^http:/i', 'https:', $returnUrl);
            }

            // -----------------------------
            // CREATE CASHFREE ORDER
            // -----------------------------
            $response = Http::withHeaders([
                'x-client-id' => $cfConfig['app_id'],
                'x-client-secret' => $cfConfig['secret_key'],
                'x-api-version' => '2022-09-01',
                'Content-Type' => 'application/json',
            ])->post($cfConfig['base_url'], [

                'order_id' => $orderId,
                'order_amount' => (float)$data['amount'],
                'order_currency' => 'INR',

                'customer_details' => [
                    'customer_id' => (string)$application->user_id,
                    'customer_name' => $application->user->name ?? 'User',
                    'customer_email' => $application->user->email ?? 'user@email.com',
                    'customer_phone' => $custPhone,
                ],

                'order_meta' => [
                    'return_url' => $returnUrl,
                ],

            ]);

            if (!$response->successful()) {
                DB::rollBack();
                \Log::error('Cashfree order creation failed: ' . $response->body());
                return response()->json([
                    'status' => false,
                    'message' => 'Cashfree order creation failed',
                    'error' => $response->json(),
                ], 500);
            }

            $cf = $response->json();
            $paymentSessionId = $cf['payment_session_id'] ?? null;
            $paymentLink = $cf['payment_link'] ?? ($paymentSessionId ? $cfConfig['checkout_url'] . '/' . $paymentSessionId : null);

            // -----------------------------
            // SAVE PAYMENT (PENDING)
            // -----------------------------
            $payment = Payment::create([
                'payment_id' => $orderId,
                'job_post_id' => $job->id,
                'job_application_id' => $application->id,
                'payer_id' => $job->user_id,
                'payee_id' => $application->user_id,
                'amount' => $data['amount'],
                'currency' => 'INR',
                'payment_method' => $data['payment_method'],
                'payment_reference' => $cf['cf_order_id'] ?? null,
                'payment_type' => $data['payment_type'],
                'target_account' => $data['payment_type'] === 'advance' ? 'admin' : 'employer',
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
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

            // -----------------------------
            // SEND DATA TO FLUTTER / CLIENT APP
            // -----------------------------
            return response()->json([
                'status' => true,
                'message' => 'Cashfree order created successfully',
                'payment_id' => $payment->id,
                'order_id' => $orderId,
                'payment_link' => $paymentLink,
                'payment_url' => $paymentLink,
                'payment_session_id' => $paymentSessionId,
                'environment' => $cfConfig['environment'],
                'latest_payment' => $payment,
                'payment_summary' => $summary,
                'payments' => $payments,
                // REQUIRED FOR FLUTTER SDK / WEB
                'cashfree' => [
                    'order_id' => $cf['order_id'] ?? $orderId,
                    'cf_order_id' => $cf['cf_order_id'] ?? null,
                    'payment_session_id' => $paymentSessionId,
                    'payment_link' => $paymentLink,
                    'payment_url' => $paymentLink,
                    'environment' => $cfConfig['is_live'] ? 'PRODUCTION' : 'SANDBOX',
                ],
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            \Log::error('Cashfree Payment Error: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Payment initialization failed',
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
            'total_amount' => $payments->sum('amount'),
        ], 200);
    }

    public function getMyPayments(Request $request)
    {
        $user = $request->user();

        $query = Payment::with(['job', 'payer'])
                          ->where('payee_id', $user->id)
                          ->where('payment_type', 'final');

        $payments = $query->orderBy('created_at', 'desc')
                          ->paginate(10);

        $totalFinalEarned = Payment::where('payee_id', $user->id)
                                     ->where('payment_type', 'final')
                                     ->where('status', 'success')
                                     ->sum('amount');

        $totalWithdrawn = Withdrawal::where('user_id', $user->id)
                                     ->where('status', 'completed')
                                     ->sum('amount');

        $pendingWithdrawal = Withdrawal::where('user_id', $user->id)
                                     ->whereIn('status', ['pending', 'processing'])
                                     ->sum('amount');

        $availableBalance = max(0, round($totalFinalEarned - ($totalWithdrawn + $pendingWithdrawal), 2));

        return response()->json([
            'status' => true,
            'payments' => $payments,
            'total_earnings' => $totalFinalEarned,
            'available_balance' => $availableBalance,
        ], 200);
    }
}
