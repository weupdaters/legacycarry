<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'society_id',
        'user_id',
        'assigned_to',
        'category',
        'title',
        'description',
        'priority',
        'status',
        'attachment',
        'resolution_notes',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime:Y-m-d H:i:s',
        'closed_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (!$this->attachment) {
            return null;
        }

        if (str_starts_with($this->attachment, 'http://') || str_starts_with($this->attachment, 'https://')) {
            return $this->attachment;
        }

        return asset('storage/' . $this->attachment);
    }

    // Scopes
    public function scopeForSociety($query, $societyId)
    {
        return $query->where('society_id', $societyId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    // Relationships
    public function society()
    {
        return $this->belongsTo(Society::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class)->oldest();
    }
}
