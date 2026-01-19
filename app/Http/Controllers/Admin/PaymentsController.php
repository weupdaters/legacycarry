<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentsController extends Controller
{
    /**
     * Admin: list all payments with full details, filters and pagination.
     */
    public function adminIndex(Request $request)
    {
        // Validate incoming filter inputs
        $validated = $request->validate([
            'payment_id' => 'nullable|string',
            'payer_id' => 'nullable|integer',
            'payee_id' => 'nullable|integer',
            'status' => 'nullable|string',
            'payment_type' => 'nullable|string',
            'target_account' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'per_page' => 'nullable|integer',
            'search' => 'nullable|string',
        ]);

        // Base query
        $query = Payment::query()
            ->with(['payer', 'payee', 'cashVerifiedBy']);

        // Filters
        if (!empty($validated['payment_id'])) {
            $query->where('payment_id', 'like', '%' . $validated['payment_id'] . '%');
        }

        if (!empty($validated['payer_id'])) {
            $query->where('payer_id', $validated['payer_id']);
        }

        if (!empty($validated['payee_id'])) {
            $query->where('payee_id', $validated['payee_id']);
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['payment_type'])) {
            $query->where('payment_type', $validated['payment_type']);
        }

        if (!empty($validated['target_account'])) {
            $query->where('target_account', $validated['target_account']);
        }

        if (!empty($validated['start_date']) || !empty($validated['end_date'])) {
            $startDate = !empty($validated['start_date'])
                ? Carbon::parse($validated['start_date'])->startOfDay()
                : now()->startOfMonth()->startOfDay();

            $endDate = !empty($validated['end_date'])
                ? Carbon::parse($validated['end_date'])->endOfDay()
                : now()->endOfDay();

            $query->whereBetween('paid_at', [$startDate, $endDate]);
        }

        if (!empty($validated['search'])) {
            $searchTerm = $validated['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('payment_id', 'like', "%{$searchTerm}%")
                  ->orWhere('payment_reference', 'like', "%{$searchTerm}%")
                  ->orWhere('notes', 'like', "%{$searchTerm}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | TOTALS (IMPORTANT PART)
        |--------------------------------------------------------------------------
        | Clone the SAME filtered query
        | Count only SUCCESS payments
        */
        $totalsQuery = clone $query;
        $totalsQuery->where('status', 'success');

        $totalAmount = (clone $totalsQuery)->sum('amount');

        $advanceAmount = (clone $totalsQuery)
            ->where('payment_type', 'advance')
            ->sum('amount');

        $finalAmount = (clone $totalsQuery)
            ->where('payment_type', 'final')
            ->sum('amount');

        $totalCredit = (clone $totalsQuery)
            ->where('target_account', 'admin')
            ->sum('amount');

        $totalDebit = (clone $totalsQuery)
            ->where('target_account', 'employer')
            ->sum('amount');

        // Pagination
        $perPage = (int) ($validated['per_page'] ?? 25);

        $payments = $query
            ->orderBy('paid_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // Return view
        return view('admin.payments.index', [
            'payments' => $payments,
            'filters' => $validated,
            'perPage' => $perPage,

            // Totals
            'totalAmount' => $totalAmount,
            'advanceAmount' => $advanceAmount,
            'finalAmount' => $finalAmount,
            'totalCredit' => $totalCredit,
            'totalDebit' => $totalDebit,
        ]);
    }

    /**
     * Admin: show single payment full details.
     */
    public function adminShow(Payment $payment)
    {
        $payment->load(['payer', 'payee', 'cashVerifiedBy']);

        return view('admin.payments.show', [
            'payment' => $payment,
        ]);
    }

    /**
     * Admin: export filtered payments CSV (Excel compatible).
     */
    public function adminExport(Request $request)
    {
        // reuse validation from index for same filters
        $validated = $request->validate([
            'payment_id' => 'nullable|string',
            'payer_id' => 'nullable|integer',
            'payee_id' => 'nullable|integer',
            'status' => 'nullable|string',
            'payment_type' => 'nullable|string',
            'target_account' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'search' => 'nullable|string',
        ]);

        $query = Payment::query()->with(['payer', 'payee', 'cashVerifiedBy']);

        // Apply same filters as in adminIndex
        if (!empty($validated['payment_id'])) {
            $query->where('payment_id', 'like', '%' . $validated['payment_id'] . '%');
        }
        if (!empty($validated['payer_id'])) {
            $query->where('payer_id', $validated['payer_id']);
        }
        if (!empty($validated['payee_id'])) {
            $query->where('payee_id', $validated['payee_id']);
        }
        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }
        if (!empty($validated['payment_type'])) {
            $query->where('payment_type', $validated['payment_type']);
        }
        if (!empty($validated['target_account'])) {
            $query->where('target_account', $validated['target_account']);
        }
        if (!empty($validated['start_date']) || !empty($validated['end_date'])) {
            $startDate = !empty($validated['start_date']) ? Carbon::parse($validated['start_date'])->startOfDay() : Carbon::now()->startOfMonth();
            $endDate = !empty($validated['end_date']) ? Carbon::parse($validated['end_date'])->endOfDay() : Carbon::now()->endOfDay();
            $query->whereBetween('paid_at', [$startDate, $endDate]);
        }
        if (!empty($validated['search'])) {
            $searchTerm = $validated['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('payment_id', 'like', '%' . $searchTerm . '%')
                  ->orWhere('payment_reference', 'like', '%' . $searchTerm . '%')
                  ->orWhere('notes', 'like', '%' . $searchTerm . '%');
            });
        }

        $results = $query->orderBy('paid_at', 'desc')->get();

        $response = new StreamedResponse(function () use ($results) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Column headers - explicit full names (no short names)
            fputcsv($handle, [
                'Database ID',
                'Payment Identifier',
                'Job Post ID',
                'Job Application ID',
                'Payer ID',
                'Payer Name',
                'Payer Email',
                'Payee ID',
                'Payee Name',
                'Payee Email',
                'Amount',
                'Currency',
                'Payment Method',
                'Payment Reference',
                'Notes',
                'Status',
                'Paid At (timestamp)',
                'Created At (timestamp)',
                'Updated At (timestamp)',
                'Appointment ID',
                'Payment Type',
                'Target Account',
                'Cash Verified By (ID)',
                'Cash Verified By (Name)',
                'Cash Verified At (timestamp)',
            ]);

            foreach ($results as $payment) {
                // safely get related user info
                $payerName = optional($payment->payer)->name ?? '';
                $payerEmail = optional($payment->payer)->email ?? '';
                $payeeName = optional($payment->payee)->name ?? '';
                $payeeEmail = optional($payment->payee)->email ?? '';
                $verifierName = optional($payment->cashVerifiedBy)->name ?? '';

                fputcsv($handle, [
                    $payment->id,
                    $payment->payment_id,
                    $payment->job_post_id,
                    $payment->job_application_id,
                    $payment->payer_id,
                    $payerName,
                    $payerEmail,
                    $payment->payee_id,
                    $payeeName,
                    $payeeEmail,
                    number_format($payment->amount, 2, '.', ''),
                    $payment->currency,
                    $payment->payment_method,
                    $payment->payment_reference,
                    $payment->notes,
                    $payment->status,
                    $payment->paid_at ? Carbon::parse($payment->paid_at)->toDateTimeString() : '',
                    $payment->created_at ? Carbon::parse($payment->created_at)->toDateTimeString() : '',
                    $payment->updated_at ? Carbon::parse($payment->updated_at)->toDateTimeString() : '',
                    $payment->appointment_id,
                    $payment->payment_type,
                    $payment->target_account,
                    $payment->cash_verified_by,
                    $verifierName,
                    $payment->cash_verified_at ? Carbon::parse($payment->cash_verified_at)->toDateTimeString() : '',
                ]);
            }

            fclose($handle);
        });

        $fileName = 'admin_payments_export_' . now()->format('Ymd_His') . '.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }

    // ... keep your other methods (ledger/exportLedger) below or above as needed ...
}
