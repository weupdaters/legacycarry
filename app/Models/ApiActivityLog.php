<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiActivityLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'api_activity_logs';

    protected $fillable = [
        'user_id',
        'user_name',
        'endpoint',
        'method',
        'ip_address',
        'device_type',
        'status_code',
        'response_time_ms',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
