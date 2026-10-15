<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    use HasFactory;

    protected $fillable = [
        'society_id',
        'created_by',
        'title',
        'description',
        'start_date',
        'end_date',
        'status',
        'results_visibility',
        'target_audience',
    ];

    protected $casts = [
        'start_date' => 'datetime:Y-m-d H:i:s',
        'end_date' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    // Scopes
    public function scopeActive($query)
    {
        $now = Carbon::now();
        return $query->where('status', 'active')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now);
    }

    public function scopeForSociety($query, $societyId)
    {
        return $query->where('society_id', $societyId);
    }

    // Helper Methods
    public function isVotingOpen(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = Carbon::now();
        return $now->betweenIncluded($this->start_date, $this->end_date);
    }

    public function hasUserVoted(int $userId): bool
    {
        return $this->votes()->where('user_id', $userId)->exists();
    }

    public function getUserVote(int $userId): ?PollVote
    {
        return $this->votes()->where('user_id', $userId)->first();
    }

    public function canViewResults(int $userId): bool
    {
        // Results visibility options: 'always', 'after_vote', 'after_close'
        if ($this->results_visibility === 'always') {
            return true;
        }

        if ($this->results_visibility === 'after_close') {
            return $this->status === 'closed' || Carbon::now()->isAfter($this->end_date);
        }

        if ($this->results_visibility === 'after_vote') {
            return $this->hasUserVoted($userId) || $this->status === 'closed' || Carbon::now()->isAfter($this->end_date);
        }

        return false;
    }

    /**
     * Compute real-time poll results including percentages and winning/tied options.
     */
    public function calculateResults(): array
    {
        $options = $this->options()->withCount('votes')->get();
        $totalVotes = $options->sum('votes_count');

        $maxVotes = $options->max('votes_count') ?? 0;
        $winnerIds = [];

        if ($maxVotes > 0) {
            $winnerIds = $options->where('votes_count', $maxVotes)->pluck('id')->toArray();
        }

        $isTie = count($winnerIds) > 1;

        $results = $options->map(function ($option) use ($totalVotes, $winnerIds) {
            $pct = $totalVotes > 0 ? round(($option->votes_count / $totalVotes) * 100, 1) : 0;
            return [
                'id' => $option->id,
                'option_text' => $option->option_text,
                'votes_count' => $option->votes_count,
                'percentage' => $pct,
                'is_winner' => in_array($option->id, $winnerIds),
            ];
        });

        return [
            'total_votes' => $totalVotes,
            'is_tie' => $isTie,
            'winner_count' => count($winnerIds),
            'options' => $results,
        ];
    }

    // Relationships
    public function society()
    {
        return $this->belongsTo(Society::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function options()
    {
        return $this->hasMany(PollOption::class)->orderBy('sort_order', 'asc');
    }

    public function votes()
    {
        return $this->hasMany(PollVote::class);
    }
}
