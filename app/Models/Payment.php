<?php

// app/Models/Payment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_post_id',
        'job_application_id',
        'payer_id',
        'payee_id',
        'amount',
        'currency',
        'payment_method',
        'payment_id',
        'payment_reference',
        'notes',
        'status',
        'paid_at',
        'appointment_id','payment_type','target_account',
        'payer_id','payee_id','amount','status','payment_reference',
        'cash_verified_by','cash_verified_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    // Relationships
    public function job()
    {
        return $this->belongsTo(JobPost::class, 'job_post_id');
    }

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function payee()
    {
        return $this->belongsTo(User::class, 'payee_id');
    }

    // Scope for successful payments
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    // Scope for recent payments
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function cashVerifiedBy()
    {
        return $this->belongsTo(User::class, 'cash_verified_by');
    }
}
