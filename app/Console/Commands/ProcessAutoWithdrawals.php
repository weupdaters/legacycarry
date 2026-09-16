<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\Withdrawal;
use App\Services\CashfreePayoutService;
use Illuminate\Console\Command;

class ProcessAutoWithdrawals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'withdrawals:process-auto {--limit=50 : Maximum number of requests to process in this run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process all eligible pending auto-withdrawal requests via Cashfree Payout API';

    /**
     * Execute the console command.
     */
    public function handle(CashfreePayoutService $cashfreeService)
    {
        $this->info('Starting Auto-Withdrawal Batch Processing...');

        $config = Setting::getWithdrawalConfig();

        if (!$config['enabled']) {
            $this->warn('Withdrawals are globally disabled in admin settings.');
            return 0;
        }

        if (!$config['auto_payout_enabled']) {
            $this->warn('Auto-payout is disabled in admin settings.');
            return 0;
        }

        $maxLimit = (float) $config['auto_payout_max_limit'];
        $batchLimit = (int) $this->option('limit');

        $pending = Withdrawal::where('status', 'pending')
            ->where('amount', '<=', $maxLimit)
            ->with(['user', 'paymentMethod'])
            ->orderBy('id', 'asc')
            ->limit($batchLimit)
            ->get();

        if ($pending->isEmpty()) {
            $this->info('No eligible pending auto-withdrawals found (Amount <= ₹' . number_format($maxLimit, 2) . ').');
            return 0;
        }

        $this->info('Found ' . $pending->count() . ' pending requests to process...');

        $successCount = 0;
        $failedCount = 0;

        foreach ($pending as $withdrawal) {
            $this->line("Processing ID: {$withdrawal->withdrawal_id} (₹{$withdrawal->amount}) for User: {$withdrawal->user?->name}...");

            $res = $cashfreeService->initiateTransfer($withdrawal);

            if ($res['success']) {
                $isSuccess = $res['status'] === 'SUCCESS';
                $withdrawal->update([
                    'status' => $isSuccess ? 'completed' : 'processing',
                    'is_auto_payout' => true,
                    'cf_transfer_id' => $res['transfer_id'],
                    'cf_reference_id' => $res['reference_id'],
                    'cf_response' => $res['raw'],
                    'paid_at' => $isSuccess ? now() : null,
                    'admin_notes' => 'Processed via scheduled auto-payout command: ' . ($res['message'] ?? 'OK'),
                ]);

                $this->info(" -> SUCCESS: Transfer ID: {$res['transfer_id']}");
                $successCount++;
            } else {
                $withdrawal->update([
                    'cf_response' => $res['raw'] ?? ['error' => $res['message']],
                    'admin_notes' => 'Auto-payout attempt failed: ' . ($res['message'] ?? 'Unknown gateway error'),
                ]);

                $this->error(" -> FAILED: " . ($res['message'] ?? 'Gateway Error'));
                $failedCount++;
            }
        }

        $this->info("Completed. Success: {$successCount}, Failed/Pending: {$failedCount}");
        return 0;
    }
}
