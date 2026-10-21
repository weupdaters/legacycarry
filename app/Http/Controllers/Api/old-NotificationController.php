<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FCMHelper;
use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Save or update device token
     */
    public function saveToken(Request $request)
    {
        $request->validate([
            'device_token' => 'required',
            'device_type' => 'required|in:android,ios',
        ]);

        // Save or update token
        DeviceToken::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'device_token' => $request->device_token,
                'device_type' => $request->device_type,
            ]
        );

        return response()->json([
            'message' => 'Device token saved successfully',
        ]);
    }

    public function updateToken(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'fcm_token' => 'required|string',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json(['status' => true, 'message' => 'Token updated']);
    }

    // Testing FCM via v1 (note: requires server-side bearer token)
    public function testPush(Request $request)
    {
        // 🔥 Force JSON read
        $data = $request->json()->all();

        if (empty($data)) {
            return response()->json([
                'status' => false,
                'message' => 'Empty JSON body received',
            ], 400);
        }

        validator($data, [
            'to' => 'required|string',
            'title' => 'nullable|string',
            'body' => 'nullable|string',
        ])->validate();

        $token = $data['to'];
        $title = $data['title'] ?? 'Test';
        $body = $data['body'] ?? 'Hello';

        $res = FCMHelper::send($token, $title, $body, $data);

        return response()->json([
            'status' => true,
            'fcm_response' => $res,
        ]);
    }


    // fetch notifications for user
    public function get($user_id)
    {
        $notes = UserNotification::where('user_id', $user_id)->orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'count' => $notes->count(), 'notifications' => $notes]);
    }

    // mark read
    public function markRead(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'notification_id' => 'required|exists:user_notifications,id',
        ]);

        $note = UserNotification::findOrFail($request->notification_id);
        $note->is_read = 1;
        $note->save();

        return response()->json(['status' => true, 'message' => 'Marked read']);
    }
}
