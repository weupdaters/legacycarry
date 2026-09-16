<?php
namespace App\Helpers;

use App\Models\AppNotification;
use App\Models\User;
use App\Models\UserNotification;
use App\Helpers\FCMHelper;

class Notify {
  public static function to($userId, $title, $message, $data = []) {
    $image = $data['image'] ?? $data['image_url'] ?? null;

    // 1. Save to UserNotification
    try {
        UserNotification::create([
          'user_id' => $userId,
          'title'   => $title,
          'message' => $message,
          'image'   => $image,
          'type'    => $data['type'] ?? ($data['pass_id'] ?? false ? 'visitor_pass' : 'general'),
          'pass_id' => $data['pass_id'] ?? null
        ]);
    } catch (\Throwable $e) {
        // Optional
    }

    // 2. Save to AppNotification (notifications table used by API)
    try {
        AppNotification::create([
            'title'       => $title,
            'description' => $message,
            'image'       => $image,
            'sent_to'     => $userId,
            'meta'        => [
                'type'      => $data['type'] ?? ($data['pass_id'] ?? false ? 'visitor_pass' : 'general'),
                'pass_id'   => $data['pass_id'] ?? null,
                'image_url' => $image,
                'data'      => $data,
            ],
        ]);
    } catch (\Throwable $e) {
        \Log::error('Notify::to AppNotification error: ' . $e->getMessage());
    }

    // 3. Send FCM push notification if token exists
    $user = User::find($userId);
    if ($user && $user->fcm_token) {
        FCMHelper::send($user->fcm_token, $title, $message, $data, $image);
    }
  }
}

