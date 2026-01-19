<?php

namespace App\Helpers;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FCMHelper
{
    public static function send(string $token, string $title, string $body, array $data = [])
    {
        try {
            $config = config('firebase');

            if (
                empty($config['project_id']) ||
                empty($config['client_email']) ||
                empty($config['private_key'])
            ) {
                Log::error('Firebase config missing');
                return false;
            }

            // 🔐 OAuth access token (FCM HTTP v1)
            $credentials = new ServiceAccountCredentials(
                'https://www.googleapis.com/auth/firebase.messaging',
                [
                    'type' => 'service_account',
                    'client_email' => $config['client_email'],
                    'private_key' => str_replace('\\n', "\n", $config['private_key']),
                ]
            );

            $tokenData = $credentials->fetchAuthToken();
            $accessToken = $tokenData['access_token'] ?? null;

            if (!$accessToken) {
                Log::error('FCM access token generation failed', $tokenData);
                return false;
            }

            $url = 'https://fcm.googleapis.com/v1/projects/217124623975/messages:send';

            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $data,
                    'android' => [
                        'priority' => 'high',
                    ],
                ],
            ];



            $response = Http::withToken($accessToken)
                ->post($url, $payload);

            if ($response->failed()) {
                Log::error('FCM send failed', [
                    'response' => $response->body(),
                ]);
                return false;
            }

            return $response->json();

        } catch (\Throwable $e) {
            Log::error('FCM exception', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
