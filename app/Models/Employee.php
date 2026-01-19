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
    ];

    protected $casts = [
        'services' => 'array',
        'phone_verified' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
