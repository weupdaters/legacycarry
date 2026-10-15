<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\ResidentAssignment;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Str;

class SocietyNotificationService
{
    /**
     * Send notification to a single user (Database + FCM Push).
     */
    public static function notifyUser(User $user, string $title, string $message, string $type, array $meta = [], ?string $image = null): void
    {
        // 1. AppNotification (notifications table)
        try {
            AppNotification::create([
                'title' => $title,
                'description' => $message,
                'image' => $image,
                'sent_to' => $user->id,
                'meta' => array_merge($meta, [
                    'type' => $type,
                    'timestamp' => now()->toIso8601String(),
                ]),
            ]);
        } catch (\Throwable $e) {
            \Log::error('SocietyNotificationService AppNotification error: ' . $e->getMessage());
        }

        // 2. UserNotification table (if active)
        try {
            UserNotification::create([
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'image' => $image,
                'type' => $type,
            ]);
        } catch (\Throwable $e) {
            // Optional table fallback
        }

        // 3. FCM Push Notification
        if (!empty($user->fcm_token) && function_exists('send_push_notification')) {
            try {
                send_push_notification(
                    $user->fcm_token,
                    $title,
                    Str::limit($message, 150),
                    array_merge($meta, ['type' => $type]),
                    $image
                );
            } catch (\Throwable $e) {
                \Log::warning('SocietyNotificationService FCM error: ' . $e->getMessage());
            }
        }
    }

    /**
     * Notify all residents of a specific society.
     */
    public static function notifySocietyResidents(int $societyId, string $title, string $message, string $type, array $meta = [], ?string $image = null): void
    {
        $residents = User::where(function ($q) use ($societyId) {
            $q->where('user_society_id', $societyId)
              ->orWhereHas('details', function ($dq) use ($societyId) {
                  $dq->where('society_id', $societyId);
              })
              ->orWhereHas('residentAssignment', function ($rq) use ($societyId) {
                  $rq->where('society_id', $societyId);
              });
        })->where('role', 'customer')->get();

        foreach ($residents as $resident) {
            self::notifyUser($resident, $title, $message, $type, $meta, $image);
        }
    }

    /**
     * Notify society admins or super admins.
     */
    public static function notifyAdmins(int $societyId, string $title, string $message, string $type, array $meta = []): void
    {
        $admins = User::where('role', 'super_admin')->get();

        foreach ($admins as $admin) {
            self::notifyUser($admin, $title, $message, $type, $meta);
        }
    }
}
