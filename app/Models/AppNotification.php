<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'title',
        'description',
        'image',
        'sent_to',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    protected $appends = [
        'image_url',
    ];

    public function getImageAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return asset('storage/' . ltrim(str_replace('storage/', '', $value), '/'));
    }

    public function getImageUrlAttribute()
    {
        return $this->image;
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'sent_to');
    }
}


