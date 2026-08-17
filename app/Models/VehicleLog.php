<?php

namespace App\Models;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Model;

class VehicleLog extends Model
{
    protected $table = 'vehicle_logs';

    protected $fillable = [
        'vehicle_id',
        'society_id',
        'guard_id',
        'direction',
        'vehicle_number',
        'notes',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function society()
    {
        return $this->belongsTo(Society::class, 'society_id');
    }

    public function guardUser()
    {
        return $this->belongsTo(User::class, 'guard_id');
    }}
