<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'method_type',
        'details',
        'masked',
        'verification_status',
        'verified_at',
        'is_primary',
        'provider',
    ];

    protected $casts = [
        'details' => 'array',
        'is_primary' => 'boolean',
    ];

    // Accessors & Mutators to support both 'type' and 'method_type'
    public function getTypeAttribute()
    {
        return $this->attributes['method_type'] ?? null;
    }

    public function setTypeAttribute($value)
    {
        $this->attributes['method_type'] = $value;
    }

    // Relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
