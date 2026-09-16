<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'withdrawal_id',
        'user_id',
        'amount',
        'currency',
        'user_payment_method_id',
        'payout_type',
        'payout_details',
        'status',
        'is_auto_payout',
        'cf_transfer_id',
        'cf_reference_id',
        'cf_response',
        'admin_notes',
        'rejection_reason',
        'processed_by',
        'processed_at',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payout_details' => 'array',
        'cf_response' => 'array',
        'is_auto_payout' => 'boolean',
        'processed_at' => 'datetime:Y-m-d H:i:s',
        'paid_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * User who requested the withdrawal
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Payment method saved by the user
     */
    public function paymentMethod()
    {
        return $this->belongsTo(UserPaymentMethod::class, 'user_payment_method_id');
    }

    /**
     * Admin who processed/approved/rejected
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeHold($query)
    {
        return $query->where('status', 'hold');
    }

    /**
     * Get breakdown of source jobs and final payments contributing to this withdrawal
     */
    public function getSourcePaymentsAttribute()
    {
        if (!empty($this->payout_details['source_jobs']) && is_array($this->payout_details['source_jobs'])) {
            return $this->payout_details['source_jobs'];
        }

        // Dynamic lookup from Payment records
        return Payment::where('payee_id', $this->user_id)
            ->where('payment_type', 'final')
            ->where('status', 'success')
            ->with(['job:id,job_title', 'payer:id,name,phone'])
            ->orderByDesc('paid_at')
            ->limit(15)
            ->get()
            ->map(function ($p) {
                return [
                    'payment_id' => $p->payment_id,
                    'job_id' => $p->job_post_id,
                    'job_title' => $p->job?->job_title ?? ($p->appointment_id ? 'Appointment #' . $p->appointment_id : 'Direct Service'),
                    'payer_name' => $p->payer?->name ?? 'Client',
                    'payer_phone' => $p->payer?->phone ?? '',
                    'amount' => (float) $p->amount,
                    'paid_at' => $p->paid_at ? $p->paid_at->format('d M Y, h:i A') : ($p->created_at ? $p->created_at->format('d M Y, h:i A') : ''),
                    'reference' => $p->payment_reference,
                ];
            })->toArray();
    }
}
