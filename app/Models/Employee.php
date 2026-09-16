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
        'date_of_birth' => 'date:Y-m-d',
    ];

    protected $appends = [
        'id_proof_url',
        'selfie_url',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getIdProofUrlAttribute()
    {
        if (!$this->id_proof_path) {
            return null;
        }

        if (str_starts_with($this->id_proof_path, 'http://') || str_starts_with($this->id_proof_path, 'https://')) {
            return $this->id_proof_path;
        }

        return asset('storage/' . $this->id_proof_path);
    }

    public function getSelfieUrlAttribute()
    {
        if (!$this->selfie_path) {
            return null;
        }

        if (str_starts_with($this->selfie_path, 'http://') || str_starts_with($this->selfie_path, 'https://')) {
            return $this->selfie_path;
        }

        return asset('storage/' . $this->selfie_path);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function flat()
    {
        return $this->belongsTo(Flat::class, 'flat_id', 'id');
    }
}
