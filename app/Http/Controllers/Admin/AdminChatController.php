<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Contract\Firestore;

class AdminChatController extends Controller
{
    public function index()
    {
        $chats = DB::table('chat_rooms')
            ->orderBy('last_message_time', 'desc')
            ->get();

        return view('admin.chats.index', compact('chats'));
    }


    public function completeChat()
    {
        $users = User::select('id', 'name')->get();

        // Create id => name map for JS
        $userMap = $users->pluck('name', 'id');

        return view('admin.chats.chat_data', [
            'users' => $userMap, // all users
        ]);
    }

    public function JobApplicationChat(JobApplication $application)
    {
        $residentId = $application->resident_id;
        $userId = $application->user_id;
        $jobId = $application->job_id;

        // Find existing chat ONLY for this application
        $chat = Chat::where('job_id', $jobId)
            ->where('resident_id', $residentId)
            ->where('user_id', $userId)
            ->first();

        // Create chat if not exists
        if (! $chat) {
            $chat = Chat::create([
                'job_id' => $jobId,
                'resident_id' => $residentId,
                'user_id' => $userId,
            ]);
        }

        return redirect()->route('chats.show', $chat->id);
    }


    public function view($chatId)
    {
        $chat = DB::table('chat_rooms')->where('chat_id', $chatId)->first();

        $messages = DB::table('messages')
            ->where('chat_id', $chatId)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.chats.view', compact('chat', 'messages'));
    }

    public function syncFirestoreChats()
    {
        $database = app(Firestore::class)->database();

        $chats = $database->collection('chats')->documents();

        foreach ($chats as $chat) {

            if (!$chat->exists()) {
                continue;
            }

            $chatData = $chat->data();
            $chatId = $chat->id();

            // Save / update chat room
            ChatRoom::updateOrCreate(
                ['chat_uid' => $chatId],
                [
                    'user_one_id' => $chatData['users'][0] ?? null,
                    'user_two_id' => $chatData['users'][1] ?? null,
                    'last_message' => $chatData['lastMessage'] ?? null,
                    'last_message_time' => isset($chatData['lastMessageTime'])
                        ? date('Y-m-d H:i:s', strtotime($chatData['lastMessageTime']))
                        : null,
                ]
            );

            // Messages
            $messages = $database
                ->collection('chats')
                ->document($chatId)
                ->collection('messages')
                ->documents();

            foreach ($messages as $msg) {
                if (!$msg->exists()) {
                    continue;
                }

                $msgData = $msg->data();


                $messageText = $msgData['text'] ?? '';
                if (isset($msgData['createdAt'])) {
                    $createdAt = $msgData['createdAt'];

                    if ($createdAt instanceof \Google\Cloud\Core\Timestamp) {
                        $messageTime = $createdAt->get()->format('Y-m-d H:i:s');
                    } else {
                        $messageTime = date('Y-m-d H:i:s', strtotime($createdAt));
                    }
                }

                ChatMessage::updateOrCreate(
                    [
                        'chat_uid' => $chatId,
                        'sender_id' => $msgData['senderId'],
                        'message_time' => $messageTime,
                    ],
                    [
                        'message' => $messageText,
                    ]
                );
            }
        }

        return 'Firestore chats synced into MySQL successfully';
    }

}
