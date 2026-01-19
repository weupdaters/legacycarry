<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftChangeRequest extends Model
{
    protected $fillable = [
        'assigned_shift_id','request_by','requested_shift_date',
        'requested_shift_start','requested_shift_end','reason','status','decision_by','decision_comment',
    ];

    public function assignedShift()
    {
        return $this->belongsTo(AssignedShift::class, 'assigned_shift_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'request_by');
    }

    public function decisionByUser()
    {
        return $this->belongsTo(User::class, 'decision_by');
    }
}
