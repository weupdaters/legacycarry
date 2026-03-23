<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'experience_years',
        'services',
        'work_radius_km',
        'hourly_rate',
        'availability',
        'about',
        'id_number',
        'phone_verified',
        'id_proof_path',
        'selfie_path',
        'latitude',
        'longitude',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'gender',
        'marital_status',
        'occupation',
        'floor_number',
        'flat_id',
        'date_of_birth',
    ];

    protected $casts = [
        'services' => 'array',
        'phone_verified' => 'boolean',
        'date_of_birth' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
