<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScanHistory extends Model
{
    protected $table = 'scan_histories';

    protected $fillable = [
        'society_id',
        'guard_id',
        'token',
        'token_type',
        'subject_id',
        'action',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
