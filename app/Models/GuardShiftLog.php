<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuardShiftLog extends Model
{
    use HasFactory;

    protected $table = 'guard_shift_logs';

    public $timestamps = false;

    protected $fillable = [
        'guard_shift_id',
        'action',
        'action_by',
        'notes',
        'created_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Parent shift
    public function shift()
    {
        return $this->belongsTo(GuardShift::class, 'guard_shift_id');
    }

    // User who performed the action (admin / guard)
    public function actionBy()
    {
        return $this->belongsTo(User::class, 'action_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'action_by');
    }

    public function guardUser()
    {
        return $this->belongsTo(User::class, 'action_by');
    }


}
