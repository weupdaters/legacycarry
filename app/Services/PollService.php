<?php

namespace App\Services;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PollService
{
    /**
     * Submit a vote for an active poll with atomic verification and concurrency protection.
     */
    public function vote(User $user, int $pollId, int $optionId, ?string $ipAddress = null): PollVote
    {
        return DB::transaction(function () use ($user, $pollId, $optionId, $ipAddress) {
            $poll = Poll::lockForUpdate()->findOrFail($pollId);

            // Society verification
            $userSocietyId = AmenityBookingService::getUserSocietyId($user);
            if (!$userSocietyId || (int) $userSocietyId !== (int) $poll->society_id) {
                throw new InvalidArgumentException('You do not belong to the society for this poll.');
            }

            // Status and timing verification
            if (!$poll->isVotingOpen()) {
                if ($poll->status !== 'active') {
                    throw new InvalidArgumentException("Voting is not open. Poll status is {$poll->status}.");
                }
                $now = Carbon::now();
                if ($now->lt($poll->start_date)) {
                    throw new InvalidArgumentException('Voting for this poll has not started yet.');
                }
                if ($now->gt($poll->end_date)) {
                    throw new InvalidArgumentException('This poll has ended.');
                }
            }

            // Target audience eligibility check
            if ($poll->target_audience !== 'all' && !empty($user->user_residential_status)) {
                $statusLower = strtolower($user->user_residential_status);
                if ($poll->target_audience === 'owners' && !str_contains($statusLower, 'owner')) {
                    throw new InvalidArgumentException('This poll is open to property owners only.');
                }
                if ($poll->target_audience === 'tenants' && !str_contains($statusLower, 'tenant')) {
                    throw new InvalidArgumentException('This poll is open to tenants only.');
                }
            }

            // Verify that the selected option strictly belongs to this poll
            $option = PollOption::where('id', $optionId)
                ->where('poll_id', $poll->id)
                ->first();

            if (!$option) {
                throw new InvalidArgumentException('The selected answer option does not exist for this poll.');
            }

            // Check if user already voted
            if ($poll->hasUserVoted($user->id)) {
                throw new InvalidArgumentException('You have already cast your vote in this poll.');
            }

            try {
                $vote = PollVote::create([
                    'poll_id' => $poll->id,
                    'poll_option_id' => $option->id,
                    'user_id' => $user->id,
                    'society_id' => $poll->society_id,
                    'ip_address' => $ipAddress,
                    'voted_at' => now(),
                ]);
            } catch (QueryException $e) {
                // Duplicate entry catch (1062) in race conditions
                if ($e->getCode() == 23000 || str_contains($e->getMessage(), '1062')) {
                    throw new InvalidArgumentException('You have already voted in this poll.');
                }
                throw $e;
            }

            // NOTE: Per requirement, avoid spamming excessive push notifications on individual votes.
            return $vote;
        });
    }

    /**
     * Create a new poll with 2 to 4 answer options.
     */
    public function createPoll(User $creator, array $data, array $optionTexts): Poll
    {
        // Filter out empty options
        $cleanOptions = array_values(array_filter(array_map('trim', $optionTexts)));

        if (count($cleanOptions) < 2 || count($cleanOptions) > 4) {
            throw new InvalidArgumentException('A poll must have between 2 and 4 answer options.');
        }

        return DB::transaction(function () use ($creator, $data, $cleanOptions) {
            $startDate = Carbon::parse($data['start_date']);
            $endDate = Carbon::parse($data['end_date']);

            if ($endDate->lte($startDate)) {
                throw new InvalidArgumentException('End date/time must be after start date/time.');
            }

            $poll = Poll::create([
                'society_id' => $data['society_id'],
                'created_by' => $creator->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $data['status'] ?? 'draft',
                'results_visibility' => $data['results_visibility'] ?? 'after_vote',
                'target_audience' => $data['target_audience'] ?? 'all',
            ]);

            foreach ($cleanOptions as $index => $text) {
                PollOption::create([
                    'poll_id' => $poll->id,
                    'option_text' => $text,
                    'sort_order' => $index + 1,
                ]);
            }

            // If poll is published as active immediately, notify society residents
            if ($poll->status === 'active' && $poll->isVotingOpen()) {
                SocietyNotificationService::notifySocietyResidents(
                    $poll->society_id,
                    'New Society Poll: ' . $poll->title,
                    'A new poll has been opened for voting. Cast your vote now!',
                    'new_poll_published',
                    ['poll_id' => $poll->id]
                );
            }

            return $poll->load('options');
        });
    }

    /**
     * Update an existing poll. If votes have been recorded, options cannot be altered.
     */
    public function updatePoll(Poll $poll, array $data, ?array $optionTexts = null): Poll
    {
        return DB::transaction(function () use ($poll, $data, $optionTexts) {
            $hasVotes = $poll->votes()->exists();

            if ($optionTexts !== null && $hasVotes) {
                throw new InvalidArgumentException('Options cannot be changed after residents have started voting.');
            }

            if (!empty($data['start_date']) && !empty($data['end_date'])) {
                $startDate = Carbon::parse($data['start_date']);
                $endDate = Carbon::parse($data['end_date']);
                if ($endDate->lte($startDate)) {
                    throw new InvalidArgumentException('End date/time must be after start date/time.');
                }
                $data['start_date'] = $startDate;
                $data['end_date'] = $endDate;
            }

            $previousStatus = $poll->status;
            $poll->update($data);

            if ($optionTexts !== null && !$hasVotes) {
                $cleanOptions = array_values(array_filter(array_map('trim', $optionTexts)));
                if (count($cleanOptions) < 2 || count($cleanOptions) > 4) {
                    throw new InvalidArgumentException('A poll must have between 2 and 4 answer options.');
                }

                $poll->options()->delete();
                foreach ($cleanOptions as $index => $text) {
                    PollOption::create([
                        'poll_id' => $poll->id,
                        'option_text' => $text,
                        'sort_order' => $index + 1,
                    ]);
                }
            }

            // If newly changed to active, send notification
            if ($previousStatus !== 'active' && $poll->status === 'active' && $poll->isVotingOpen()) {
                SocietyNotificationService::notifySocietyResidents(
                    $poll->society_id,
                    'New Society Poll: ' . $poll->title,
                    'A new poll has been opened for voting. Cast your vote now!',
                    'new_poll_published',
                    ['poll_id' => $poll->id]
                );
            }

            return $poll->load('options');
        });
    }

    /**
     * Close a poll and notify residents if results are visible after close.
     */
    public function closePoll(Poll $poll, User $admin): Poll
    {
        return DB::transaction(function () use ($poll, $admin) {
            $poll->update(['status' => 'closed']);

            if ($poll->results_visibility === 'after_close') {
                $results = $poll->calculateResults();
                SocietyNotificationService::notifySocietyResidents(
                    $poll->society_id,
                    'Poll Closed: ' . $poll->title,
                    "Voting has ended with {$results['total_votes']} votes cast. Results are now available to view.",
                    'poll_closed_results',
                    ['poll_id' => $poll->id]
                );
            }

            return $poll;
        });
    }
}
