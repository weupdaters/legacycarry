<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\DB;

class SyncChats extends Command
{
    protected $signature = 'sync:chats';
    protected $description = 'Sync all Firebase chats into MySQL';

    public function handle(FirebaseService $firebase)
    {
        $chats = $firebase->firestore->collection("chats")->documents();

        foreach ($chats as $chat) {

            $chatId = $chat->id();
            $data = $chat->data();

            DB::table('chat_rooms')->updateOrInsert(
                ['chat_id' => $chatId],
                [
                    'users' => json_encode($data['users']),
                    'user_names' => json_encode($data['userNames']),
                    'last_message' => $data['lastMessage'] ?? '',
                    'last_message_time' => $data['lastMessageTime'] ?? null,
                ]
            );

            $messages = $firebase->firestore->collection("chats")
                ->document($chatId)
                ->collection("messages")
                ->documents();

            foreach ($messages as $msg) {
                $m = $msg->data();

                DB::table('messages')->updateOrInsert(
                    ['message_id' => $msg->id()],
                    [
                        'chat_id' => $chatId,
                        'sender' => $m['sender'] ?? null,
                        'message' => $m['message'] ?? null,
                        'file_url' => $m['fileUrl'] ?? null,
                        'created_at' => $m['createdAt'] ?? null,
                    ]
                );
            }
        }

        $this->info("All chats synced successfully!");
    }
}
