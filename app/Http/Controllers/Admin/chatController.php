<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function syncChat($chatId, FirebaseService $firebase)
    {
        // Get chat metadata
        $chat = $firebase->getChat($chatId);

        // Insert chat metadata into MySQL
        DB::table('chat_rooms')->updateOrInsert(
            ['chat_id' => $chatId],
            [
                'users' => json_encode($chat['users']),
                'user_names' => json_encode($chat['userNames']),
                'last_message' => $chat['lastMessage'],
                'last_message_time' => $chat['lastMessageTime'],
            ]
        );

        // Get messages list
        $messages = $firebase->getMessages($chatId);

        foreach ($messages as $msg) {
            $data = $msg->data();

            DB::table('messages')->updateOrInsert(
                ['message_id' => $msg->id()],
                [
                    'chat_id' => $chatId,
                    'sender' => $data['sender'] ?? null,
                    'text' => $data['text'] ?? null,
                    'time' => $data['createdAt'] ?? null,
                ]
            );
        }

        return response()->json(['status' => 'Synced']);
    }

    public function show($chatId)
    {
        $chat = Chat::with([
            'resident:id,name',
            'user:id,name',
            'messages.sender:id,name',
        ])->findOrFail($chatId);

        // Security: only resident or user can view
        if (
            auth()->id() !== $chat->resident_id &&
            auth()->id() !== $chat->user_id
        ) {
            abort(403);
        }

        return view('chat.show', compact('chat'));
    }
}
