<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    use HasFactory;

    protected $fillable = [
        'society_id',
        'name',
        'description',
        'image',
        'status',
        'booking_mode',
        'opening_time',
        'closing_time',
        'available_days',
        'slot_duration_minutes',
        'max_booking_duration_minutes',
        'advance_booking_days',
        'fee',
    ];

    protected $casts = [
        'available_days' => 'array',
        'fee' => 'decimal:2',
        'slot_duration_minutes' => 'integer',
        'max_booking_duration_minutes' => 'integer',
        'advance_booking_days' => 'integer',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return asset('storage/' . $this->image);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isAvailableOnDay(string $dayName): bool
    {
        if (empty($this->available_days)) {
            return true; // Default to all days if not specified
        }

        return in_array(substr($dayName, 0, 3), $this->available_days) || in_array($dayName, $this->available_days);
    }

    // Relationships
    public function society()
    {
        return $this->belongsTo(Society::class);
    }

    public function bookings()
    {
        return $this->hasMany(AmenityBooking::class);
    }
}
