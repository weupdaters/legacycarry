<?php

namespace App\Console\Commands;

use App\Services\CashfreePayoutService;
use Illuminate\Console\Command;

class TestCashfreePayout extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cashfree:test-payout 
                            {--mode=auth : Mode: "auth" to test token/connection or "transfer" to do test payout}
                            {--upi= : UPI VPA (e.g. user@okhdfcbank) if testing payout}
                            {--account= : Bank account number if testing bank transfer}
                            {--ifsc= : Bank IFSC code if testing bank transfer}
                            {--name=Test User : Account holder name}
                            {--phone= : Recipient phone number}
                            {--amount=1.00 : Amount in INR (default: 1.00)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Cashfree Payout API connection and initiate a test transfer';

    /**
     * Execute the console command.
     */
    public function handle(CashfreePayoutService $service)
    {
        $this->info('=============================================');
        $this->info('  Cashfree Payout Connection & Transfer Test ');
        $this->info('=============================================');

        $this->line('Environment : <fg=yellow>' . strtoupper($service->getEnvironment()) . '</>');
        $this->line('Client ID   : <fg=cyan>' . $service->getClientId() . '</>');
        $this->line('Base URL    : ' . $service->getBaseUrl());
        $this->newLine();

        $this->comment('1. Testing Cashfree Authorization API...');
        $authTest = $service->testConnection();

        if (!$authTest['success']) {
            $this->error('❌ Authorization Failed!');
            $this->line('Status Code : ' . ($authTest['subCode'] ?? 'Unknown'));
            $this->line('Message     : ' . ($authTest['message'] ?? 'None'));

            if (($authTest['subCode'] ?? '') === '403' || str_contains(strtolower($authTest['message'] ?? ''), 'ip not whitelisted')) {
                $this->newLine();
                $this->warn('⚠️ ACTION REQUIRED: IP Whitelist Needed in Cashfree Dashboard!');
                $this->line('Cashfree requires your server IP to be whitelisted for Payouts 2FA:');
                $this->line('1. Open: https://merchant.cashfree.com/merchants/login');
                $this->line('2. Select: Payouts -> Developers -> Two-Factor Authentication (2FA)');
                $ipMatch = '49.43.109.122';
                if (preg_match('/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $authTest['message'] ?? '', $m)) {
                    $ipMatch = $m[1];
                }
                $this->line('3. Add IP Address: <fg=green;options=bold>' . $ipMatch . '</>');
                $this->line('4. Once added, run this command again to verify and test transfer!');
            }
            return 1;
        }

        $this->info('✅ Authorization Succeeded! Bearer Token obtained.');
        $this->line('Token: ' . substr($authTest['token'] ?? '', 0, 15) . '...');
        $this->newLine();

        $mode = $this->option('mode');
        if ($mode !== 'transfer') {
            $this->info('Test complete. Payout credentials and connection are 100% working!');
            $this->comment('To initiate 1 test payout, run:');
            $this->line('php artisan cashfree:test-payout --mode=transfer --upi=your_upi_id@bank --amount=1.00');
            return 0;
        }

        // Transfer Mode
        $upi = $this->option('upi');
        $account = $this->option('account');
        $ifsc = $this->option('ifsc');
        $name = $this->option('name') ?: 'Test User';
        $amount = (float) ($this->option('amount') ?: 1.00);

        if (!$upi && (!$account || !$ifsc)) {
            $this->error('Please provide either --upi=... or (--account=... and --ifsc=...)');
            return 1;
        }

        $this->comment("2. Initiating test payout of ₹{$amount} to " . ($upi ?: "Account {$account}") . "...");

        $type = $upi ? 'upi' : 'bank';
        $phone = $this->option('phone') ?: '9915041743';
        $details = [
            'name' => $name,
            'phone' => $phone,
            'email' => 'ranjitsingh@legacycarry.com',
            'upi_id' => $upi,
            'account_number' => $account,
            'ifsc' => $ifsc,
        ];

        $res = $service->testDirectTransfer($type, $details, $amount, 'Test verification payout');

        if ($res['success']) {
            $this->info('🎉 Payout Transfer Initiated Successfully!');
            $this->line('Transfer ID   : ' . ($res['transfer_id'] ?? 'N/A'));
            $this->line('Cashfree Ref  : ' . ($res['reference_id'] ?? 'N/A'));
            $this->line('Status        : ' . ($res['status'] ?? 'SUCCESS'));
            $this->line('Message       : ' . ($res['message'] ?? 'OK'));

            $this->newLine();
            $this->comment('Checking status from Cashfree API...');
            sleep(1);
            $check = $service->getTransferStatus($res['transfer_id']);
            if (!empty($check['data'])) {
                $st = $check['data'];
                $this->line('Current Status: ' . ($st['status'] ?? 'N/A'));
                if (!empty($st['status_code'])) {
                    $this->line('Status Code   : ' . $st['status_code']);
                }
                if (!empty($st['status_description'])) {
                    $this->line('Description   : ' . $st['status_description']);
                }
            }
            return 0;
        } else {
            $this->error('❌ Payout Transfer Failed!');
            $this->line('Message : ' . ($res['message'] ?? 'N/A'));
            return 1;
        }
    }
}
