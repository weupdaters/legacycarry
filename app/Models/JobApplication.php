<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $fillable = [
        'job_id',
        'user_id',
        'resident_id',
        'start_datetime',
        'expected_cost',
        'status',
        'shortlisted',
        'message_to_employer',
        'certificate_photo',
        'appointment_id',
        'completed_at',
    ];

    // Worker/User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Resident who posted job
    public function resident()
    {
        return $this->belongsTo(User::class, 'resident_id');
    }

    // Job Post details
    public function jobPost()
    {
        return $this->belongsTo(JobPost::class, 'job_id');
    }

    // Employee Profile
    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id', 'user_id');
    }

    // 🔗 Relationship to Appointment
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function getDisplayNameAttribute()
    {
        return $this->title ?? $this->job_title ?? 'Service';
    }

}
