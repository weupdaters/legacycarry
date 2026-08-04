<?php

namespace App\Helpers;

use App\Models\User;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FCMHelper
{
    /**
     * Get OAuth2 Access Token for Firebase HTTP v1 API
     */
    public static function getAccessToken(): ?string
    {
        try {
            $jsonPath = storage_path('app/firebase/legacycarry-firebase-adminsdk-fbsvc-411308132280.json');

            if (file_exists($jsonPath)) {
                $credentials = json_decode(file_get_contents($jsonPath), true);
                $client = new ServiceAccountCredentials(
                    'https://www.googleapis.com/auth/firebase.messaging',
                    $credentials
                );
                $tokenData = $client->fetchAuthToken();
                return $tokenData['access_token'] ?? null;
            }

            $config = config('firebase');
            if (!empty($config['client_email']) && !empty($config['private_key'])) {
                $credentials = new ServiceAccountCredentials(
                    'https://www.googleapis.com/auth/firebase.messaging',
                    [
                        'type' => 'service_account',
                        'client_email' => $config['client_email'],
                        'private_key' => str_replace('\\n', "\n", $config['private_key']),
                    ]
                );
                $tokenData = $credentials->fetchAuthToken();
                return $tokenData['access_token'] ?? null;
            }

            Log::error('FCM credentials not found in json file or config');
            return null;
        } catch (\Throwable $e) {
            Log::error('FCM access token generation exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get Firebase Project ID
     */
    public static function getProjectId(): string
    {
        $jsonPath = storage_path('app/firebase/legacycarry-firebase-adminsdk-fbsvc-411308132280.json');
        if (file_exists($jsonPath)) {
            $credentials = json_decode(file_get_contents($jsonPath), true);
            if (!empty($credentials['project_id'])) {
                return $credentials['project_id'];
            }
        }

        return config('firebase.project_id') ?: env('FIREBASE_PROJECT_ID', 'legacycarry');
    }

    /**
     * Send push notification via FCM HTTP v1 API
     *
     * @param string|array|User|int $tokens FCM token string, array of FCM tokens, User model, or User ID
     * @param string $title
     * @param string $body
     * @param array $data
     * @param string|null $image
     * @return array|bool Returns response array on success or false on failure
     */
    public static function send($tokens, string $title, string $body, array $data = [], ?string $image = null)
    {
        // Resolve tokens into an array of string tokens
        $deviceTokens = self::resolveTokens($tokens);

        if (empty($deviceTokens)) {
            Log::warning('FCM send skipped: No valid tokens provided');
            return false;
        }

        $accessToken = self::getAccessToken();
        if (!$accessToken) {
            Log::error('FCM send failed: Unable to fetch access token');
            return false;
        }

        $projectId = self::getProjectId();
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        // Format data payload: FCM HTTP v1 requires all values to be strings
        $formattedData = [];
        foreach ($data as $key => $val) {
            if ($val === null) continue;
            $formattedData[(string) $key] = is_array($val) || is_object($val) ? json_encode($val) : (string) $val;
        }

        $results = [];
        $hasSuccess = false;

        foreach ($deviceTokens as $token) {
            if (!is_string($token) || trim($token) === '') {
                continue;
            }

            $notificationPayload = [
                'title' => $title,
                'body' => $body,
            ];

            $messagePayload = [
                'token' => trim($token),
                'notification' => $notificationPayload,
                'data' => $formattedData,
                'android' => [
                    'priority' => 'high',
                ],
            ];

            if (!empty($image)) {
                $notificationPayload['image'] = $image;
                $formattedData['image'] = $image;
                $messagePayload['notification'] = $notificationPayload;
                $messagePayload['data'] = $formattedData;
                $messagePayload['android']['notification'] = ['image' => $image];
                $messagePayload['apns'] = [
                    'payload' => [
                        'aps' => ['mutable-content' => 1],
                    ],
                    'fcm_options' => ['image' => $image],
                ];
            }

            try {
                $response = Http::withToken($accessToken)->post($url, [
                    'message' => $messagePayload,
                ]);

                if ($response->successful()) {
                    $hasSuccess = true;
                    $resJson = $response->json();
                    $results[] = [
                        'token' => $token,
                        'status' => 'success',
                        'response' => $resJson,
                    ];
                } else {
                    $responseBody = $response->json();
                    Log::error('FCM send failed for token: ' . $token, [
                        'status' => $response->status(),
                        'response' => $response->body(),
                    ]);

                    // Check for unregistered/invalid token error code
                    $errorCode = null;
                    if (isset($responseBody['error']['details'])) {
                        foreach ($responseBody['error']['details'] as $detail) {
                            if (isset($detail['errorCode'])) {
                                $errorCode = $detail['errorCode'];
                                break;
                            }
                        }
                    }

                    if ($response->status() === 404 || $errorCode === 'UNREGISTERED' || $errorCode === 'INVALID_ARGUMENT') {
                        User::where('fcm_token', $token)->update(['fcm_token' => null]);
                    }

                    $results[] = [
                        'token' => $token,
                        'status' => 'error',
                        'http_code' => $response->status(),
                        'error' => $responseBody,
                    ];
                }
            } catch (\Throwable $e) {
                Log::error('FCM exception for token: ' . $token . ' - ' . $e->getMessage());
                $results[] = [
                    'token' => $token,
                    'status' => 'exception',
                    'error' => $e->getMessage(),
                ];
            }
        }

        if (count($deviceTokens) === 1) {
            return $results[0]['status'] === 'success' ? $results[0]['response'] : false;
        }

        return [
            'success' => $hasSuccess,
            'results' => $results,
        ];
    }

    /**
     * Resolve input into an array of string device tokens
     */
    private static function resolveTokens($tokens): array
    {
        if (empty($tokens)) {
            return [];
        }

        if ($tokens instanceof User) {
            return $tokens->fcm_token ? [$tokens->fcm_token] : [];
        }

        if (is_numeric($tokens)) {
            $user = User::find((int) $tokens);
            return ($user && $user->fcm_token) ? [$user->fcm_token] : [];
        }

        if (is_string($tokens)) {
            return [trim($tokens)];
        }

        if (is_array($tokens)) {
            $resolved = [];
            foreach ($tokens as $item) {
                if ($item instanceof User && $item->fcm_token) {
                    $resolved[] = trim($item->fcm_token);
                } elseif (is_numeric($item)) {
                    $user = User::find((int) $item);
                    if ($user && $user->fcm_token) {
                        $resolved[] = trim($user->fcm_token);
                    }
                } elseif (is_string($item) && trim($item) !== '') {
                    $resolved[] = trim($item);
                }
            }
            return array_values(array_unique(array_filter($resolved)));
        }

        return [];
    }
}

