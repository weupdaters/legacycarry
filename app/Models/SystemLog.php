<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $fillable = [
        'action',
        'details',
        'user_id',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    /**
     * Get the user that performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for recent logs
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get formatted action label
     */
    public function getActionLabelAttribute()
    {
        $labels = [
            'clear_cache' => 'Clear Cache',
            'optimize_database' => 'Optimize Database',
            'database_backup' => 'Database Backup',
            'clear_logs' => 'Clear Logs',
            'toggle_maintenance' => 'Toggle Maintenance Mode',
        ];

        return $labels[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }
}