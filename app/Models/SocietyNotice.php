<?php

// app/Models/SocietyNotice.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Society;
use App\Models\User;

class SocietyNotice extends Model
{
    use HasFactory;

    protected $fillable = [
        'society_id',
        'created_by',
        'title',
        'slug',
        'description',
        'image',
        'notice_type',
        'target_user_id',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($notice) {
            if (empty($notice->slug)) {
                $notice->slug = Str::slug($notice->title) . '-' . Str::random(6);
            }
            if (empty($notice->published_at) && $notice->is_published) {
                $notice->published_at = now();
            }
        });
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function society()
    {
        return $this->belongsTo(Society::class, 'society_id');
    }
}
