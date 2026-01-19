<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingSlot extends Model
{
    protected $fillable = [
        'society_id',
        'slot_number',
        'is_occupied',
        'current_pass_id'
    ];

    public function currentPass()
    {
        return $this->belongsTo(Pass::class, 'current_pass_id');
    }

    public function logs()
    {
        return $this->hasMany(ParkingLog::class, 'slot_id');
    }
}
