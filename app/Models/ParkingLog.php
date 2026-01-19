<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingLog extends Model
{
    protected $fillable = [
        'slot_id',
        'pass_id',
        'vehicle_number',
        'entered_by',
        'entered_at',
        'exited_by',
        'exited_at'
    ];

    public function slot()
    {
        return $this->belongsTo(ParkingSlot::class, 'slot_id');
    }

    public function pass()
    {
        return $this->belongsTo(Pass::class, 'pass_id');
    }
}
