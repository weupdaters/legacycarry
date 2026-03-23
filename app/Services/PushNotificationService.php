<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class PushNotificationService
{
    public static function sendToUser($userId, $title, $body, $data = [])
    {
        $tokens = User::where('id', $userId)->pluck('fcm_token')->toArray();

        if (empty($tokens)) {
            return false; // No FCM token
        }

        $payload = [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
        ];

        $serverKey = env('FCM_SERVER_KEY');

        return Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', $payload);
    }
}
