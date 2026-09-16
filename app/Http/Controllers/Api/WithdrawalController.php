<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\UserPaymentMethod;
use App\Models\Withdrawal;
use App\Services\CashfreePayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WithdrawalController extends Controller
{
    protected CashfreePayoutService $cashfreeService;

    public function __construct(CashfreePayoutService $cashfreeService)
    {
        $this->cashfreeService = $cashfreeService;
    }

    /**
     * Get user earnings and available balance summary for withdrawal
     * GET /api/withdrawals/summary
     */
    public function summary(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated'], 401);
        }

        $config = Setting::getWithdrawalConfig();

        // Total earnings strictly from completed FINAL payments as payee
        $totalFinalEarned = (float) Payment::where('payee_id', $user->id)
            ->where('payment_type', 'final')
            ->where('status', 'success')
            ->sum('amount');

        // Total advance payments held in escrow/pending job completion
        $pendingAdvance = (float) Payment::where('payee_id', $user->id)
            ->where('payment_type', 'advance')
            ->where('status', 'success')
            ->sum('amount');

        // Total already withdrawn (completed)
        $totalWithdrawn = (float) Withdrawal::where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('amount');

        // Total pending / in-process / on-hold withdrawal requests
        $pendingWithdrawal = (float) Withdrawal::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'processing', 'hold'])
            ->sum('amount');

        // Total rejected withdrawals (for stats)
        $rejectedAmount = (float) Withdrawal::where('user_id', $user->id)
            ->where('status', 'rejected')
            ->sum('amount');

        // Available withdrawable balance = Total Final Earned - (Completed + Pending/Processing)
        $availableBalance = max(0, round($totalFinalEarned - ($totalWithdrawn + $pendingWithdrawal), 2));

        // User's saved payout methods
        $payoutMethods = UserPaymentMethod::where('user_id', $user->id)
            ->orderByDesc('is_primary')
            ->orderByDesc('id')
            ->get();

        $primaryMethod = $payoutMethods->firstWhere('is_primary', true) ?: $payoutMethods->first();

        return response()->json([
            'status' => true,
            'user_id' => $user->id,
            'summary' => [
                'total_earned' => $totalFinalEarned,
                'final_earnings' => $totalFinalEarned,
                'pending_advance' => $pendingAdvance,
                'total_withdrawn' => $totalWithdrawn,
                'pending_withdrawal' => $pendingWithdrawal,
                'rejected_amount' => $rejectedAmount,
                'available_balance' => $availableBalance,
                'currency' => 'INR',
            ],
            'rules' => [
                'withdrawals_enabled' => $config['enabled'],
                'auto_withdrawal_enabled' => $config['auto_payout_enabled'],
                'auto_withdrawal_max_limit' => $config['auto_payout_max_limit'],
                'min_withdrawal_amount' => $config['min_amount'],
                'max_withdrawal_amount' => $config['max_amount'],
            ],
            'primary_payout_method' => $primaryMethod,
            'payout_methods' => $payoutMethods,
        ], 200);
    }

    /**
     * Request a withdrawal
     * POST /api/withdrawals/request
     */
    public function requestWithdrawal(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated'], 401);
        }

        $config = Setting::getWithdrawalConfig();

        // 1. Check if withdrawal system is enabled
        if (!$config['enabled']) {
            return response()->json([
                'status' => false,
                'message' => 'Withdrawals are currently disabled by the administration.',
            ], 403);
        }

        // 2. Validate request
        $request->validate([
            'amount' => 'required|numeric|gt:0|min:' . $config['min_amount'] . '|max:' . $config['max_amount'],
            'payout_method_id' => 'nullable|integer',
            'notes' => 'nullable|string|max:500',
        ], [
            'amount.gt' => 'Withdrawal amount must be greater than zero.',
            'amount.min' => 'Minimum withdrawal amount is ₹' . number_format($config['min_amount'], 2),
            'amount.max' => 'Maximum withdrawal amount per request is ₹' . number_format($config['max_amount'], 2),
        ]);

        $requestedAmount = round((float) $request->amount, 2);

        // 3. Resolve Payout Method
        $payoutMethod = null;
        if ($request->filled('payout_method_id')) {
            $payoutMethod = UserPaymentMethod::where('id', $request->payout_method_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$payoutMethod) {
                return response()->json([
                    'status' => false,
                    'message' => 'Selected payout method not found or does not belong to your account.',
                ], 422);
            }
        } else {
            $payoutMethod = UserPaymentMethod::where('user_id', $user->id)
                ->orderByDesc('is_primary')
                ->orderByDesc('id')
                ->first();
        }

        if (!$payoutMethod) {
            return response()->json([
                'status' => false,
                'message' => 'No valid payout method (Bank Account / UPI ID) found. Please add a payment method before requesting a withdrawal.',
            ], 422);
        }

        $payoutDetails = is_array($payoutMethod->details) ? $payoutMethod->details : [];
        $payoutType = $payoutMethod->type ?: ($payoutDetails['type'] ?? 'bank');

        // 4. DB Transaction with Row-Level Lock to prevent concurrent double-withdrawal
        $withdrawal = null;

        try {
            $withdrawal = DB::transaction(function () use ($user, $requestedAmount, $payoutMethod, $payoutType, $payoutDetails, $request, $config) {
                // Lock user record to serialize concurrent requests
                \App\Models\User::where('id', $user->id)->lockForUpdate()->first();

                // Lock & sum ONLY completed FINAL payments
                $totalFinalEarned = (float) Payment::where('payee_id', $user->id)
                    ->where('payment_type', 'final')
                    ->where('status', 'success')
                    ->lockForUpdate()
                    ->sum('amount');

                // Lock & sum active withdrawals
                $activeWithdrawals = (float) Withdrawal::where('user_id', $user->id)
                    ->whereIn('status', ['completed', 'pending', 'processing'])
                    ->lockForUpdate()
                    ->sum('amount');

                $availableBalance = max(0, round($totalFinalEarned - $activeWithdrawals, 2));

                if ($requestedAmount > $availableBalance || $availableBalance <= 0) {
                    throw new \Exception('Insufficient available balance. Your withdrawable balance from completed final payments is ₹' . number_format($availableBalance, 2));
                }

                // Generate unique Withdrawal ID
                $withdrawalId = 'WD' . date('Ymd') . Str::upper(Str::random(6));
                while (Withdrawal::where('withdrawal_id', $withdrawalId)->exists()) {
                    $withdrawalId = 'WD' . date('Ymd') . Str::upper(Str::random(6));
                }

                // Fetch verified source final payments snapshot for audit
                $sourceJobs = Payment::where('payee_id', $user->id)
                    ->where('payment_type', 'final')
                    ->where('status', 'success')
                    ->with(['job:id,job_title', 'payer:id,name,phone'])
                    ->orderByDesc('paid_at')
                    ->limit(10)
                    ->get()
                    ->map(function ($p) {
                        return [
                            'payment_id' => $p->payment_id,
                            'job_id' => $p->job_post_id,
                            'job_title' => $p->job?->job_title ?? ($p->appointment_id ? 'Appointment #' . $p->appointment_id : 'Direct Job'),
                            'payer_name' => $p->payer?->name ?? 'Client',
                            'payer_phone' => $p->payer?->phone ?? '',
                            'amount' => (float) $p->amount,
                            'paid_at' => $p->paid_at ? $p->paid_at->format('d M Y, h:i A') : ($p->created_at ? $p->created_at->format('d M Y, h:i A') : ''),
                            'reference' => $p->payment_reference,
                        ];
                    })->toArray();

                $record = Withdrawal::create([
                    'withdrawal_id' => $withdrawalId,
                    'user_id' => $user->id,
                    'amount' => $requestedAmount,
                    'currency' => 'INR',
                    'user_payment_method_id' => $payoutMethod->id,
                    'payout_type' => $payoutType,
                    'payout_details' => array_merge($payoutDetails, [
                        'masked' => $payoutMethod->masked,
                        'provider' => $payoutMethod->provider,
                        'source_jobs' => $sourceJobs,
                    ]),
                    'status' => 'pending',
                    'is_auto_payout' => false,
                    'admin_notes' => $request->notes ?: 'Withdrawal request submitted. Awaiting scheduled processing or admin approval.',
                ]);

                return $record;
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        // Calculate new available balance
        $totalFinalEarned = (float) Payment::where('payee_id', $user->id)
            ->where('payment_type', 'final')
            ->where('status', 'success')
            ->sum('amount');
        $activeWithdrawals = (float) Withdrawal::where('user_id', $user->id)
            ->whereIn('status', ['completed', 'pending', 'processing', 'hold'])
            ->sum('amount');
        $newAvailableBalance = max(0, round($totalFinalEarned - $activeWithdrawals, 2));

        return response()->json([
            'status' => true,
            'message' => 'Withdrawal request of ₹' . number_format($requestedAmount, 2) . ' submitted successfully. It will be processed to your ' . strtoupper($withdrawal->payout_type) . ' account.',
            'withdrawal' => [
                'id' => $withdrawal->id,
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'amount' => $withdrawal->amount,
                'currency' => $withdrawal->currency,
                'payout_type' => $withdrawal->payout_type,
                'payout_details' => $withdrawal->payout_details,
                'status' => $withdrawal->status,
                'is_auto_payout' => $withdrawal->is_auto_payout,
                'reference_id' => $withdrawal->cf_reference_id,
                'created_at' => $withdrawal->created_at->format('Y-m-d H:i:s'),
                'paid_at' => null,
            ],
            'remaining_balance' => $newAvailableBalance,
        ], 201);
    }

    /**
     * Get user withdrawal history
     * GET /api/withdrawals/history
     */
    public function history(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated'], 401);
        }

        $query = Withdrawal::where('user_id', $user->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $perPage = (int) ($request->per_page ?? 15);
        $withdrawals = $query->orderByDesc('id')->paginate($perPage);

        $formatted = $withdrawals->getCollection()->map(function ($w) {
            $masked = $w->payout_details['masked'] ?? ($w->payout_details['upi_id'] ?? ($w->payout_details['account_number'] ?? ''));
            return [
                'id' => $w->id,
                'withdrawal_id' => $w->withdrawal_id,
                'amount' => $w->amount,
                'formatted_amount' => '₹' . number_format($w->amount, 2),
                'status' => $w->status,
                'payout_type' => $w->payout_type,
                'account_masked' => $masked,
                'is_auto_payout' => (bool) $w->is_auto_payout,
                'reference_id' => $w->cf_reference_id,
                'rejection_reason' => $w->rejection_reason,
                'created_at' => $w->created_at->format('d M Y, h:i A'),
                'paid_at' => $w->paid_at ? $w->paid_at->format('d M Y, h:i A') : null,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $formatted,
            'pagination' => [
                'current_page' => $withdrawals->currentPage(),
                'last_page' => $withdrawals->lastPage(),
                'per_page' => $withdrawals->perPage(),
                'total' => $withdrawals->total(),
            ],
        ], 200);
    }

    /**
     * Get single withdrawal details
     * GET /api/withdrawals/{id}
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated'], 401);
        }

        $withdrawal = Withdrawal::where('user_id', $user->id)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('withdrawal_id', $id);
            })
            ->first();

        if (!$withdrawal) {
            return response()->json(['status' => false, 'message' => 'Withdrawal record not found'], 404);
        }

        return response()->json([
            'status' => true,
            'withdrawal' => [
                'id' => $withdrawal->id,
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'amount' => $withdrawal->amount,
                'formatted_amount' => '₹' . number_format($withdrawal->amount, 2),
                'currency' => $withdrawal->currency,
                'status' => $withdrawal->status,
                'payout_type' => $withdrawal->payout_type,
                'payout_details' => $withdrawal->payout_details,
                'is_auto_payout' => (bool) $withdrawal->is_auto_payout,
                'reference_id' => $withdrawal->cf_reference_id,
                'rejection_reason' => $withdrawal->rejection_reason,
                'created_at' => $withdrawal->created_at->format('d M Y, h:i A'),
                'paid_at' => $withdrawal->paid_at ? $withdrawal->paid_at->format('d M Y, h:i A') : null,
            ],
        ], 200);
    }
}
