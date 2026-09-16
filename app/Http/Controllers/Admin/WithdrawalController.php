<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\CashfreePayoutService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WithdrawalController extends Controller
{
    protected CashfreePayoutService $cashfreeService;

    public function __construct(CashfreePayoutService $cashfreeService)
    {
        $this->cashfreeService = $cashfreeService;
    }

    /**
     * Display a listing of withdrawals with filters and KPI metrics
     */
    public function index(Request $request)
    {
        $query = Withdrawal::with(['user', 'paymentMethod', 'processedBy']);

        // Filter: Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter: Payout Type (upi, bank, wallet)
        if ($request->filled('payout_type') && $request->payout_type !== 'all') {
            $query->where('payout_type', $request->payout_type);
        }

        // Filter: Mode (auto vs manual)
        if ($request->filled('mode') && $request->mode !== 'all') {
            if ($request->mode === 'auto') {
                $query->where('is_auto_payout', true);
            } elseif ($request->mode === 'manual') {
                $query->where('is_auto_payout', false);
            }
        }

        // Filter: User ID or Name search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('withdrawal_id', 'like', "%{$search}%")
                  ->orWhere('cf_transfer_id', 'like', "%{$search}%")
                  ->orWhere('cf_reference_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Filter: Date range
        if ($request->filled('start_date') || $request->filled('end_date')) {
            $startDate = $request->filled('start_date')
                ? Carbon::parse($request->start_date)->startOfDay()
                : Carbon::now()->startOfMonth()->startOfDay();

            $endDate = $request->filled('end_date')
                ? Carbon::parse($request->end_date)->endOfDay()
                : Carbon::now()->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // KPI Summary Metrics
        $totalCompleted = Withdrawal::where('status', 'completed')->sum('amount');
        $totalCompletedCount = Withdrawal::where('status', 'completed')->count();

        $totalPending = Withdrawal::whereIn('status', ['pending', 'processing'])->sum('amount');
        $totalPendingCount = Withdrawal::whereIn('status', ['pending', 'processing'])->count();

        $totalHold = Withdrawal::where('status', 'hold')->sum('amount');
        $totalHoldCount = Withdrawal::where('status', 'hold')->count();

        $totalAutoPayout = Withdrawal::where('status', 'completed')->where('is_auto_payout', true)->sum('amount');
        $totalAutoPayoutCount = Withdrawal::where('status', 'completed')->where('is_auto_payout', true)->count();

        $totalRejected = Withdrawal::where('status', 'rejected')->sum('amount');
        $totalRejectedCount = Withdrawal::where('status', 'rejected')->count();

        $perPage = (int) ($request->per_page ?? 20);
        $withdrawals = $query->orderByDesc('id')->paginate($perPage)->withQueryString();

        return view('admin.withdrawals.index', compact(
            'withdrawals',
            'totalCompleted',
            'totalCompletedCount',
            'totalPending',
            'totalPendingCount',
            'totalHold',
            'totalHoldCount',
            'totalAutoPayout',
            'totalAutoPayoutCount',
            'totalRejected',
            'totalRejectedCount'
        ));
    }

    /**
     * Show single withdrawal details (modal/view JSON)
     */
    public function show($id)
    {
        $withdrawal = Withdrawal::with(['user', 'paymentMethod', 'processedBy'])->findOrFail($id);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'status' => true,
                'data' => $withdrawal,
            ]);
        }

        return view('admin.withdrawals.show', compact('withdrawal'));
    }

    /**
     * Process Payout via Cashfree Gateway
     */
    public function processPayout(Request $request, $id)
    {
        $withdrawal = Withdrawal::findOrFail($id);

        if ($withdrawal->status === 'completed') {
            return back()->with('error', 'This withdrawal has already been completed.');
        }

        $res = $this->cashfreeService->initiateTransfer($withdrawal);

        if ($res['success']) {
            $isSuccess = $res['status'] === 'SUCCESS';
            $withdrawal->update([
                'status' => $isSuccess ? 'completed' : 'processing',
                'is_auto_payout' => true,
                'cf_transfer_id' => $res['transfer_id'],
                'cf_reference_id' => $res['reference_id'],
                'cf_response' => $res['raw'],
                'processed_by' => Auth::id(),
                'processed_at' => now(),
                'paid_at' => $isSuccess ? now() : null,
                'admin_notes' => 'Payout initiated by admin via Cashfree: ' . ($res['message'] ?? 'OK'),
            ]);

            return back()->with('success', 'Cashfree payout transfer initiated successfully! Transfer ID: ' . $res['transfer_id']);
        }

        // Save error info in withdrawal logs
        $withdrawal->update([
            'cf_response' => $res['raw'] ?? ['error' => $res['message']],
            'admin_notes' => 'Manual Cashfree payout attempt failed: ' . $res['message'],
        ]);

        return back()->with('error', 'Cashfree payout failed: ' . $res['message']);
    }

    /**
     * Batch process all eligible pending auto-withdrawals
     */
    public function processBatchAuto(Request $request)
    {
        $config = \App\Models\Setting::getWithdrawalConfig();
        $maxLimit = (float) $config['auto_payout_max_limit'];

        $pending = Withdrawal::where('status', 'pending')
            ->where('amount', '<=', $maxLimit)
            ->with(['user', 'paymentMethod'])
            ->orderBy('id', 'asc')
            ->limit(50)
            ->get();

        if ($pending->isEmpty()) {
            return back()->with('error', 'No eligible pending requests found for auto-payout (Amount <= ₹' . number_format($maxLimit, 2) . ').');
        }

        $successCount = 0;
        foreach ($pending as $withdrawal) {
            $res = $this->cashfreeService->initiateTransfer($withdrawal);
            if ($res['success']) {
                $isSuccess = $res['status'] === 'SUCCESS';
                $withdrawal->update([
                    'status' => $isSuccess ? 'completed' : 'processing',
                    'is_auto_payout' => true,
                    'cf_transfer_id' => $res['transfer_id'],
                    'cf_reference_id' => $res['reference_id'],
                    'cf_response' => $res['raw'],
                    'processed_by' => Auth::id(),
                    'processed_at' => now(),
                    'paid_at' => $isSuccess ? now() : null,
                    'admin_notes' => 'Batch payout processed by admin: ' . ($res['message'] ?? 'OK'),
                ]);
                $successCount++;
            }
        }

        return back()->with('success', "Batch processing complete! {$successCount} requests successfully transferred via Cashfree.");
    }

    /**
     * Test Cashfree Payout API Connection / 2FA status
     */
    public function testConnection()
    {
        $res = $this->cashfreeService->testConnection();
        return response()->json($res);
    }

    /**
     * Test a single direct payout transfer
     */
    public function testPayoutTransfer(Request $request)
    {
        $request->validate([
            'payout_type' => 'required|in:upi,bank',
            'amount' => 'required|numeric|min:1',
            'upi_id' => 'required_if:payout_type,upi',
            'account_number' => 'required_if:payout_type,bank',
            'ifsc' => 'required_if:payout_type,bank',
        ]);

        $details = [
            'name' => $request->name ?: 'Test Recipient',
            'phone' => $request->phone ?: '9999999999',
            'email' => $request->email ?: 'test@legacycarry.com',
            'upi_id' => $request->upi_id,
            'account_number' => $request->account_number,
            'ifsc' => $request->ifsc,
        ];

        $res = $this->cashfreeService->testDirectTransfer(
            $request->payout_type,
            $details,
            (float) $request->amount,
            $request->remarks ?: 'Live Admin Test Payout'
        );

        return response()->json($res);
    }

    /**
     * Mark withdrawal as manually paid (Offline NEFT / IMPS / UPI)
     */
    public function markManualPaid(Request $request, $id)
    {
        $request->validate([
            'reference_id' => 'required|string|max:100',
            'notes' => 'nullable|string|max:500',
        ], [
            'reference_id.required' => 'Please provide the Bank UTR / Transaction Reference number.',
        ]);

        $withdrawal = Withdrawal::findOrFail($id);

        if ($withdrawal->status === 'completed') {
            return back()->with('error', 'This withdrawal has already been marked as completed.');
        }

        $withdrawal->update([
            'status' => 'completed',
            'is_auto_payout' => false,
            'cf_reference_id' => $request->reference_id,
            'admin_notes' => $request->notes ? $request->notes : 'Marked as manually paid by admin with UTR: ' . $request->reference_id,
            'processed_by' => Auth::id(),
            'processed_at' => now(),
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Withdrawal successfully marked as Paid (Manual UTR: ' . $request->reference_id . ').');
    }

    /**
     * Put a withdrawal request on Hold with Note
     */
    public function hold(Request $request, $id)
    {
        $request->validate([
            'hold_reason' => 'required|string|max:500',
        ], [
            'hold_reason.required' => 'Please provide a note/reason for putting this withdrawal on hold.',
        ]);

        $withdrawal = Withdrawal::findOrFail($id);

        if ($withdrawal->status === 'completed') {
            return back()->with('error', 'Cannot put a completed payout on hold.');
        }

        $withdrawal->update([
            'status' => 'hold',
            'admin_notes' => 'Put on hold by admin: ' . $request->hold_reason,
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Withdrawal request has been put on Hold.');
    }

    /**
     * Release withdrawal request from Hold back to Pending
     */
    public function unhold(Request $request, $id)
    {
        $withdrawal = Withdrawal::findOrFail($id);

        if ($withdrawal->status !== 'hold') {
            return back()->with('error', 'This withdrawal request is not currently on hold.');
        }

        $withdrawal->update([
            'status' => 'pending',
            'admin_notes' => 'Released from hold to pending status by admin. ' . ($request->notes ?? ''),
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Withdrawal request released from Hold and returned to Pending.');
    }

    /**
     * Reject a withdrawal request with Note / Reason
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ], [
            'rejection_reason.required' => 'Please provide a reason for rejecting this withdrawal request.',
        ]);

        $withdrawal = Withdrawal::findOrFail($id);

        if ($withdrawal->status === 'completed') {
            return back()->with('error', 'Cannot reject a completed payout.');
        }

        $withdrawal->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'processed_by' => Auth::id(),
            'processed_at' => now(),
            'admin_notes' => 'Rejected by admin: ' . $request->rejection_reason,
        ]);

        return back()->with('success', 'Withdrawal request rejected. The requested amount has been restored to the user\'s available balance.');
    }

    /**
     * Export withdrawals to CSV
     */
    public function export(Request $request)
    {
        $query = Withdrawal::with(['user', 'paymentMethod', 'processedBy']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('payout_type') && $request->payout_type !== 'all') {
            $query->where('payout_type', $request->payout_type);
        }
        if ($request->filled('start_date') || $request->filled('end_date')) {
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $results = $query->orderByDesc('id')->get();

        $response = new StreamedResponse(function () use ($results) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, [
                'Withdrawal ID',
                'User Name',
                'User Email',
                'User Phone',
                'Amount (INR)',
                'Payout Type',
                'Account / UPI Details',
                'Status',
                'Payout Mode',
                'Cashfree Transfer ID',
                'Reference / UTR ID',
                'Rejection Reason',
                'Admin Notes',
                'Processed By',
                'Created At',
                'Paid At',
            ]);

            foreach ($results as $w) {
                $details = is_array($w->payout_details) ? json_encode($w->payout_details) : '';
                fputcsv($handle, [
                    $w->withdrawal_id,
                    $w->user?->name ?? 'N/A',
                    $w->user?->email ?? 'N/A',
                    $w->user?->phone ?? 'N/A',
                    number_format($w->amount, 2, '.', ''),
                    strtoupper($w->payout_type),
                    $w->payout_details['masked'] ?? ($w->payout_details['upi_id'] ?? ($w->payout_details['account_number'] ?? $details)),
                    strtoupper($w->status),
                    $w->is_auto_payout ? 'Auto (Cashfree)' : 'Manual',
                    $w->cf_transfer_id ?? '',
                    $w->cf_reference_id ?? '',
                    $w->rejection_reason ?? '',
                    $w->admin_notes ?? '',
                    $w->processedBy?->name ?? '',
                    $w->created_at ? $w->created_at->format('Y-m-d H:i:s') : '',
                    $w->paid_at ? $w->paid_at->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($handle);
        });

        $fileName = 'withdrawals_export_' . now()->format('Ymd_His') . '.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }
}
