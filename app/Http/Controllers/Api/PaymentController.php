<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\User;

class PaymentController extends Controller
{
    // -----------------------------
    // ADVANCE PAYMENT
    // -----------------------------
    public function payAdvance(Request $req, Appointment $appointment)
    {
        $req->validate([
            'user_id' => 'required|exists:users,id',
            'payment_reference' => 'required|string'
        ]);

        // ❌ Advance already paid for this appointment
        if ($appointment->advance_paid) {
            return response()->json([
                'status' => false,
                'message' => 'Advance already paid'
            ], 422);
        }

        // 🔍 Find payment reference
        $payment = Payment::where('payment_id', $req->payment_reference)->first();

        if (!$payment) {
            return response()->json([
                'status' => false,
                'message' => 'Payment reference not found'
            ], 404);
        }

        // ❌ Payment already used
        if ($payment->payment_type != 'advance' && $payment->payment_type != null) {
            return response()->json([
                'status' => false,
                'message' => 'Payment reference already used'
            ], 422);
        }

        // ❌ Payment used for another appointment
        if ($payment->appointment_id && $payment->appointment_id !== $appointment->id) {
            return response()->json([
                'status' => false,
                'message' => 'Payment reference already linked to another appointment'
            ], 422);
        }

        // ✅ Mark advance paid
        $appointment->advance_paid = 1;
        $appointment->save();

        $payment->update([
            'appointment_id'     => $appointment->id,
            'target_account'     => 'admin'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Advance payment recorded successfully'
        ]);
    }




    // -----------------------------
    // FINAL PAYMENT
    // -----------------------------
    public function payFinal(Request $req, Appointment $appointment)
    {
        $req->validate([
            'user_id' => 'required|exists:users,id',
            'payment_reference' => 'required|string'
        ]);

        // ❌ Final already paid
        if ($appointment->final_paid) {
            return response()->json([
                'status' => false,
                'message' => 'Final payment already paid'
            ], 422);
        }

        // 🔍 Find payment
        $payment = Payment::where('payment_id', $req->payment_reference)->first();

        if (!$payment) {
            return response()->json([
                'status' => false,
                'message' => 'Payment reference not found'
            ], 404);
        }

        // ❌ Payment already used
        if ($payment->payment_type != 'final' && $payment->payment_type != null) {
            return response()->json([
                'status' => false,
                'message' => 'Payment reference already used'
            ], 422);
        }

        // ❌ Payment used for another appointment
        if ($payment->appointment_id && $payment->appointment_id !== $appointment->id) {
            return response()->json([
                'status' => false,
                'message' => 'Payment reference already linked to another appointment'
            ], 422);
        }

        // ✅ Mark final paid
        $appointment->final_paid = 1;
        $appointment->save();

        $payment->update([
            'appointment_id'     => $appointment->id,
            'target_account'     => 'employer'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Final payment recorded successfully'
        ]);
    }




    public function laborEarnings(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'payment_type' => 'nullable|in:advance,final',
            'date' => 'nullable|date'
        ]);

        $user = User::find($request->user_id);

        // ❌ Not a labor
        if ($user->role !== 'labor') {
            return response()->json([
                'status' => false,
                'message' => 'User is not labor'
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | SUMMARY (Top Card)
        |--------------------------------------------------------------------------
        */

        // FINAL = current balance
        $currentBalance = Payment::where('payee_id', $user->id)
            ->where('payment_type', 'final')
            ->where('status', 'success')
            ->sum('amount');

        // ADVANCE = pending payout
        $pendingPayout = Payment::where('payee_id', $user->id)
            ->where('payment_type', 'advance')
            ->where('status', 'success')
            ->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | TRANSACTIONS LIST
        |--------------------------------------------------------------------------
        */

        $query = Payment::where('payee_id', $user->id)
            ->where('status', 'success');

        // 🔽 Filter by type
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
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
                    'amount' => '+ ₹' . number_format($payment->amount, 0),
                    'raw_amount' => $payment->amount,
                    'payment_type' => $payment->payment_type,
                    'date' => $payment->paid_at->format('d M Y'),
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
                'current_balance' => $currentBalance,
                'pending_payout' => $pendingPayout
            ],
            'transactions' => $transactions
        ]);
    }


    public function laborAppointmentPayments(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'appointment_id' => 'required|exists:appointments,id'
        ]);

        $user = User::find($request->user_id);

        if ($user->role !== 'labor') {
            return response()->json([
                'status' => false,
                'message' => 'User is not labor'
            ], 403);
        }

        $appointment = Appointment::find($request->appointment_id);

        // ensure appointment belongs to labor
        if ($appointment->labor_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Access denied'
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
            'payments' => $payments
        ]);
    }

    
    
}
