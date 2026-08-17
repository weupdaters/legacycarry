<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuardShift extends Model
{
    use HasFactory;

    protected $table = 'guard_shifts';

    protected $fillable = [
        'society_id',
        'guard_id',
        'assigned_by',
        'gate',
        'start_shift_date',
        'end_shift_date',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'start_shift_date' => 'date:Y-m-d',
        'end_shift_date' => 'date:Y-m-d',
        'start_time' => 'string',
        'end_time' => 'string',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /* ===========================
     | Relationships
     ===========================*/

    public function guardUser()
    {
        return $this->belongsTo(User::class, 'guard_id');
    }

    public function assignedByUser()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function logs()
    {
        return $this->hasMany(GuardShiftLog::class, 'guard_shift_id');
    }

    public function requests()
    {
        return $this->hasMany(GuardShiftRequest::class);
    }
}
