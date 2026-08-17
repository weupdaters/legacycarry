<?php

namespace App\Models;

use App\Models\ShiftChangeRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AssignedShift extends Model
{
    // ❌ Do NOT use $guarded with relationship named guard()
    // ✅ Either use fillable OR guarded — not both
    protected $fillable = [
        'society_id',
        'guard_user_id',
        'assigned_by',
        'gate_name',
        'shift_date',
        'shift_start',
        'shift_end',
        'status',
        'notes',
    ];

    protected $casts = [
        'shift_date' => 'date:Y-m-d',
        'shift_start' => 'string',
        'shift_end' => 'string',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Assigned security guard
     */
    public function guardUser()
    {
        return $this->belongsTo(User::class, 'guard_user_id');
    }

    /**
     * Assigned by (admin/supervisor)
     */
    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Shift change requests
     */
    public function changeRequests()
    {
        return $this->hasMany(ShiftChangeRequest::class, 'assigned_shift_id');
    }
}
