<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Services\AmenityBookingService;
use App\Services\PollService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class PollApiController extends Controller
{
    protected PollService $pollService;

    public function __construct(PollService $pollService)
    {
        $this->pollService = $pollService;
    }

    /**
     * List accessible polls for the authenticated resident's society.
     * GET /api/v1/polls
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user);

        if (!$societyId) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'User is not assigned to any society.',
                'data' => [],
            ], 403);
        }

        $now = Carbon::now();
        $statusFilter = $request->input('status', 'active');

        $query = Poll::with(['options'])
            ->where('society_id', $societyId);

        if ($statusFilter === 'active') {
            $query->where('status', 'active')
                  ->where('start_date', '<=', $now)
                  ->where('end_date', '>=', $now);
        } elseif ($statusFilter === 'closed') {
            $query->where(function ($q) use ($now) {
                $q->where('status', 'closed')
                  ->orWhere('end_date', '<', $now);
            });
        }

        $polls = $query->latest('start_date')->paginate(15);

        $data = $polls->getCollection()->map(function ($poll) use ($user) {
            $userVote = $poll->getUserVote($user->id);
            $hasVoted = !is_null($userVote);
            $canView = $poll->canViewResults($user->id);

            return [
                'id' => $poll->id,
                'title' => $poll->title,
                'description' => $poll->description,
                'start_date' => $poll->start_date->format('Y-m-d H:i:s'),
                'end_date' => $poll->end_date->format('Y-m-d H:i:s'),
                'status' => $poll->status,
                'is_open' => $poll->isVotingOpen(),
                'results_visibility' => $poll->results_visibility,
                'has_voted' => $hasVoted,
                'user_selected_option_id' => $userVote?->poll_option_id,
                'can_view_results' => $canView,
                'options' => $poll->options->map(function ($opt) {
                    return [
                        'id' => $opt->id,
                        'option_text' => $opt->option_text,
                    ];
                }),
                'results' => $canView ? $poll->calculateResults() : null,
            ];
        });

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Polls fetched successfully',
            'data' => $data,
            'pagination' => [
                'current_page' => $polls->currentPage(),
                'last_page' => $polls->lastPage(),
                'per_page' => $polls->perPage(),
                'total' => $polls->total(),
            ],
        ]);
    }

    /**
     * Get single poll details.
     * GET /api/v1/polls/{id}
     */
    public function show($id): JsonResponse
    {
        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user);

        $poll = Poll::with('options')->find($id);

        if (!$poll) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Poll not found',
            ], 404);
        }

        if ($user->role !== 'super_admin' && (int) $poll->society_id !== (int) $societyId) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Unauthorized access to this poll.',
            ], 403);
        }

        $userVote = $poll->getUserVote($user->id);
        $hasVoted = !is_null($userVote);
        $canViewResults = $poll->canViewResults($user->id);

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Poll details fetched successfully',
            'data' => [
                'id' => $poll->id,
                'title' => $poll->title,
                'description' => $poll->description,
                'start_date' => $poll->start_date->format('Y-m-d H:i:s'),
                'end_date' => $poll->end_date->format('Y-m-d H:i:s'),
                'status' => $poll->status,
                'is_open' => $poll->isVotingOpen(),
                'results_visibility' => $poll->results_visibility,
                'target_audience' => $poll->target_audience,
                'has_voted' => $hasVoted,
                'user_selected_option_id' => $userVote?->poll_option_id,
                'voted_at' => optional($userVote?->voted_at)->format('Y-m-d H:i:s'),
                'can_view_results' => $canViewResults,
                'options' => $poll->options->map(function ($opt) {
                    return [
                        'id' => $opt->id,
                        'option_text' => $opt->option_text,
                    ];
                }),
                'results' => $canViewResults ? $poll->calculateResults() : null,
            ],
        ]);
    }

    /**
     * Submit a vote for a poll.
     * POST /api/v1/polls/{id}/vote
     */
    public function vote(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'poll_option_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();

        try {
            $vote = $this->pollService->vote(
                $user,
                (int) $id,
                (int) $request->input('poll_option_id'),
                $request->ip()
            );

            $poll = Poll::with('options')->find($id);
            $canView = $poll->canViewResults($user->id);

            return response()->json([
                'success' => true,
                'status' => true,
                'message' => 'Your vote has been submitted successfully!',
                'data' => [
                    'poll_id' => $poll->id,
                    'selected_option_id' => $vote->poll_option_id,
                    'voted_at' => $vote->voted_at->format('Y-m-d H:i:s'),
                    'results' => $canView ? $poll->calculateResults() : null,
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => $e->getMessage(),
            ], 409); // 409 Conflict for duplicate vote or closed poll
        } catch (\Throwable $e) {
            \Log::error('Poll vote error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'An error occurred while submitting your vote.',
            ], 500);
        }
    }

    /**
     * Get authorized poll results.
     * GET /api/v1/polls/{id}/results
     */
    public function results($id): JsonResponse
    {
        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user);

        $poll = Poll::find($id);

        if (!$poll) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Poll not found',
            ], 404);
        }

        if ($user->role !== 'super_admin' && (int) $poll->society_id !== (int) $societyId) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Unauthorized access to this poll.',
            ], 403);
        }

        if (!$poll->canViewResults($user->id) && $user->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Results are not visible yet according to poll settings. Please cast your vote or wait until the poll closes.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Poll results calculated successfully',
            'data' => [
                'poll_id' => $poll->id,
                'title' => $poll->title,
                'status' => $poll->status,
                'results' => $poll->calculateResults(),
            ],
        ]);
    }

    /**
     * Get polls participated in by authenticated resident.
     * GET /api/v1/my-polls
     */
    public function myPolls(Request $request): JsonResponse
    {
        $user = auth()->user();

        $polls = Poll::whereHas('votes', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with(['options'])
        ->latest()
        ->paginate(15);

        $data = $polls->getCollection()->map(function ($poll) use ($user) {
            $vote = $poll->getUserVote($user->id);
            return [
                'id' => $poll->id,
                'title' => $poll->title,
                'description' => $poll->description,
                'status' => $poll->status,
                'selected_option_id' => $vote?->poll_option_id,
                'voted_at' => optional($vote?->voted_at)->format('Y-m-d H:i:s'),
                'results' => $poll->calculateResults(),
            ];
        });

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'User participating polls fetched successfully',
            'data' => $data,
            'pagination' => [
                'current_page' => $polls->currentPage(),
                'last_page' => $polls->lastPage(),
                'per_page' => $polls->perPage(),
                'total' => $polls->total(),
            ],
        ]);
    }
}
