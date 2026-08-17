<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FCMHelper;
use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        if (!$user && (!empty($input['user_id']) || $request->filled('user_id'))) {
            $user = User::find($input['user_id'] ?? $request->user_id);
        }

        if (!$user) {
            return response()->json([
                'status' => false,
                'error' => 'Unauthenticated or user_id is required',
            ], 401);
        }

        $user->fcm_token = trim($input['token'] ?? $request->token);
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
     * Send push notification using user_id or direct device token.
     * Supports uploaded image files (form-data), image URLs, or base64 data URIs.
     * Saves the notification and image to the database and returns it in notification list.
     */
    public function send(Request $request)
    {
        $input = $this->cleanRequestInput($request);

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'body' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
            'token' => 'nullable|string',
            'data' => 'nullable',
        ];

        // Conditional image rules depending on input type (file vs string/url)
        if ($request->hasFile('image')) {
            $rules['image'] = 'nullable|image|max:10240';
        } elseif ($request->hasFile('upload_image')) {
            $rules['upload_image'] = 'nullable|image|max:10240';
        } elseif ($request->hasFile('file')) {
            $rules['file'] = 'nullable|image|max:10240';
        } elseif ($request->hasFile('image_url')) {
            $rules['image_url'] = 'nullable|image|max:10240';
        } else {
            $rules['image'] = 'nullable|string';
            $rules['image_url'] = 'nullable|string';
            $rules['upload_image'] = 'nullable|string';
        }

        $validator = Validator::make(array_merge($request->allFiles(), $input), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors(),
            ], 422);
        }

        $title = $input['title'] ?? $request->input('title');
        $bodyText = $input['description'] ?? $input['body'] ?? $request->input('description') ?? $request->input('body') ?? '';
        $imageUrl = null;

        // 1. Check for multipart file upload (image, upload_image, file, or image_url)
        $uploadedFile = $request->file('image')
            ?? $request->file('upload_image')
            ?? $request->file('file')
            ?? $request->file('image_url');

        if ($uploadedFile && $uploadedFile->isValid()) {
            $path = $uploadedFile->store('notifications', 'public');
            $imageUrl = asset('storage/' . $path);
        } else {
            // 2. Check for image URL or Base64 string from input
            $rawImage = $input['image_url'] ?? $input['image'] ?? $input['upload_image'] ?? $request->input('image_url') ?? $request->input('image');

            if (!empty($rawImage) && is_string($rawImage)) {
                $rawImage = trim($rawImage);
                // Handle Base64 Data URI
                if (preg_match('/^data:image\/(\w+);base64,/', $rawImage, $type)) {
                    $data = substr($rawImage, strpos($rawImage, ',') + 1);
                    $type = strtolower($type[1]); // jpg, png, gif, webp
                    $decodedData = base64_decode($data);
                    if ($decodedData !== false) {
                        $fileName = 'notifications/' . uniqid('notif_', true) . '.' . $type;
                        Storage::disk('public')->put($fileName, $decodedData);
                        $imageUrl = asset('storage/' . $fileName);
                    }
                } else {
                    $imageUrl = $rawImage;
                }
            }
        }

        $targetToken = null;
        $user = null;
        $userId = $input['user_id'] ?? $request->input('user_id');
        $token = $input['token'] ?? $request->input('token');

        if (!empty($token)) {
            $targetToken = trim($token);
            if (!empty($userId)) {
                $user = User::find($userId);
            } else {
                $user = User::where('fcm_token', $targetToken)->first();
            }
        } elseif (!empty($userId)) {
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'error' => 'User not found with id ' . $userId,
                ], 404);
            }
            $targetToken = $user->fcm_token;
        } elseif ($request->user()) {
            $user = $request->user();
            $targetToken = $user->fcm_token;
        } else {
            return response()->json([
                'status' => false,
                'error' => 'Either user_id or token is required to send a push notification',
            ], 422);
        }

        $dataPayload = $input['data'] ?? $request->input('data', []);
        if (is_string($dataPayload)) {
            $decodedData = json_decode($dataPayload, true);
            $dataPayload = is_array($decodedData) ? $decodedData : [];
        }

        $notificationId = null;
        $notification = null;

        // Save DB record in notifications table
        if ($user) {
            $notification = AppNotification::create([
                'title' => $title,
                'description' => $bodyText,
                'image' => $imageUrl,
                'sent_to' => $user->id,
                'meta' => [
                    'sent_by' => $request->user()?->id,
                    'image_url' => $imageUrl,
                    'data' => $dataPayload,
                ],
            ]);
            $notificationId = $notification->id;
        }

        $fcmResult = null;
        if (!empty($targetToken)) {
            // Prepare FCM data payload
            $fcmData = is_array($dataPayload) ? $dataPayload : [];
            if ($notificationId) {
                $fcmData['notification_id'] = (string) $notificationId;
            }
            if ($user) {
                $fcmData['user_id'] = (string) $user->id;
            }
            if ($imageUrl) {
                $fcmData['image'] = $imageUrl;
                $fcmData['image_url'] = $imageUrl;
            }

            // Send via FCMHelper
            $fcmResult = FCMHelper::send($targetToken, $title, $bodyText, $fcmData, $imageUrl);
        }

        return response()->json([
            'status' => true,
            'message' => !empty($targetToken) 
                ? 'Push notification sent successfully' 
                : 'Notification saved in database (User has no active device token for push)',
            'data' => [
                'notification_id' => $notification?->id,
                'user_id' => $user?->id,
                'title' => $title,
                'description' => $bodyText,
                'image' => $imageUrl,
                'image_url' => $imageUrl,
                'token' => $targetToken,
                'created_at' => $notification?->created_at?->format('d M Y, h:i A'),
            ],
            'fcm_response' => $fcmResult,
        ], 200);
    }

    /**
     * Test push notification endpoint
     */
    public function testPush(Request $request)
    {
        $input = $this->cleanRequestInput($request);
        $token = $input['token'] ?? $request->input('token') ?? $request->user()?->fcm_token;
        $userId = $input['user_id'] ?? $request->input('user_id');

        if (!$token && !empty($userId)) {
            $user = User::find($userId);
            $token = $user?->fcm_token;
        }

        if (!$token) {
            return response()->json([
                'status' => false,
                'error' => 'Provide token or user_id with valid fcm_token',
            ], 422);
        }

        $title = $input['title'] ?? $request->input('title', 'Test Push Notification');
        $body = $input['body'] ?? $input['description'] ?? $request->input('body', 'This is a test notification from Legacy Carry.');
        $image = $input['image_url'] ?? $input['image'] ?? $request->input('image_url') ?? null;

        $result = FCMHelper::send($token, $title, $body, ['type' => 'test_push'], $image);

        return response()->json([
            'status' => (bool) $result,
            'message' => $result ? 'Test notification sent successfully' : 'Failed to send test notification',
            'result' => $result,
        ]);
    }

    /**
     * List notifications for a user (including image, image_url, metadata, and timestamps)
     */
    public function index(Request $request, $user_id = null)
    {
        $targetUserId = $user_id ?? $request->query('user_id') ?? $request->input('user_id') ?? $request->user()?->id;

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
            ->get()
            ->map(function ($notif) {
                return [
                    'id' => $notif->id,
                    'title' => $notif->title,
                    'description' => $notif->description,
                    'image' => $notif->image,
                    'image_url' => $notif->image,
                    'sent_to' => $notif->sent_to,
                    'meta' => $notif->meta,
                    'created_at' => $notif->created_at ? $notif->created_at->format('d M Y, h:i A') : null,
                    'created_at_raw' => $notif->created_at,
                    'updated_at' => $notif->updated_at,
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'Notifications fetched successfully',
            'count' => $notifications->count(),
            'data' => $notifications,
        ]);
    }

    /**
     * Alias for getting user notifications
     */
    public function get(Request $request, $user_id = null)
    {
        return $this->index($request, $user_id);
    }

    /**
     * Mark notification(s) as read
     */
    public function markRead(Request $request)
    {
        $input = $this->cleanRequestInput($request);
        $notificationId = $input['notification_id'] ?? $request->notification_id ?? null;
        $userId = $input['user_id'] ?? $request->user_id ?? $request->user()?->id;

        if ($notificationId) {
            $notif = AppNotification::find($notificationId);
            if ($notif) {
                $meta = $notif->meta ?? [];
                $meta['is_read'] = true;
                $meta['read_at'] = now()->toDateTimeString();
                $notif->meta = $meta;
                $notif->save();
            }
        } elseif ($userId) {
            $notifs = AppNotification::where('sent_to', $userId)->get();
            foreach ($notifs as $notif) {
                $meta = $notif->meta ?? [];
                $meta['is_read'] = true;
                $meta['read_at'] = now()->toDateTimeString();
                $notif->meta = $meta;
                $notif->save();
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Notification(s) marked as read',
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



