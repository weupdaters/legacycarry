<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\AmenityBookingService;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class TicketApiController extends Controller
{
    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Resident: Create a support / complaint ticket for any issue.
     * Automatically assigns to the resident's society.
     * POST /api/v1/tickets
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
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
            $ticket = $this->ticketService->createTicket(
                $user,
                $request->only(['category', 'title', 'description', 'priority']),
                $request->file('attachment')
            );

            return response()->json([
                'success' => true,
                'status' => true,
                'message' => "Ticket #{$ticket->ticket_number} created successfully.",
                'data' => [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'society_id' => $ticket->society_id,
                    'category' => $ticket->category,
                    'title' => $ticket->title,
                    'description' => $ticket->description,
                    'priority' => $ticket->priority,
                    'status' => $ticket->status,
                    'attachment_url' => $ticket->attachment_url,
                    'created_at' => $ticket->created_at->format('Y-m-d H:i:s'),
                ],
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Throwable $e) {
            \Log::error('TicketApiController store exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'An error occurred while creating the ticket.',
            ], 500);
        }
    }

    /**
     * Resident / Resident Admin: List tickets.
     * - Resident Admin: Defaults to all tickets of their society (can pass scope=my for own).
     * - Normal Resident: Returns their own tickets.
     * GET /api/v1/tickets
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user) ?: (function_exists('getSocietyId') ? getSocietyId() : null);

        $isResidentAdmin = has_permission('ticket_index')
            || has_permission('ticket_show')
            || has_permission('ticket_reply')
            || has_permission('ticket_status_update')
            || has_permission('resident_index')
            || $user->role === 'super_admin';

        $scope = $request->input('scope', ($isResidentAdmin && $societyId) ? 'society' : 'my');

        $query = Ticket::with(['user', 'society'])->latest();

        if ($scope === 'society' && $isResidentAdmin && $societyId) {
            $query->where('society_id', $societyId);
        } else {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $tickets = $query->paginate(15);

        $data = $tickets->getCollection()->map(function ($ticket) {
            return [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'society_id' => $ticket->society_id,
                'resident_id' => $ticket->user_id,
                'resident_name' => $ticket->user?->name,
                'resident_phone' => $ticket->user?->phone,
                'category' => $ticket->category,
                'title' => $ticket->title,
                'priority' => $ticket->priority,
                'status' => $ticket->status,
                'attachment_url' => $ticket->attachment_url,
                'resolved_at' => optional($ticket->resolved_at)->format('Y-m-d H:i:s'),
                'created_at' => $ticket->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Tickets fetched successfully',
            'data' => $data,
            'pagination' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ],
        ]);
    }

    /**
     * Resident / Admin: View single ticket details with replies thread.
     * GET /api/v1/tickets/{id}
     */
    public function show($id): JsonResponse
    {
        $user = auth()->user();
        $ticket = Ticket::with(['society', 'user', 'assignee', 'replies.user'])->find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Ticket not found.',
            ], 404);
        }

        $userSocietyId = AmenityBookingService::getUserSocietyId($user);
        $isOwner = (int) $ticket->user_id === (int) $user->id;
        $isSocietyAdmin = (int) $ticket->society_id === (int) $userSocietyId && ($user->role === 'super_admin' || has_permission('ticket_show'));

        if (!$isOwner && !$isSocietyAdmin && $user->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Unauthorized to view this ticket.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Ticket details fetched successfully',
            'data' => [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'society' => [
                    'id' => $ticket->society->id,
                    'name' => $ticket->society->name,
                ],
                'resident' => [
                    'id' => $ticket->user->id,
                    'name' => $ticket->user->name,
                    'phone' => $ticket->user->phone,
                ],
                'assigned_to' => $ticket->assignee ? [
                    'id' => $ticket->assignee->id,
                    'name' => $ticket->assignee->name,
                ] : null,
                'category' => $ticket->category,
                'title' => $ticket->title,
                'description' => $ticket->description,
                'priority' => $ticket->priority,
                'status' => $ticket->status,
                'attachment_url' => $ticket->attachment_url,
                'resolution_notes' => $ticket->resolution_notes,
                'resolved_at' => optional($ticket->resolved_at)->format('Y-m-d H:i:s'),
                'closed_at' => optional($ticket->closed_at)->format('Y-m-d H:i:s'),
                'created_at' => $ticket->created_at->format('Y-m-d H:i:s'),
                'replies' => $ticket->replies->map(function ($reply) {
                    return [
                        'id' => $reply->id,
                        'user_id' => $reply->user_id,
                        'user_name' => $reply->user?->name,
                        'user_role' => $reply->user?->role,
                        'message' => $reply->message,
                        'attachment_url' => $reply->attachment_url,
                        'created_at' => $reply->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
            ],
        ]);
    }

    /**
     * Resident / Admin: Post a reply to a ticket thread.
     * POST /api/v1/tickets/{id}/reply
     */
    public function reply(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
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
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Ticket not found.',
            ], 404);
        }

        $userSocietyId = AmenityBookingService::getUserSocietyId($user);
        $isOwner = (int) $ticket->user_id === (int) $user->id;
        $isSocietyAdmin = (int) $ticket->society_id === (int) $userSocietyId;

        if (!$isOwner && !$isSocietyAdmin && $user->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Unauthorized to reply to this ticket.',
            ], 403);
        }

        $isInternal = $request->boolean('is_internal') && $isSocietyAdmin;

        $reply = $this->ticketService->replyTicket(
            $ticket,
            $user,
            $request->input('message'),
            $request->file('attachment'),
            $isInternal
        );

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Reply posted successfully.',
            'data' => [
                'id' => $reply->id,
                'ticket_id' => $reply->ticket_id,
                'message' => $reply->message,
                'attachment_url' => $reply->attachment_url,
                'created_at' => $reply->created_at->format('Y-m-d H:i:s'),
            ],
        ], 201);
    }

    /**
     * Resident: Close their own ticket once resolved.
     * POST /api/v1/tickets/{id}/close
     */
    public function close($id): JsonResponse
    {
        $user = auth()->user();
        $ticket = Ticket::where('id', $id)->where('user_id', $user->id)->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Ticket not found or unauthorized.',
            ], 404);
        }

        $this->ticketService->updateStatus($ticket, $user, 'closed', 'Closed by resident.');

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Ticket closed successfully.',
            'data' => [
                'id' => $ticket->id,
                'status' => 'closed',
            ],
        ]);
    }

    /**
     * Admin: List tickets strictly for the admin's society.
     * "automatic show only resident admin jis society ka user hai"
     * GET /api/v1/admin/tickets
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $admin = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($admin);

        $query = Ticket::with(['user', 'society'])->latest();

        // Society isolation: If not super_admin, force strictly to admin's society
        if ($admin->role !== 'super_admin' || !empty($societyId)) {
            $query->where('society_id', $societyId);
        } elseif ($request->filled('society_id')) {
            $query->where('society_id', $request->input('society_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $tickets = $query->paginate(20);

        $data = $tickets->getCollection()->map(function ($ticket) {
            return [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'society_id' => $ticket->society_id,
                'society_name' => $ticket->society?->name,
                'resident_id' => $ticket->user?->id,
                'resident_name' => $ticket->user?->name,
                'resident_phone' => $ticket->user?->phone,
                'category' => $ticket->category,
                'title' => $ticket->title,
                'priority' => $ticket->priority,
                'status' => $ticket->status,
                'attachment_url' => $ticket->attachment_url,
                'created_at' => $ticket->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Society tickets fetched successfully',
            'data' => $data,
            'pagination' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ],
        ]);
    }

    /**
     * Admin: Update ticket status (in_progress, resolved, closed).
     * PATCH /api/v1/admin/tickets/{id}/status
     */
    public function adminUpdateStatus(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:open,in_progress,resolved,closed',
            'resolution_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $admin = auth()->user();
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Ticket not found.',
            ], 404);
        }

        // Verify that this ticket belongs to the admin's society
        $adminSocietyId = AmenityBookingService::getUserSocietyId($admin);
        if ($admin->role !== 'super_admin' && (int) $ticket->society_id !== (int) $adminSocietyId) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Unauthorized. This ticket does not belong to your society.',
            ], 403);
        }

        $this->ticketService->updateStatus(
            $ticket,
            $admin,
            $request->input('status'),
            $request->input('resolution_notes')
        );

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => "Ticket status updated to {$ticket->status}.",
            'data' => [
                'id' => $ticket->id,
                'status' => $ticket->status,
                'resolution_notes' => $ticket->resolution_notes,
                'resolved_at' => optional($ticket->resolved_at)->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Admin: Assign ticket.
     * POST /api/v1/admin/tickets/{id}/assign
     */
    public function adminAssign(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'assigned_to' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $admin = auth()->user();
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Ticket not found.',
            ], 404);
        }

        $adminSocietyId = AmenityBookingService::getUserSocietyId($admin);
        if ($admin->role !== 'super_admin' && (int) $ticket->society_id !== (int) $adminSocietyId) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $this->ticketService->assignTicket($ticket, $admin, (int) $request->input('assigned_to'));

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Ticket assigned successfully.',
        ]);
    }
}
