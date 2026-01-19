<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pass extends Model
{
    protected $fillable = [
      'society_id','pass_type','created_by',
      'guest_name','guest_phone','vehicle_number','vehicle_type','vehicle_owner_name',
      'purpose','valid_from','valid_until','otp','token','token_used',
      'scanned_in_by','scanned_in_at','scanned_out_by','scanned_out_at',
    ];
    protected $casts = [
      'valid_from' => 'datetime','valid_until' => 'datetime',
      'scanned_in_at' => 'datetime','scanned_out_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scannedInGuard()
    {
        return $this->belongsTo(User::class, 'scanned_in_by');
    }

    public function scannedOutGuard()
    {
        return $this->belongsTo(User::class, 'scanned_out_by');
    }

    public function createdByUser()
    {
        // passes.created_by → users.id
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function getUserDetilas()
    {
        return $this->belongsTo(UserDetail::class, 'user_id');
    }

    public function society()
    {
        return $this->belongsTo(Society::class, 'society_id');
    }

    public function assignment()
    {
        return $this->belongsTo(ResidentAssignment::class, 'assignment_id');
    }

    // public function flat()
    // {
    //     return $this->belongsTo(Flat::class, 'flat_id', 'id');
    // }

}
