<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPost extends Model
{
    protected $table = 'job_posts'; // 👈 custom table name

    protected $fillable = [
        'job_title',
        //'location',
        'latitude',
        'longitude',
        'address',
        'job_type',
        'workers_required',
        'skills_required',
        'tools_provided',
        'documents_required',
        'safety_instructions',
        'start_date',
        'end_date',
        'shift',
        'pay_type',
        'pay_amount',
        'advance_payment',
        'advance_amount',
        'user_id',
        'society_id',
        'approved_by',
        'status',
        'completed_at'
    ];

    protected $casts = [
        'skills_required' => 'array',
        'documents_required' => 'array',
        'tools_provided' => 'boolean',
        'advance_payment' => 'boolean',
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'completed_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'job_id');
    }

    
}
