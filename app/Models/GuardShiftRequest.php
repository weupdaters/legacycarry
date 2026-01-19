<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuardShiftRequest extends Model
{
    use HasFactory;

    protected $table = 'guard_shift_requests';

    protected $fillable = [
        'guard_shift_id',
        'requested_by',
        'new_date',
        'new_start',
        'new_end',
        'reason',
        'status',
    ];

    protected $casts = [
        'new_date' => 'date',
        'new_start' => 'datetime:H:i',
        'new_end' => 'datetime:H:i',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Related shift
    public function shift()
    {
        return $this->belongsTo(GuardShift::class, 'guard_shift_id');
    }

    // Guard / Admin who requested change
    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }


}
