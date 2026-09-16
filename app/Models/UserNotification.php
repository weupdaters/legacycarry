<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'image',
        'type',
        'pass_id',
        'is_read',
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
}

