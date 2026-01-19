<?php

namespace App\Services;

use Kreait\Firebase\Factory;

class FirebaseService
{
    protected $firestore;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(storage_path('firebase/serviceAccount.json'));

        $this->firestore = $factory->createFirestore()->database();
    }

    public function getChat($chatId)
    {
        return $this->firestore->collection("chats")->document($chatId)->snapshot()->data();
    }

    public function getMessages($chatId)
    {
        return $this->firestore->collection("chats")
            ->document($chatId)
            ->collection("messages")
            ->documents();
    }
}
