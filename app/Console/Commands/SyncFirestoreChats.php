<?php

namespace App\Console\Commands;

use App\Models\ChatMessage;
use App\Models\ChatSyncLog;
use Illuminate\Console\Command;
use Kreait\Firebase\Factory;

class SyncFirestoreChats extends Command
{
    protected $signature = 'chat:sync-firestore';
    protected $description = 'Sync old Firestore chats into MySQL';

    public function handle()
    {
        $syncLog = ChatSyncLog::first();

        if ($syncLog && $syncLog->last_synced_at) {
            $this->info('Chats already synced. No action needed.');
            return Command::SUCCESS;
        }

        $factory = (new Factory())
            ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')))
            ->withProjectId(env('FIREBASE_PROJECT_ID'));

        $database = $factory->createFirestore()->database();

        $chats = $database->collection('chats')->documents();

        foreach ($chats as $chat) {
            if (!$chat->exists()) {
                continue;
            }

            $chatId = $chat->id();

            $messages = $database
                ->collection('chats')
                ->document($chatId)
                ->collection('messages')
                ->documents();

            foreach ($messages as $msg) {
                if (!$msg->exists()) {
                    continue;
                }

                $data = $msg->data();

                $senderId = $data['senderId'] ?? null;
                if (!$senderId) {
                    continue;
                }

                $text = $data['text'] ?? '';
                $time = $data['createdAt']->get()->format('Y-m-d H:i:s');

                ChatMessage::updateOrCreate(
                    [
                        'chat_uid' => $chatId,
                        'sender_id' => $senderId,
                        'message_time' => $time,
                    ],
                    [
                        'message' => $text,
                    ]
                );
            }
        }

        ChatSyncLog::updateOrCreate(
            ['id' => 1],
            ['last_synced_at' => now()]
        );

        $this->info('Firestore chats synced successfully.');

        return Command::SUCCESS;
    }
}
