<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    protected $fillable = [
        'chat_uid',
        'user_one_id',
        'user_two_id',
        'last_message',
        'last_message_time',
    ];
}
