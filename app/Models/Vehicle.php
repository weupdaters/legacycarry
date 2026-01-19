<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'vehicle_number', 'vehicle_type', 'color', 'qr_token', 'status', 'society_id',
    ];

    public function logs()
    {
        return $this->hasMany(VehicleLog::class, 'vehicle_id');
    }

    public function passes()
    {
        return $this->hasMany(VehiclePass::class, 'vehicle_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function society()
    {
        return $this->belongsTo(Society::class, 'society_id');
    }

    public function userDetails()
    {
        return $this->hasOne(UserDetail::class, 'user_id');
    }
}
