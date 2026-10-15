<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Society;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AmenityBookingService;
use App\Services\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * List tickets strictly for the admin's society.
     * "automatic show only resident admin jis society ka user hai"
     */
    public function index(Request $request)
    {
        $admin = auth()->user();
        $adminSocietyId = AmenityBookingService::getUserSocietyId($admin);

        $societies = Society::orderBy('name')->get();
        $query = Ticket::with(['society', 'user', 'assignee'])->latest();

        // Society isolation
        if ($admin->role !== 'super_admin' || !empty($adminSocietyId)) {
            $query->where('society_id', $adminSocietyId);
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

        return view('admin.tickets.index', compact('tickets', 'societies', 'adminSocietyId'));
    }

    /**
     * Show ticket details, message thread, and resolution panel.
     */
    public function show($id)
    {
        $admin = auth()->user();
        $adminSocietyId = AmenityBookingService::getUserSocietyId($admin);

        $ticket = Ticket::with(['society', 'user', 'assignee', 'replies.user'])->findOrFail($id);

        if ($admin->role !== 'super_admin' && (int) $ticket->society_id !== (int) $adminSocietyId) {
            abort(403, 'Unauthorized to view tickets of another society.');
        }

        $staffUsers = User::where(function ($q) use ($ticket) {
            $q->where('user_society_id', $ticket->society_id)
              ->orWhere('role', 'super_admin');
        })->get();

        return view('admin.tickets.show', compact('ticket', 'staffUsers'));
    }

    /**
     * Update ticket status.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'resolution_notes' => 'nullable|string|max:1000',
        ]);

        $admin = auth()->user();
        $adminSocietyId = AmenityBookingService::getUserSocietyId($admin);

        $ticket = Ticket::findOrFail($id);

        if ($admin->role !== 'super_admin' && (int) $ticket->society_id !== (int) $adminSocietyId) {
            abort(403);
        }

        $this->ticketService->updateStatus(
            $ticket,
            $admin,
            $request->input('status'),
            $request->input('resolution_notes')
        );

        return back()->with('success', "Ticket status updated to {$ticket->status}.");
    }

    /**
     * Admin post reply to ticket.
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $admin = auth()->user();
        $adminSocietyId = AmenityBookingService::getUserSocietyId($admin);

        $ticket = Ticket::findOrFail($id);

        if ($admin->role !== 'super_admin' && (int) $ticket->society_id !== (int) $adminSocietyId) {
            abort(403);
        }

        $isInternal = $request->boolean('is_internal');

        $this->ticketService->replyTicket(
            $ticket,
            $admin,
            $request->input('message'),
            $request->file('attachment'),
            $isInternal
        );

        return back()->with('success', 'Reply posted successfully.');
    }

    /**
     * Assign ticket to staff.
     */
    public function assign(Request $request, $id)
    {
        $request->validate(['assigned_to' => 'required|exists:users,id']);

        $admin = auth()->user();
        $adminSocietyId = AmenityBookingService::getUserSocietyId($admin);

        $ticket = Ticket::findOrFail($id);

        if ($admin->role !== 'super_admin' && (int) $ticket->society_id !== (int) $adminSocietyId) {
            abort(403);
        }

        $this->ticketService->assignTicket($ticket, $admin, (int) $request->input('assigned_to'));

        return back()->with('success', 'Ticket assigned successfully.');
    }
}
