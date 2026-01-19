<?php

use App\Models\ResidentAssignment;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserDetail;

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
