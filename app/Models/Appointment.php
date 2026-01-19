<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'society_id',
        'resident_id',
        'employer_id',
        'job_application_id',

        'total_amount',
        'advance_percentage',
        'advance_amount',
        'final_amount',

        'advance_paid',
        'final_paid',

        'entry_token',
        'entry_token_used',
        'exit_token',
        'exit_token_used',

        'guard_in_time',
        'guard_out_time',

        'guard_in_id',
        'guard_out_id',

        'job_start_time',
        'job_end_time',

        'resident_confirmation',
        'status',
    ];

    protected $casts = [
        'job_start_time' => 'datetime',
        'job_end_time' => 'datetime',
        'guard_in_time' => 'datetime',
        'guard_out_time' => 'datetime',
    ];

    // 🔗 Link back to Job Application
    public function jobApplication()
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }

    // 🔗 Resident
    public function resident()
    {
        return $this->belongsTo(User::class, 'resident_id');
    }

    // 🔗 Employer / Worker
    public function employer()
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    // Auto calculate minutes worked
    public function totalMinutes()
    {
        if ($this->job_start_time && $this->job_end_time) {
            return $this->job_end_time->diffInMinutes($this->job_start_time);
        }
        return 0;
    }

    public function guardIn()
    {
        return $this->belongsTo(User::class, 'guard_in_id');
    }


    public function guardOut()
    {
        return $this->belongsTo(User::class, 'guard_out_id');
    }

    // 🔗 Society
    public function society()
    {
        return $this->belongsTo(Society::class, 'society_id');
    }

    public function userDetails()
    {
        return $this->hasOne(UserDetail::class, 'resident_id', 'user_id');
    }

}
