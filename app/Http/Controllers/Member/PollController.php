<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Services\AmenityBookingService;
use App\Services\PollService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PollController extends Controller
{
    protected PollService $pollService;

    public function __construct(PollService $pollService)
    {
        $this->pollService = $pollService;
    }

    /**
     * List active and closed polls for the resident's society.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user);

        if (!$societyId) {
            return view('member.polls.index', [
                'activePolls' => collect(),
                'closedPolls' => collect(),
                'error' => 'You are not assigned to any society.',
            ]);
        }

        $now = Carbon::now();

        // Active Polls
        $activePolls = Poll::with(['options', 'votes'])
            ->where('society_id', $societyId)
            ->where('status', 'active')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->latest('start_date')
            ->get();

        // Closed or Past Polls
        $closedPolls = Poll::with(['options', 'votes'])
            ->where('society_id', $societyId)
            ->where(function ($q) use ($now) {
                $q->where('status', 'closed')
                  ->orWhere('end_date', '<', $now);
            })
            ->latest('end_date')
            ->paginate(10);

        return view('member.polls.index', compact('activePolls', 'closedPolls'));
    }

    /**
     * Show poll details, vote submission options, or results.
     */
    public function show($id)
    {
        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user);

        $poll = Poll::with('options')->where('id', $id)->where('society_id', $societyId)->firstOrFail();

        $userVote = $poll->getUserVote($user->id);
        $hasVoted = !is_null($userVote);
        $canViewResults = $poll->canViewResults($user->id);
        $results = $canViewResults ? $poll->calculateResults() : null;

        return view('member.polls.show', compact('poll', 'hasVoted', 'userVote', 'canViewResults', 'results'));
    }

    /**
     * Cast vote on a poll.
     */
    public function vote(Request $request, $id)
    {
        $request->validate([
            'poll_option_id' => 'required|integer|exists:poll_options,id',
        ]);

        $user = auth()->user();

        try {
            $this->pollService->vote(
                $user,
                (int) $id,
                (int) $request->input('poll_option_id'),
                $request->ip()
            );

            return redirect()->route('member.polls.show', $id)->with('success', 'Thank you! Your vote has been recorded successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            \Log::error('Member vote error: ' . $e->getMessage());
            return back()->with('error', 'Failed to submit vote. Please try again.');
        }
    }
}
