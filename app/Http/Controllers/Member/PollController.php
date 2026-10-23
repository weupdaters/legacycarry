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
     * Check if user is Resident Admin or Super Admin.
     */
    protected function isResidentAdmin(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->role === 'super_admin'
            || has_permission('poll_create')
            || has_permission('poll_edit')
            || has_permission('poll_delete')
            || has_permission('poll_close')
            || has_permission('resident_index');
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

        $isResidentAdmin = $this->isResidentAdmin();

        return view('member.polls.index', compact('activePolls', 'closedPolls', 'isResidentAdmin'));
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

    /**
     * Show create poll form for Resident Admin.
     */
    public function create()
    {
        abort_unless($this->isResidentAdmin(), 403, 'Unauthorized to create polls.');

        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user);
        if (!$societyId) {
            return back()->with('error', 'You are not assigned to any society.');
        }

        return view('member.polls.form', compact('societyId'));
    }

    /**
     * Store new poll for Resident Admin.
     */
    public function store(Request $request)
    {
        abort_unless($this->isResidentAdmin(), 403, 'Unauthorized to create polls.');

        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user);
        if (!$societyId) {
            return back()->with('error', 'You are not assigned to any society.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:draft,scheduled,active,closed',
            'results_visibility' => 'required|in:always,after_vote,after_close',
            'target_audience' => 'required|in:all,owners,tenants',
            'options' => 'required|array|min:2|max:4',
            'options.*' => 'required|string|max:255',
        ]);

        try {
            $data = $request->only(['title', 'description', 'start_date', 'end_date', 'status', 'results_visibility', 'target_audience']);
            $data['society_id'] = $societyId;

            $this->pollService->createPoll(
                $user,
                $data,
                $request->input('options')
            );

            return redirect()->route('member.polls.index')->with('success', 'Poll created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Show edit poll form for Resident Admin.
     */
    public function edit($id)
    {
        abort_unless($this->isResidentAdmin(), 403, 'Unauthorized to edit polls.');

        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user);

        $poll = Poll::with('options')->withCount('votes')
            ->where('id', $id)
            ->where('society_id', $societyId)
            ->firstOrFail();

        return view('member.polls.form', compact('poll', 'societyId'));
    }

    /**
     * Update poll for Resident Admin.
     */
    public function update(Request $request, $id)
    {
        abort_unless($this->isResidentAdmin(), 403, 'Unauthorized to update polls.');

        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user);

        $poll = Poll::where('id', $id)->where('society_id', $societyId)->firstOrFail();

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:draft,scheduled,active,closed',
            'results_visibility' => 'required|in:always,after_vote,after_close',
            'target_audience' => 'required|in:all,owners,tenants',
        ];

        // Options can only be updated if no votes have been cast yet
        if (!$poll->votes()->exists()) {
            $rules['options'] = 'required|array|min:2|max:4';
            $rules['options.*'] = 'required|string|max:255';
        }

        $request->validate($rules);

        try {
            $this->pollService->updatePoll(
                $poll,
                $request->only(['title', 'description', 'start_date', 'end_date', 'status', 'results_visibility', 'target_audience']),
                $poll->votes()->exists() ? null : $request->input('options')
            );

            return redirect()->route('member.polls.index')->with('success', 'Poll updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete poll for Resident Admin.
     */
    public function destroy($id)
    {
        abort_unless($this->isResidentAdmin(), 403, 'Unauthorized to delete polls.');

        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user);

        $poll = Poll::where('id', $id)->where('society_id', $societyId)->firstOrFail();
        $poll->delete();

        return redirect()->route('member.polls.index')->with('success', 'Poll deleted successfully.');
    }

    /**
     * Close poll early (Resident Admin).
     */
    public function close($id)
    {
        abort_unless($this->isResidentAdmin(), 403, 'Unauthorized to close polls.');

        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user);

        $poll = Poll::where('id', $id)->where('society_id', $societyId)->firstOrFail();

        try {
            $this->pollService->closePoll($poll, $user);
            return back()->with('success', 'Poll closed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * View detailed poll results (Resident Admin / Eligible Residents).
     */
    public function results($id)
    {
        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user);

        $poll = Poll::with(['options', 'votes.user'])
            ->where('id', $id)
            ->where('society_id', $societyId)
            ->firstOrFail();

        // Check if user is allowed to view results
        if (!$poll->canViewResults($user->id) && !$this->isResidentAdmin() && !has_permission('poll_results')) {
            abort(403, 'Poll results are not visible yet.');
        }

        $results = $poll->calculateResults();

        return view('member.polls.results', compact('poll', 'results'));
    }
}
