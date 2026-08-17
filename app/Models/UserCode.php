<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserCode extends Model
{
    use HasFactory;

    // Table name (optional if it follows Laravel naming convention)
    protected $table = 'user_codes';

    // Mass assignable fields
    protected $fillable = [
        'user_id',
        'phone',
        'country_code',
        'code_hash',
        'expires_at',
        'attempts',
        'purpose',
    ];

    // Casts
    protected $casts = [
        'expires_at' => 'datetime:Y-m-d H:i:s',
        'attempts' => 'integer',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Relation to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if OTP/code is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->lt(Carbon::now());
    }

    /**
     * Increment attempts
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }
}
