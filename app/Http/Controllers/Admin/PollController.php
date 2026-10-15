<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\Society;
use App\Services\PollService;
use Illuminate\Http\Request;

class PollController extends Controller
{
    protected PollService $pollService;

    public function __construct(PollService $pollService)
    {
        $this->pollService = $pollService;
    }

    /**
     * List all polls with society and status filters.
     */
    public function index(Request $request)
    {
        $societies = Society::orderBy('name')->get();

        $query = Poll::with(['society', 'creator'])->withCount('votes');

        if ($request->filled('society_id')) {
            $query->where('society_id', $request->input('society_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $polls = $query->latest('start_date')->paginate(15);

        return view('admin.polls.index', compact('polls', 'societies'));
    }

    /**
     * Show create poll form.
     */
    public function create()
    {
        $societies = Society::orderBy('name')->get();
        return view('admin.polls.form', compact('societies'));
    }

    /**
     * Store new poll with 2 to 4 options.
     */
    public function store(Request $request)
    {
        $request->validate([
            'society_id' => 'required|exists:societies,id',
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
            $this->pollService->createPoll(
                auth()->user(),
                $request->only(['society_id', 'title', 'description', 'start_date', 'end_date', 'status', 'results_visibility', 'target_audience']),
                $request->input('options')
            );

            return redirect()->route('admin.polls.index')->with('success', 'Poll created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Show edit poll form.
     */
    public function edit($id)
    {
        $poll = Poll::with('options')->withCount('votes')->findOrFail($id);
        $societies = Society::orderBy('name')->get();

        return view('admin.polls.form', compact('poll', 'societies'));
    }

    /**
     * Update poll.
     */
    public function update(Request $request, $id)
    {
        $poll = Poll::findOrFail($id);

        $rules = [
            'society_id' => 'required|exists:societies,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:draft,scheduled,active,closed',
            'results_visibility' => 'required|in:always,after_vote,after_close',
            'target_audience' => 'required|in:all,owners,tenants',
        ];

        // Options only validated if poll has no votes yet
        if (!$poll->votes()->exists()) {
            $rules['options'] = 'required|array|min:2|max:4';
            $rules['options.*'] = 'required|string|max:255';
        }

        $request->validate($rules);

        try {
            $options = $poll->votes()->exists() ? null : $request->input('options');
            $this->pollService->updatePoll(
                $poll,
                $request->only(['society_id', 'title', 'description', 'start_date', 'end_date', 'status', 'results_visibility', 'target_audience']),
                $options
            );

            return redirect()->route('admin.polls.index')->with('success', 'Poll updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete poll.
     */
    public function destroy($id)
    {
        $poll = Poll::findOrFail($id);
        $poll->delete();

        return redirect()->route('admin.polls.index')->with('success', 'Poll deleted successfully.');
    }

    /**
     * Manually close poll.
     */
    public function close($id)
    {
        $poll = Poll::findOrFail($id);
        $this->pollService->closePoll($poll, auth()->user());

        return back()->with('success', 'Poll closed successfully.');
    }

    /**
     * View detailed poll results with percentages and tied/winning options.
     */
    public function results($id)
    {
        $poll = Poll::with(['options', 'society', 'creator', 'votes.user'])->findOrFail($id);
        $results = $poll->calculateResults();

        return view('admin.polls.results', compact('poll', 'results'));
    }
}
