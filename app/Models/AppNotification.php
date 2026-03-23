<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    protected $table = 'notifications';
    protected $fillable = [
        'title', 'description', 'image', 'sent_to', 'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
