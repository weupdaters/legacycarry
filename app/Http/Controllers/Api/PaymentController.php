<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    // -----------------------------
    // ADVANCE PAYMENT
    // -----------------------------
    public function payAdvance(Request $req, Appointment $appointment)
    {
        $req->validate([
            'user_id' => 'required|exists:users,id',
            'payment_reference' => 'required|string',
        ]);

        if ($req->user_id != $appointment->resident_id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($req, $appointment) {
            // Lock the appointment for update to prevent concurrent double-payments
            $appointment = \App\Models\Appointment::where('id', $appointment->id)->lockForUpdate()->first();

            // ❌ Advance already paid for this appointment
            if ($appointment->advance_paid) {
            return response()->json([
                'status' => false,
                'message' => 'Advance already paid',
            ], 422);
        }



        // 🔍 Find payment reference
        $payment = Payment::where('payment_id', $req->payment_reference)->first();

        if (!$payment) {
            return response()->json([
                'status' => false,
                'message' => 'Payment reference not found',
            ], 404);
        }

        // ✅ VERIFY FROM CASHFREE
        if (!$this->verifyCashfreePayment($payment->payment_id)) {
            return response()->json([
                'status' => false,
                'message' => 'Payment not completed or failed',
            ], 422);
        }


        // ❌ Payment already used
        if ($payment->payment_type != 'advance' && $payment->payment_type != null) {
            return response()->json([
                'status' => false,
                'message' => 'Payment reference already used',
            ], 422);
        }

        // ❌ Payment used for another appointment
        if ($payment->appointment_id && $payment->appointment_id !== $appointment->id) {
            return response()->json([
                'status' => false,
                'message' => 'Payment reference already linked to another appointment',
            ], 422);
        }

        // ✅ Mark advance paid
        $appointment->advance_paid = 1;
        $appointment->save();

        $payment->update([
            'appointment_id' => $appointment->id,
            'target_account' => 'admin',
        ]);

        \App\Helpers\Notify::to($appointment->employer_id, 'Advance Payment Received', 'Resident has paid the advance payment.', ['appointment_id' => $appointment->id]);

            return response()->json([
                'status' => true,
                'message' => 'Advance payment recorded successfully',
            ]);
        });
    }




    // -----------------------------
    // FINAL PAYMENT
    // -----------------------------
    public function payFinal(Request $req, Appointment $appointment)
    {
        $req->validate([
            'user_id' => 'required|exists:users,id',
            'payment_reference' => 'required|string',
        ]);

        if ($req->user_id != $appointment->resident_id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($req, $appointment) {
            // Lock the appointment for update
            $appointment = \App\Models\Appointment::where('id', $appointment->id)->lockForUpdate()->first();

            // ❌ Final already paid
            if ($appointment->final_paid) {
            return response()->json([
                'status' => false,
                'message' => 'Final payment already paid',
            ], 422);
        }

        // 🔍 Find payment
        $payment = Payment::where('payment_id', $req->payment_reference)->first();

        if (!$payment) {
            return response()->json([
                'status' => false,
                'message' => 'Payment reference not found',
            ], 404);
        }

        $verifiedPayment = $this->verifyCashfreePayment($payment->payment_id);

        if (!$verifiedPayment) {
            return response()->json([
                'status' => false,
                'message' => 'Payment not completed or failed',
            ], 422);
        }

        // ❌ Payment already used
        if ($payment->payment_type != 'final' && $payment->payment_type != null) {
            return response()->json([
                'status' => false,
                'message' => 'Payment reference already used',
            ], 422);
        }

        // ❌ Payment used for another appointment
        if ($payment->appointment_id && $payment->appointment_id !== $appointment->id) {
            return response()->json([
                'status' => false,
                'message' => 'Payment reference already linked to another appointment',
            ], 422);
        }

        // ✅ Mark final paid
        $appointment->final_paid = 1;
        $appointment->save();

        $payment->update([
            'appointment_id' => $appointment->id,
            'target_account' => 'employer',
        ]);

        \App\Helpers\Notify::to($appointment->employer_id, 'Final Payment Received', 'Resident has paid the final payment.', ['appointment_id' => $appointment->id]);

            return response()->json([
                'status' => true,
                'message' => 'Final payment recorded successfully',
            ]);
        });
    }



    private function verifyCashfreePayment($orderId)
    {
        try {

            // find local payment first
            $payment = Payment::where('payment_id', $orderId)->first();

            if (!$payment) {
                return false;
            }

            $cfConfig = Setting::getCashfreeConfig();

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'x-client-id' => $cfConfig['app_id'],
                'x-client-secret' => $cfConfig['secret_key'],
                'x-api-version' => '2022-09-01',
            ])->get($cfConfig['base_url'] . '/' . $orderId . '/payments');

            if (!$response->successful()) {
                return false;
            }

            $payments = $response->json();

            if (!is_array($payments)) {
                return false;
            }

            foreach ($payments as $p) {

                if (
                    isset($p['payment_status']) &&
                    strtoupper($p['payment_status']) === 'SUCCESS'
                ) {

                    // ✅ UPDATE DATABASE ONLY IF NOT ALREADY SUCCESS
                    if ($payment->status !== 'success') {

                        $payment->update([
                            'status' => 'success',
                            'paid_at' => now(),
                            'payment_reference' => $p['cf_payment_id'] ?? $payment->payment_reference,
                            'payment_method' => $p['payment_method'] ?? $payment->payment_method,
                        ]);
                    }

                    return $payment; // return updated model
                }
            }

            return false;

        } catch (\Exception $e) {

            \Log::error('Cashfree verify failed: ' . $e->getMessage());
            return false;
        }
    }

    public function laborEarnings(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'payment_type' => 'nullable|in:advance,final',
            'date' => 'nullable|date',
        ]);

        $user = User::find($request->user_id);

        // ❌ Not a labor or service provider
        if ($user->role !== 'labor' && $user->role !== 'service_provider') {
            return response()->json([
                'status' => false,
                'message' => 'User is not labor or service provider',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | SUMMARY (Top Card)
        |--------------------------------------------------------------------------
        */

        // Total Final Earned
        $totalFinalEarned = (float) Payment::where('payee_id', $user->id)
            ->where('payment_type', 'final')
            ->where('status', 'success')
            ->sum('amount');

        // Total already Withdrawn (Completed)
        $totalWithdrawn = (float) Withdrawal::where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('amount');

        // Total pending / in-process withdrawal requests
        $pendingWithdrawal = (float) Withdrawal::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'processing'])
            ->sum('amount');

        // Available balance = Final Earned - (Completed Withdrawn + Pending Withdrawals)
        $availableBalance = max(0, round($totalFinalEarned - ($totalWithdrawn + $pendingWithdrawal), 2));

        // ADVANCE = pending payout held in admin escrow until job completion
        $pendingPayout = (float) Payment::where('payee_id', $user->id)
            ->where('payment_type', 'advance')
            ->where('status', 'success')
            ->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | TRANSACTIONS LIST (Payment History)
        |--------------------------------------------------------------------------
        */

        $query = Payment::where('payee_id', $user->id)
            ->where('status', 'success');

        // 🔽 Filter by type: Default to 'final' so history shows final payments
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        } else {
            $query->where('payment_type', 'final');
        }

        // 📅 Filter by date
        if ($request->filled('date')) {
            $query->whereDate('paid_at', $request->date);
        }

        $transactions = $query
            ->orderByDesc('paid_at')
            ->with('payer:id,name')
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'title' => 'Payment from ' . ($payment->payer->name ?? 'Client'),
                    'amount' => '+ ₹' . number_format($payment->amount, 2),
                    'raw_amount' => $payment->amount,
                    'payment_type' => $payment->payment_type,
                    'date' => $payment->paid_at ? $payment->paid_at->format('d M Y') : $payment->created_at->format('d M Y'),
                    'paid_at' => $payment->paid_at,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | FINAL RESPONSE
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'status' => true,
            'user_id' => $user->id,
            'summary' => [
                'current_balance' => $availableBalance,
                'available_balance' => $availableBalance,
                'total_final_earned' => $totalFinalEarned,
                'total_withdrawn' => $totalWithdrawn,
                'pending_withdrawal' => $pendingWithdrawal,
                'pending_payout' => $pendingPayout,
            ],
            'transactions' => $transactions,
        ]);
    }


    public function laborAppointmentPayments(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'appointment_id' => 'required|exists:appointments,id',
        ]);

        $user = User::find($request->user_id);

        if ($user->role !== 'labor') {
            return response()->json([
                'status' => false,
                'message' => 'User is not labor',
            ], 403);
        }

        $appointment = Appointment::find($request->appointment_id);

        // ensure appointment belongs to labor
        if ($appointment->labor_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Access denied',
            ], 403);
        }

        $payments = Payment::where('appointment_id', $appointment->id)
            ->where('status', 'success')
            ->get();

        return response()->json([
            'status' => true,
            'appointment_id' => $appointment->id,
            'advance_paid' => $appointment->advance_paid,
            'final_paid' => $appointment->final_paid,
            'payments' => $payments,
        ]);
    }

    /**
 * Resident Payment History & Summary
 * payer_id = resident
 */
    public function residentPayments(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'payment_type' => 'nullable|in:advance,final',
            'date' => 'nullable|date',
        ]);

        $resident = User::find($request->user_id);

        // ❌ Only resident allowed
        if ($resident->role !== 'customer') {
            return response()->json([
                'status' => false,
                'message' => 'User is not a resident',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | SUMMARY
        |--------------------------------------------------------------------------
        */

        $totalPaid = Payment::where('payer_id', $resident->id)
            ->where('status', 'success')
            ->sum('amount');

        $advancePaid = Payment::where('payer_id', $resident->id)
            ->where('payment_type', 'advance')
            ->where('status', 'success')
            ->sum('amount');

        $finalPaid = Payment::where('payer_id', $resident->id)
            ->where('payment_type', 'final')
            ->where('status', 'success')
            ->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | PAYMENT HISTORY
        |--------------------------------------------------------------------------
        */

        $query = Payment::where('payer_id', $resident->id)
            ->where('status', 'success')
            ->with([
                'payee:id,name',           // appointment
            ]);

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->filled('date')) {
            $query->whereDate('paid_at', $request->date);
        }

        $payments = $query
            ->orderByDesc('paid_at')
            ->get()
            ->map(function ($payment) {
                return [
                    'payment_id' => $payment->id,
                    'labor_name' => optional($payment->payee)->name ?? 'Labor',
                    'payment_type' => $payment->payment_type,
                    'amount' => '₹' . number_format($payment->amount, 0),
                    'raw_amount' => $payment->amount,
                    'paid_at' => optional($payment->paid_at)->format('d M Y, h:i A'),
                ];
            });

        return response()->json([
            'status' => true,
            'resident_id' => $resident->id,
            'summary' => [
                'total_paid' => $totalPaid,
                'advance_paid' => $advancePaid,
                'final_paid' => $finalPaid,
            ],
            'payments' => $payments,
        ]);
    }


}
