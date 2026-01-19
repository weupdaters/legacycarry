<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = [
        'chat_uid',
        'sender_id',
        'message',
        'message_time',
    ];
}
