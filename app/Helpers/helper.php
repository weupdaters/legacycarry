<?php

use App\Models\ResidentAssignment;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

if (!function_exists('setting')) {

    /**
     * Get setting value by:
     * - key
     * - or group + key: setting('branding.logo_light')
     */
    function setting($key, $default = null)
    {
        // Check if group included → "branding.logo_light"
        if (str_contains($key, '.')) {
            [$group, $actualKey] = explode('.', $key, 2);

            return Setting::where('group', $group)
                ->where('key', $actualKey)
                ->value('value') ?? $default;
        }

        // Normal single key lookup
        return Setting::getValue($key, $default);
    }
}

if (!function_exists('getUserDatails')) {
    function getUserDatails($userId)
    {
        $user = Userdetail::with('society')
            ->where('user_id', $userId)
            ->first();

        if (!$user) {
            return null;
        }

        return $user;
    }
}

function assingedSocietyName($userId)
{

    $userDetails = ResidentAssignment::where('user_id', $userId)->with('society')->first();
    if ($userDetails && $userDetails->society) {
        return $userDetails->society->name;
    }

    return null;
}

if (!function_exists('api_auth')) {
    function api_auth($key = null, $default = null)
    {
        $user = request()->user(); // auto checks Bearer token

        // Not authenticated
        if (!$user) {
            return [
                'authenticated' => false,
                'user' => null,
                'value' => $default,
            ];
        }

        return [
            'authenticated' => true,
            'user' => $user,
            'value' => $key ? data_get($user, $key, $default) : $user,
        ];
    }
}



if (!function_exists('getSocietyId')) {
    function getSocietyId()
    {
        $userId = auth()->id();

        if (!$userId) {
            return null;
        }

        $userDetail = UserDetail::where('user_id', $userId)->first();

        return $userDetail?->society_id;
    }
}


if (!function_exists('has_permission')) {

    function has_permission($permissionCode)
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        // Only Super admin bypasses everything
        if ($user->role === 'super_admin') {
            return true;
        }

        return DB::table('permission_user')
            ->join('permissions', 'permissions.id', '=', 'permission_user.permission_id')
            ->where('permission_user.user_id', $user->id)
            ->where('permissions.permission_code', $permissionCode)
            ->exists();
    }

}
if (!function_exists('send_push_notification')) {
    /**
     * Send push notification using Firebase
     * @param array|string $tokens
     * @param string $title
     * @param string $body
     * @param array $data
     */
    function send_push_notification($tokens, $title, $body, $data = [])
    {
        if (empty($tokens)) return false;

        $messaging = app('firebase.messaging');
        $tokens = is_array($tokens) ? $tokens : [$tokens];
        
        // Remove empty or null tokens
        $tokens = array_filter($tokens);
        if (empty($tokens)) return false;

        try {
            $notification = \Kreait\Firebase\Messaging\Notification::create($title, $body);
            

            // Since we use Multicast for multiple tokens
            $cloudMessage = \Kreait\Firebase\Messaging\CloudMessage::new()
                ->withNotification($notification)
                ->withData($data);

            $report = $messaging->sendMulticast($cloudMessage, $tokens);
            
            return [
                'success' => $report->successes()->count(),
                'failure' => $report->failures()->count(),
            ];
        } catch (\Exception $e) {
            \Log::error('Firebase Notification Error: ' . $e->getMessage());
            return false;
        }
    }
}
