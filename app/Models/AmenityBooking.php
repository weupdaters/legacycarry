<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmenityBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'society_id',
        'amenity_id',
        'user_id',
        'booking_date',
        'start_time',
        'end_time',
        'status',
        'rejection_reason',
        'admin_notes',
        'approved_by',
        'approved_at',
        'cancelled_at',
    ];

    protected $casts = [
        'booking_date' => 'date:Y-m-d',
        'approved_at' => 'datetime:Y-m-d H:i:s',
        'cancelled_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeActiveBookings($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed']);
    }

    // Helper
    public function canCancel(): bool
    {
        if (in_array($this->status, ['cancelled', 'rejected'])) {
            return false;
        }

        $bookingStart = \Carbon\Carbon::parse($this->booking_date->format('Y-m-d') . ' ' . $this->start_time);
        return $bookingStart->isFuture();
    }

    // Relationships
    public function society()
    {
        return $this->belongsTo(Society::class);
    }

    public function amenity()
    {
        return $this->belongsTo(Amenity::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
