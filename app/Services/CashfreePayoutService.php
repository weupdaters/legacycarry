<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CashfreePayoutService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $environment;
    protected string $baseUrl;
    protected string $fundsourceId;
    protected ?string $lastAuthError = null;

    public function __construct()
    {
        $config = class_exists(Setting::class) ? Setting::getCashfreePayoutConfig() : [];

        $this->environment = strtolower((string) ($config['environment'] ?? config('services.cashfree_payout.environment') ?: env('CASHFREE_PAYOUT_ENV', 'production')));
        $this->clientId = (string) ($config['client_id'] ?? config('services.cashfree_payout.client_id') ?: env('CASHFREE_PAYOUT_CLIENT_ID', ''));
        $this->clientSecret = (string) ($config['client_secret'] ?? config('services.cashfree_payout.client_secret') ?: env('CASHFREE_PAYOUT_CLIENT_SECRET', ''));
        $this->fundsourceId = (string) ($config['fundsource_id'] ?? config('services.cashfree_payout.fundsource_id') ?: env('CASHFREE_PAYOUT_FUNDSOURCE_ID', 'LEGACY CARRY PRIVATE LIMITED'));

        $defaultUrl = in_array($this->environment, ['production', 'live'])
            ? 'https://payout-api.cashfree.com'
            : 'https://payout-gamma.cashfree.com';

        $this->baseUrl = (string) ($config['base_url'] ?? config('services.cashfree_payout.base_url') ?: env('CASHFREE_PAYOUT_BASE_URL', $defaultUrl));
    }

    /**
     * Get Client ID
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * Get Environment
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Get Base URL
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Get last auth error message
     */
    public function getLastAuthError(): ?string
    {
        return $this->lastAuthError;
    }

    /**
     * Test connection and authorization with Cashfree Payout API
     */
    public function testConnection(): array
    {
        if (empty($this->clientId) || empty($this->clientSecret)) {
            return [
                'success' => false,
                'status' => 'ERROR',
                'subCode' => 'MISSING_CREDENTIALS',
                'message' => 'Cashfree Payout Client ID or Secret is not configured in settings or .env.',
                'token' => null,
            ];
        }

        try {
            $response = Http::timeout(15)->withHeaders([
                'X-Client-Id' => $this->clientId,
                'X-Client-Secret' => $this->clientSecret,
                'Content-Type' => 'application/json',
            ])->post(rtrim($this->baseUrl, '/') . '/payout/v1/authorize');

            $body = $response->json() ?? [];
            $status = $body['status'] ?? ($response->successful() ? 'SUCCESS' : 'ERROR');
            $subCode = (string) ($body['subCode'] ?? $response->status());
            $message = $body['message'] ?? ($response->successful() ? 'Authorized successfully' : 'Authorization failed');

            if ($response->successful() && $status === 'SUCCESS' && !empty($body['data']['token'])) {
                return [
                    'success' => true,
                    'status' => 'SUCCESS',
                    'subCode' => $subCode,
                    'message' => 'Connection verified successfully! Live Cashfree Payout API is working.',
                    'token' => $body['data']['token'],
                    'expiry' => $body['data']['expiry'] ?? null,
                ];
            }

            return [
                'success' => false,
                'status' => $status,
                'subCode' => $subCode,
                'message' => $message,
                'token' => null,
                'raw' => $body,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'EXCEPTION',
                'subCode' => '500',
                'message' => 'Connection error: ' . $e->getMessage(),
                'token' => null,
            ];
        }
    }

    /**
     * Get Auth Bearer Token from Cashfree Payouts API
     */
    public function getAuthToken(): ?string
    {
        $this->lastAuthError = null;

        if (empty($this->clientId) || empty($this->clientSecret)) {
            $this->lastAuthError = 'Missing client ID or secret in Cashfree Payout configuration.';
            Log::error('Cashfree Payout: Missing client ID or secret');
            return null;
        }

        try {
            $response = Http::timeout(15)->withHeaders([
                'X-Client-Id' => $this->clientId,
                'X-Client-Secret' => $this->clientSecret,
                'Content-Type' => 'application/json',
            ])->post(rtrim($this->baseUrl, '/') . '/payout/v1/authorize');

            $body = $response->json() ?? [];

            if ($response->successful()) {
                if (($body['status'] ?? '') === 'SUCCESS' && !empty($body['data']['token'])) {
                    return $body['data']['token'];
                }
            }

            $errMsg = $body['message'] ?? 'Authorization failed (' . $response->status() . ')';
            $this->lastAuthError = $errMsg;

            Log::error('Cashfree Payout Authorization failed', [
                'status' => $response->status(),
                'body' => $body ?: $response->body(),
            ]);

            return null;

        } catch (\Exception $e) {
            $this->lastAuthError = $e->getMessage();
            Log::error('Cashfree Payout Auth Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Initiate Direct Payout Transfer for a Withdrawal record using Cashfree Payouts v2
     */
    public function initiateTransfer(Withdrawal $withdrawal): array
    {
        $user = $withdrawal->user;
        $details = is_array($withdrawal->payout_details) ? $withdrawal->payout_details : [];
        $type = strtolower($withdrawal->payout_type);

        $transferId = 'TR_' . $withdrawal->id . '_' . Str::upper(Str::random(6));

        $phone = !empty($user?->phone) ? preg_replace('/[^0-9]/', '', $user->phone) : '9999999999';
        if (strlen($phone) < 10) {
            $phone = '9999999999';
        }

        $email = (!empty($user?->email) && filter_var($user->email, FILTER_VALIDATE_EMAIL))
            ? $user->email
            : 'user' . ($user?->id ?? '1') . '@legacycarry.com';

        $userName = $details['account_holder_name'] ?? ($user?->name ?? 'Recipient');

        $beneficiaryDetails = [
            'beneficiary_name' => $userName,
            'beneficiary_contact_details' => [
                'beneficiary_phone' => $phone,
                'beneficiary_email' => $email,
            ],
        ];

        if ($type === 'upi') {
            $transferMode = 'upi';
            $beneficiaryDetails['beneficiary_instrument_details'] = [
                'vpa' => $details['upi_id'] ?? ($details['vpa'] ?? ''),
            ];
        } else {
            $transferMode = 'banktransfer';
            $beneficiaryDetails['beneficiary_instrument_details'] = [
                'bank_account_number' => (string) ($details['account_number'] ?? ''),
                'bank_ifsc' => strtoupper((string) ($details['ifsc'] ?? '')),
            ];
        }

        $payload = [
            'transfer_id' => $transferId,
            'transfer_amount' => (float) number_format((float) $withdrawal->amount, 2, '.', ''),
            'transfer_mode' => $transferMode,
            'transfer_remarks' => 'Payout for ' . ($withdrawal->withdrawal_id ?? 'Withdrawal #' . $withdrawal->id),
            'beneficiary_details' => $beneficiaryDetails,
        ];

        if (!empty($this->fundsourceId)) {
            $payload['fundsource_id'] = $this->fundsourceId;
        }

        $v2Url = (in_array($this->environment, ['production', 'live']))
            ? 'https://api.cashfree.com/payout/transfers'
            : 'https://sandbox.cashfree.com/payout/transfers';

        try {
            $response = Http::timeout(25)->withHeaders([
                'x-api-version' => '2024-01-01',
                'x-client-id' => $this->clientId,
                'x-client-secret' => $this->clientSecret,
                'Content-Type' => 'application/json',
            ])->post($v2Url, $payload);

            $body = $response->json() ?? [];

            Log::info('Cashfree v2 Transfer Response', [
                'withdrawal_id' => $withdrawal->id,
                'payload' => $payload,
                'response' => $body,
            ]);

            $status = strtoupper($body['status'] ?? ($body['status_code'] ?? ''));
            $cfTransferId = $body['cf_transfer_id'] ?? null;

            if ($response->successful() && in_array($status, ['SUCCESS', 'RECEIVED', 'PENDING', '200', '201'])) {
                $statusName = ($status === 'SUCCESS') ? 'SUCCESS' : 'PENDING';

                return [
                    'success' => true,
                    'status' => $statusName,
                    'transfer_id' => $transferId,
                    'reference_id' => $cfTransferId,
                    'message' => $body['status_description'] ?? 'Payout initiated successfully via Cashfree v2',
                    'raw' => $body,
                ];
            }

            // Failure response
            $errorMessage = $body['status_description'] ?? ($body['message'] ?? 'Transfer request rejected by Cashfree Payout gateway');
            if (!empty($body['status_code'])) {
                $errorMessage .= ' (' . $body['status_code'] . ')';
            }

            return [
                'success' => false,
                'status' => 'FAILED',
                'transfer_id' => $transferId,
                'reference_id' => $cfTransferId,
                'message' => $errorMessage,
                'raw' => $body,
            ];

        } catch (\Exception $e) {
            Log::error('Cashfree Payout v2 Transfer Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 'FAILED',
                'transfer_id' => $transferId,
                'reference_id' => null,
                'message' => 'Exception during Cashfree payout: ' . $e->getMessage(),
                'raw' => ['error' => $e->getMessage()],
            ];
        }
    }

    /**
     * Direct test payout to any UPI ID or Bank Account using v2
     */
    public function testDirectTransfer(string $payoutType, array $accountDetails, float $amount = 1.00, string $remarks = 'Test Payout'): array
    {
        $transferId = 'TEST_' . time() . '_' . Str::upper(Str::random(4));
        $type = strtolower($payoutType);

        $phone = $accountDetails['phone'] ?? '9999999999';
        if (strlen($phone) < 10) {
            $phone = '9999999999';
        }

        $beneficiaryDetails = [
            'beneficiary_name' => $accountDetails['name'] ?? 'Test Recipient',
            'beneficiary_contact_details' => [
                'beneficiary_phone' => $phone,
                'beneficiary_email' => $accountDetails['email'] ?? 'test@legacycarry.com',
            ],
        ];

        if ($type === 'upi') {
            $transferMode = 'upi';
            $beneficiaryDetails['beneficiary_instrument_details'] = [
                'vpa' => $accountDetails['upi_id'] ?? ($accountDetails['vpa'] ?? ''),
            ];
        } else {
            $transferMode = 'banktransfer';
            $beneficiaryDetails['beneficiary_instrument_details'] = [
                'bank_account_number' => (string) ($accountDetails['account_number'] ?? ''),
                'bank_ifsc' => strtoupper((string) ($accountDetails['ifsc'] ?? '')),
            ];
        }

        $payload = [
            'transfer_id' => $transferId,
            'transfer_amount' => (float) number_format($amount, 2, '.', ''),
            'transfer_mode' => $transferMode,
            'transfer_remarks' => $remarks,
            'beneficiary_details' => $beneficiaryDetails,
        ];

        if (!empty($this->fundsourceId)) {
            $payload['fundsource_id'] = $this->fundsourceId;
        }

        $v2Url = (in_array($this->environment, ['production', 'live']))
            ? 'https://api.cashfree.com/payout/transfers'
            : 'https://sandbox.cashfree.com/payout/transfers';

        try {
            $response = Http::timeout(25)->withHeaders([
                'x-api-version' => '2024-01-01',
                'x-client-id' => $this->clientId,
                'x-client-secret' => $this->clientSecret,
                'Content-Type' => 'application/json',
            ])->post($v2Url, $payload);

            $body = $response->json() ?? [];
            $status = strtoupper($body['status'] ?? ($body['status_code'] ?? ''));

            if ($response->successful() && in_array($status, ['SUCCESS', 'RECEIVED', 'PENDING', '200', '201'])) {
                $cfTransferId = $body['cf_transfer_id'] ?? null;
                $msg = $body['status_description'] ?? 'Payout transferred successfully';

                // Check terminal status after 2 seconds
                sleep(2);
                $statusCheck = $this->getTransferStatus($transferId);
                if ($statusCheck['success'] && !empty($statusCheck['data'])) {
                    $detailData = $statusCheck['data'];
                    $terminalStatus = strtoupper($detailData['status'] ?? ($detailData['status_code'] ?? ''));
                    if (!empty($terminalStatus)) {
                        $isSuccess = in_array($terminalStatus, ['SUCCESS', 'PENDING', 'RECEIVED']);
                        return [
                            'success' => $isSuccess,
                            'status' => $terminalStatus,
                            'status_code' => $detailData['status_code'] ?? null,
                            'transfer_id' => $transferId,
                            'reference_id' => $detailData['cf_transfer_id'] ?? $cfTransferId,
                            'message' => $detailData['status_description'] ?? $msg,
                            'raw' => $detailData,
                        ];
                    }
                }

                return [
                    'success' => true,
                    'status' => $status,
                    'transfer_id' => $transferId,
                    'reference_id' => $cfTransferId,
                    'message' => $msg,
                    'raw' => $body,
                ];
            }

            return [
                'success' => false,
                'status' => 'FAILED',
                'transfer_id' => $transferId,
                'reference_id' => $body['cf_transfer_id'] ?? null,
                'message' => ($body['status_description'] ?? ($body['message'] ?? 'Failed')) . (isset($body['status_code']) ? ' (' . $body['status_code'] . ')' : ''),
                'raw' => $body,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'EXCEPTION',
                'message' => $e->getMessage(),
                'raw' => ['error' => $e->getMessage()],
            ];
        }
    }

    /**
     * Check status of a transfer by Transfer ID using v2
     */
    public function getTransferStatus(string $transferId): array
    {
        $v2Url = (in_array($this->environment, ['production', 'live']))
            ? 'https://api.cashfree.com/payout/transfers'
            : 'https://sandbox.cashfree.com/payout/transfers';

        try {
            $response = Http::timeout(15)->withHeaders([
                'x-api-version' => '2024-01-01',
                'x-client-id' => $this->clientId,
                'x-client-secret' => $this->clientSecret,
            ])->get($v2Url, [
                'transfer_id' => $transferId,
            ]);

            return [
                'success' => $response->successful(),
                'data' => $response->json() ?? [],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

