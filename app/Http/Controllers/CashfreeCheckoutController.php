<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CashfreeCheckoutController extends Controller
{
    /**
     * Hosted web checkout page using Cashfree JS SDK
     */
    public function checkout($paymentSessionId)
    {
        $cfConfig = Setting::getCashfreeConfig();
        $mode = $cfConfig['is_live'] ? 'production' : 'sandbox';

        return view('cashfree.checkout', [
            'paymentSessionId' => $paymentSessionId,
            'mode'             => $mode,
            'isLive'           => $cfConfig['is_live'],
        ]);
    }

    /**
     * Cashfree return redirect page after payment
     */
    public function callback(Request $request)
    {
        $orderId = $request->query('order_id');

        if (!$orderId) {
            return view('cashfree.status', [
                'status'  => 'error',
                'message' => 'No Order ID provided in callback.',
                'order'   => null,
            ]);
        }

        $cfConfig = Setting::getCashfreeConfig();

        try {
            // Verify payment status from Cashfree API
            $response = Http::withHeaders([
                'x-client-id'     => $cfConfig['app_id'],
                'x-client-secret' => $cfConfig['secret_key'],
                'x-api-version'   => '2022-09-01',
            ])->get($cfConfig['base_url'] . '/' . $orderId . '/payments');

            $paymentData = $response->json();
            $isSuccess = false;
            $paymentDetails = null;

            if ($response->successful() && is_array($paymentData)) {
                foreach ($paymentData as $p) {
                    if (isset($p['payment_status']) && strtoupper($p['payment_status']) === 'SUCCESS') {
                        $isSuccess = true;
                        $paymentDetails = $p;
                        break;
                    }
                }
            }

            // Update local payment record if found
            $payment = Payment::where('payment_id', $orderId)->first();
            if ($payment && $isSuccess) {
                $payment->update([
                    'status'            => 'success',
                    'paid_at'           => now(),
                    'payment_reference' => $paymentDetails['cf_payment_id'] ?? $payment->payment_reference,
                    'payment_method'    => $paymentDetails['payment_method'] ?? $payment->payment_method,
                ]);
            }

            return view('cashfree.status', [
                'status'         => $isSuccess ? 'success' : 'pending',
                'message'        => $isSuccess ? 'Payment completed successfully!' : 'Payment is pending or was cancelled.',
                'orderId'        => $orderId,
                'paymentDetails' => $paymentDetails,
                'payment'        => $payment,
            ]);

        } catch (\Exception $e) {
            Log::error('Cashfree callback error: ' . $e->getMessage());

            return view('cashfree.status', [
                'status'  => 'error',
                'message' => 'Error verifying payment: ' . $e->getMessage(),
                'orderId' => $orderId,
                'payment' => null,
            ]);
        }
    }

    /**
     * Cashfree Live / Sandbox Gateway Tester page
     */
    public function tester()
    {
        $cfConfig = Setting::getCashfreeConfig();
        return view('cashfree.tester', [
            'cfConfig' => $cfConfig,
        ]);
    }

    /**
     * Create a test order via Ajax
     */
    public function createTestOrder(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'phone'  => 'nullable|string',
            'email'  => 'nullable|email',
            'name'   => 'nullable|string',
        ]);

        $cfConfig = Setting::getCashfreeConfig();

        if (empty($cfConfig['app_id']) || empty($cfConfig['secret_key'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cashfree API credentials are not configured.',
            ], 400);
        }

        $orderId = 'TEST_' . time() . '_' . Str::upper(Str::random(4));
        $returnUrl = url('/cashfree-return?order_id={order_id}');
        if ($cfConfig['is_live'] && str_starts_with($returnUrl, 'http://')) {
            $returnUrl = preg_replace('/^http:/i', 'https:', $returnUrl);
        }

        try {
            $response = Http::withHeaders([
                'x-client-id'     => $cfConfig['app_id'],
                'x-client-secret' => $cfConfig['secret_key'],
                'x-api-version'   => '2022-09-01',
                'Content-Type'    => 'application/json',
            ])->post($cfConfig['base_url'], [
                'order_id'       => $orderId,
                'order_amount'   => (float)$request->input('amount', 1),
                'order_currency' => 'INR',
                'customer_details' => [
                    'customer_id'    => 'CUST_' . time(),
                    'customer_name'  => $request->input('name', 'Test User'),
                    'customer_email' => $request->input('email', 'test@example.com'),
                    'customer_phone' => preg_replace('/[^0-9]/', '', $request->input('phone', '9999999999')) ?: '9999999999',
                ],
                'order_meta' => [
                    'return_url' => $returnUrl,
                ],
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cashfree API Error: ' . $response->body(),
                    'data'    => $response->json(),
                ], 400);
            }

            $data = $response->json();
            $paymentSessionId = $data['payment_session_id'] ?? null;
            $checkoutUrl = url('/cashfree-checkout/' . $paymentSessionId);

            return response()->json([
                'success'            => true,
                'order_id'           => $orderId,
                'payment_session_id' => $paymentSessionId,
                'checkout_url'       => $checkoutUrl,
                'raw_response'       => $data,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
            ], 500);
        }
    }
}
