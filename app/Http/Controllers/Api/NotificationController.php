<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FCMHelper;
use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Register or update FCM device token
     * Accepts: token (required), user_id (optional, falls back to authenticated user)
     */
    public function registerToken(Request $request)
    {
        $input = $this->cleanRequestInput($request);

        $validator = Validator::make($input, [
            'token' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        if (!$user && $request->filled('user_id')) {
            $user = User::find($request->user_id);
        }

        if (!$user) {
            return response()->json([
                'status' => false,
                'error' => 'Unauthenticated or user_id is required',
            ], 401);
        }

        $user->fcm_token = trim($request->token);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Device token saved successfully',
            'user_id' => $user->id,
            'fcm_token' => $user->fcm_token,
        ], 200);
    }

    /**
     * Save token alias
     */
    public function saveToken(Request $request)
    {
        return $this->registerToken($request);
    }

    /**
     * Update token alias
     */
    public function updateToken(Request $request)
    {
        return $this->registerToken($request);
    }

    /**
     * Send push notification using user_id or direct device token
     */
    public function send(Request $request)
    {
        $input = $this->cleanRequestInput($request);

        $validator = Validator::make($input, [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'body' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'image_url' => 'nullable|url',
            'user_id' => 'nullable|exists:users,id',
            'token' => 'nullable|string',
            'data' => 'nullable|array',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors(),
            ], 422);
        }

        $bodyText = $request->description ?? $request->body ?? '';
        $imageUrl = null;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('notifications', 'public');
            $imageUrl = asset('storage/' . $path);
        } elseif ($request->filled('image_url')) {
            $imageUrl = $request->image_url;
        }

        $targetToken = null;
        $user = null;

        if ($request->filled('token')) {
            $targetToken = trim($request->token);
            if ($request->filled('user_id')) {
                $user = User::find($request->user_id);
            }
        } elseif ($request->filled('user_id')) {
            $user = User::find($request->user_id);
            if (!$user || !$user->fcm_token) {
                return response()->json([
                    'status' => false,
                    'error' => 'User does not have a registered device token',
                ], 422);
            }
            $targetToken = $user->fcm_token;
        } elseif ($request->user()) {
            $user = $request->user();
            if (!$user->fcm_token) {
                return response()->json([
                    'status' => false,
                    'error' => 'Authenticated user does not have a registered device token',
                ], 422);
            }
            $targetToken = $user->fcm_token;
        } else {
            return response()->json([
                'status' => false,
                'error' => 'Either user_id or token is required to send a push notification',
            ], 422);
        }

        $notificationId = null;

        // Save DB record if user is identified
        if ($user) {
            $notification = AppNotification::create([
                'title' => $request->title,
                'description' => $bodyText,
                'image' => $imageUrl,
                'sent_to' => $user->id,
                'meta' => [
                    'sent_by' => $request->user()?->id,
                    'data' => $request->input('data', []),
                ],
            ]);
            $notificationId = $notification->id;
        }

        $dataPayload = $request->input('data', []);
        if ($notificationId) {
            $dataPayload['notification_id'] = (string) $notificationId;
        }
        if ($user) {
            $dataPayload['user_id'] = (string) $user->id;
        }

        // Send via FCMHelper
        $fcmResult = FCMHelper::send($targetToken, $request->title, $bodyText, $dataPayload, $imageUrl);

        if ($fcmResult === false) {
            return response()->json([
                'status' => false,
                'error' => 'FCM push notification failed to send. Check server logs or token validity.',
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Push notification sent successfully',
            'user_id' => $user?->id,
            'token' => $targetToken,
            'fcm_response' => $fcmResult,
        ], 200);
    }

    /**
     * Test push notification endpoint
     */
    public function testPush(Request $request)
    {
        $token = $request->input('token') ?? $request->user()?->fcm_token;
        if (!$token && $request->filled('user_id')) {
            $user = User::find($request->user_id);
            $token = $user?->fcm_token;
        }

        if (!$token) {
            return response()->json([
                'status' => false,
                'error' => 'Provide token or user_id with valid fcm_token',
            ], 422);
        }

        $title = $request->input('title', 'Test Push Notification');
        $body = $request->input('body', 'This is a test notification from Legacy Carry.');

        $result = FCMHelper::send($token, $title, $body, ['type' => 'test_push']);

        return response()->json([
            'status' => (bool) $result,
            'message' => $result ? 'Test notification sent successfully' : 'Failed to send test notification',
            'result' => $result,
        ]);
    }

    /**
     * List notifications for a user
     */
    public function index(Request $request, $user_id = null)
    {
        $targetUserId = $user_id ?? $request->user()?->id;

        $validator = Validator::make(['user_id' => $targetUserId], [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors(),
            ], 422);
        }

        $notifications = AppNotification::where('sent_to', $targetUserId)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Notifications fetched successfully',
            'data' => $notifications,
        ]);
    }

    /**
     * Clean raw JSON input if comments like // are present in Postman/raw JSON
     */
    private function cleanRequestInput(Request $request): array
    {
        $all = $request->all();
        if (!empty($all) && (isset($all['title']) || isset($all['token']) || isset($all['user_id']))) {
            return $all;
        }

        $raw = $request->getContent();
        if (!empty($raw)) {
            $clean = preg_replace('#^\s*//.*$#m', '', $raw);
            $clean = preg_replace('#/\*.*?\*/#s', '', $clean);
            $decoded = json_decode($clean, true);
            if (is_array($decoded) && !empty($decoded)) {
                $request->merge($decoded);
                if (method_exists($request, 'setJson')) {
                    $request->setJson(new \Symfony\Component\HttpFoundation\InputBag($decoded));
                }
                return array_merge($all, $decoded);
            }
        }

        return $all;
    }

}


