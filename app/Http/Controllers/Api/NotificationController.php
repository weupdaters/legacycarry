<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\User;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Save FCM device token
     */
    public function registerToken(Request $request)
    {
        $validator = Validator::make($request->only('token'), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $user->fcm_token = $request->token;
        $user->save();

        return response()->json(['message' => 'Token saved']);
    }

    /**
     * Send push notification using user_id
     */
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'image_url' => 'nullable|url',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::find($request->user_id);

        if (! $user->fcm_token) {
            return response()->json([
                'error' => 'User does not have a device token',
            ], 422);
        }

        // Handle image
        $imageUrl = null;
        if ($request->hasFile('image')) {

            // store inside: storage/app/public/notifications
            $path = $request->file('image')->store('notifications', 'public');

            // generate public URL
            $imageUrl = asset('storage/' . $path);

        } elseif ($request->filled('image_url')) {

            $imageUrl = $request->image_url;
        }

        // Save notification in DB
        $notification = AppNotification::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imageUrl,
            'sent_to' => $user->id,
            'meta' => [
                'sent_by' => $request->user()?->id,
            ],
        ]);

        // Send to Firebase
        $response = $this->sendToFirebase(
            $user->fcm_token,
            $request->title,
            $request->description,
            $imageUrl,
            $notification->id,
            $user->id
        );

        if (! $response->successful()) {
            $responseBody = $response->json();
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
                // Clear the stale/invalid fcm_token
                $user->fcm_token = null;
                $user->save();

                return response()->json([
                    'message' => 'Notification saved, but recipient\'s device token is unregistered or expired.',
                    'user_id' => $user->id,
                    'warning' => 'unregistered_device',
                ], 200);
            }

            return response()->json([
                'error' => 'FCM failed',
                'status' => $response->status(),
                'response' => $response->body(),
            ], 500);
        }

        return response()->json([
            'message' => 'Notification sent successfully',
            'user_id' => $user->id,
        ]);
    }

    /**
     * List notifications
     */
    public function index(Request $request, $user_id)
    {
        $validator = Validator::make([
            'user_id' => $user_id,
        ], [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $notifications = AppNotification::where('sent_to', $user_id)
            ->orderByDesc('id')->get();

        $notifications->each(function ($notification) use ($user_id) {
            $notification->user = User::find($user_id)->only('id', 'name', 'email');
        });

        return response()->json([
            'status' => true,
            'message' => 'Notifications fetched successfully',
            'data' => $notifications,
        ]);
    }



    /**
     * Firebase HTTP v1 sender
     */
    private function sendToFirebase(
        string $token,
        string $title,
        ?string $body,
        ?string $image,
        int $notificationId,
        int $userId
    ) {
        $accessToken = $this->getFirebaseAccessToken();

        $notificationPayload = [
            'title' => $title,
            'body' => $body ?? '',
        ];

        $dataPayload = [
            'notification_id' => (string) $notificationId,
            'user_id' => (string) $userId,
        ];

        $message = [
            'token' => $token,
        ];

        if (!empty($image)) {
            $notificationPayload['image'] = $image;
            $dataPayload['image'] = $image;

            $message['android'] = [
                'notification' => [
                    'image' => $image,
                ],
            ];

            $message['apns'] = [
                'payload' => [
                    'aps' => [
                        'mutable-content' => 1,
                    ],
                ],
                'fcm_options' => [
                    'image' => $image,
                ],
            ];
        }

        $message['notification'] = $notificationPayload;
        $message['data'] = $dataPayload;

        return Http::withToken($accessToken)
            ->post(
                'https://fcm.googleapis.com/v1/projects/' . env('FIREBASE_PROJECT_ID') . '/messages:send',
                [
                    'message' => $message,
                ]
            );
    }

    /**
     * Get Firebase OAuth token
     */
    private function getFirebaseAccessToken(): string
    {
        $credentials = json_decode(
            file_get_contents(storage_path('app/firebase/legacycarry-firebase-adminsdk-fbsvc-411308132280.json')),
            true
        );

        $client = new ServiceAccountCredentials(
            'https://www.googleapis.com/auth/firebase.messaging',
            $credentials
        );

        $token = $client->fetchAuthToken();

        return $token['access_token'];
    }
}
