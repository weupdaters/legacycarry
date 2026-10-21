<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CashfreeTestController extends Controller
{
    /**
     * Show the Cashfree Test Web Page
     */
    public function index()
    {
        $cfConfig = Setting::getCashfreeConfig();

        return view('cashfree_tester', [
            'config' => $cfConfig,
            'defaultAppId' => $cfConfig['app_id'] ?? env('CASHFREE_APP_ID', ''),
            'defaultSecret' => $cfConfig['secret_key'] ?? env('CASHFREE_SECRET_KEY', ''),
            'defaultEnv' => $cfConfig['environment'] ?? 'sandbox',
        ]);
    }

    /**
     * Test Cashfree API and create order
     */
    public function testOrder(Request $request)
    {
        $request->validate([
            'environment' => 'required|in:sandbox,production',
            'app_id' => 'required|string',
            'secret_key' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string',
        ]);

        $env = $request->input('environment');
        $appId = trim($request->input('app_id'));
        $secretKey = trim($request->input('secret_key'));
        $amount = (float) $request->input('amount');
        $customerName = trim($request->input('customer_name'));
        $customerEmail = trim($request->input('customer_email'));
        $customerPhone = preg_replace('/[^0-9]/', '', $request->input('customer_phone'));
        if (strlen($customerPhone) < 10) {
            $customerPhone = '9999999999';
        }

        $baseUrl = ($env === 'production') 
            ? 'https://api.cashfree.com/pg/orders' 
            : 'https://sandbox.cashfree.com/pg/orders';

        $checkoutBaseUrl = ($env === 'production')
            ? 'https://payments.cashfree.com/pg/view'
            : 'https://sandbox.cashfree.com/pg/view';

        $orderId = 'TEST_' . strtoupper(Str::random(10));
        $customerId = 'CUST_' . strtoupper(Str::random(6));
        $returnUrl = url('/cashfree-return?order_id={order_id}');
        if ($env === 'production' && str_starts_with($returnUrl, 'http://')) {
            $returnUrl = preg_replace('/^http:/i', 'https:', $returnUrl);
        }

        $payload = [
            'order_id' => $orderId,
            'order_amount' => $amount,
            'order_currency' => 'INR',
            'customer_details' => [
                'customer_id' => $customerId,
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'customer_phone' => $customerPhone,
            ],
            'order_meta' => [
                'return_url' => $returnUrl,
            ],
            'order_note' => 'Demo website test payment',
        ];

        $headers = [
            'x-client-id' => $appId,
            'x-client-secret' => $secretKey,
            'x-api-version' => '2023-08-01',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        try {
            $startTime = microtime(true);
            $response = Http::withHeaders($headers)
                ->timeout(15)
                ->post($baseUrl, $payload);
            $durationMs = round((microtime(true) - $startTime) * 1000, 2);

            $statusCode = $response->status();
            $body = $response->json();

            $paymentSessionId = $body['payment_session_id'] ?? null;
            $paymentLink = $body['payment_link'] ?? ($paymentSessionId ? $checkoutBaseUrl . '/' . $paymentSessionId : null);

            $analysis = $this->analyzeResponse($statusCode, $body, $env, $appId);

            return response()->json([
                'success' => $response->successful(),
                'status_code' => $statusCode,
                'duration_ms' => $durationMs,
                'endpoint' => $baseUrl,
                'environment' => $env,
                'request_headers' => [
                    'x-client-id' => substr($appId, 0, 8) . '...' . substr($appId, -4),
                    'x-client-secret' => 'cfsk_***(hidden)',
                    'x-api-version' => '2023-08-01',
                    'Content-Type' => 'application/json',
                ],
                'request_payload' => $payload,
                'response_body' => $body,
                'payment_session_id' => $paymentSessionId,
                'payment_link' => $paymentLink,
                'analysis' => $analysis,
            ], $statusCode >= 200 && $statusCode < 300 ? 200 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status_code' => 500,
                'error' => $e->getMessage(),
                'analysis' => [
                    'type' => 'connection_error',
                    'title' => 'Network / Server Error',
                    'details' => 'Could not connect to Cashfree endpoint: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Provide diagnosis for common Cashfree errors
     */
    private function analyzeResponse(int $statusCode, ?array $body, string $env, string $appId): array
    {
        if ($statusCode >= 200 && $statusCode < 300) {
            return [
                'type' => 'success',
                'title' => '✅ Order Created Successfully!',
                'details' => 'Your Cashfree API credentials and environment mode are working perfectly. You can proceed to test the checkout link.',
            ];
        }

        $message = strtolower($body['message'] ?? '');
        $code = strtolower($body['code'] ?? '');

        if (str_contains($message, 'transactions are not enabled') || str_contains($code, 'request_failed')) {
            $isTestKeyOnProd = ($env === 'production' && str_starts_with(strtoupper($appId), 'TEST'));
            $isProdKeyOnTest = ($env === 'sandbox' && !str_starts_with(strtoupper($appId), 'TEST') && !str_starts_with(strtoupper($appId), 'CF'));

            return [
                'type' => 'gateway_disabled',
                'title' => '⚠️ Transactions Not Enabled on this Account/Mode',
                'details' => $isTestKeyOnProd 
                    ? 'You are using TEST/Sandbox credentials on the PRODUCTION environment. Switch Environment to "Sandbox (Testing)" or use Live credentials.'
                    : ($isProdKeyOnTest 
                        ? 'You are using Live credentials on the SANDBOX environment. Switch Environment to "Production (Live)" or use Sandbox credentials.'
                        : 'Either your Cashfree KYC is pending verification on the Live merchant portal, or the Payment Gateway product is not activated on your Cashfree dashboard.'),
                'recommendation' => [
                    '1. For testing: Make sure Environment is set to "Sandbox (Testing)" and use keys from https://sandbox.cashfree.com',
                    '2. For production: Complete KYC and activate Payment Gateway on https://merchant.cashfree.com',
                    '3. Ensure the keys were generated under "Payment Gateway" -> "Developers" -> "API Keys" (not Payouts).',
                ]
            ];
        }

        if (str_contains($message, 'authentication failed') || str_contains($message, 'invalid client_id') || $statusCode === 401) {
            return [
                'type' => 'auth_failed',
                'title' => '❌ Authentication Failed (Invalid App ID or Secret)',
                'details' => 'Cashfree could not authenticate your App ID and Secret Key. Please double check that there are no extra spaces.',
                'recommendation' => [
                    '1. Verify the App ID and Secret Key match the selected environment (' . strtoupper($env) . ').',
                    '2. Regenerate API keys in Cashfree Dashboard if necessary.',
                ]
            ];
        }

        return [
            'type' => 'other_error',
            'title' => 'Error from Cashfree API',
            'details' => $body['message'] ?? 'Cashfree returned an error code: ' . $statusCode,
            'recommendation' => [
                'Review the response payload below for detailed error parameters.',
            ]
        ];
    }

    /**
     * Payment Return Callback page
     */
    public function returnHandler(Request $request)
    {
        $orderId = $request->query('order_id');

        return view('cashfree_return', [
            'order_id' => $orderId,
        ]);
    }
}
