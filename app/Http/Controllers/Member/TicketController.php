<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
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
     * List tickets:
     * - Resident Admin: Sees all complaints of their society with a toggle to view "My Complaints".
     * - Normal Resident: Sees only their own complaints.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user) ?: getSocietyId();

        if (!$societyId) {
            return view('member.tickets.index', [
                'tickets' => collect(),
                'isResidentAdmin' => false,
                'activeTab' => 'my',
                'societyTicketsCount' => 0,
                'myTicketsCount' => 0,
                'error' => 'You are not assigned to any society.',
            ]);
        }

        $isResidentAdmin = has_permission('ticket_index')
            || has_permission('ticket_show')
            || has_permission('ticket_reply')
            || has_permission('ticket_status_update')
            || has_permission('resident_index')
            || $user->role === 'super_admin';

        $activeTab = $request->input('view', $isResidentAdmin ? 'society' : 'my');

        $query = Ticket::with(['user.details.flat', 'society'])->latest();

        if ($activeTab === 'society' && $isResidentAdmin) {
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

        $societyTicketsCount = $isResidentAdmin ? Ticket::where('society_id', $societyId)->count() : 0;
        $myTicketsCount = Ticket::where('user_id', $user->id)->count();

        return view('member.tickets.index', compact(
            'tickets',
            'isResidentAdmin',
            'activeTab',
            'societyTicketsCount',
            'myTicketsCount'
        ));
    }

    /**
     * Show form to raise a new complaint / ticket.
     */
    public function create()
    {
        $categories = [
            'Plumbing' => 'Plumbing & Water leakage',
            'Electrical' => 'Electrical & Lighting',
            'Maintenance' => 'Civil / Lift / Common Area',
            'Security' => 'Security / Guard Issue',
            'Cleanliness' => 'Garbage / Housekeeping',
            'Noise' => 'Noise & Disturbance',
            'Parking' => 'Parking Issue',
            'Other' => 'General / Other Issue',
        ];

        return view('member.tickets.create', compact('categories'));
    }

    /**
     * Store new ticket.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $user = auth()->user();

        try {
            $ticket = $this->ticketService->createTicket(
                $user,
                $request->only(['category', 'title', 'description', 'priority']),
                $request->file('attachment')
            );

            return redirect()->route('member.tickets.show', $ticket->id)
                ->with('success', "Ticket #{$ticket->ticket_number} created and sent to your society management.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Show ticket conversation thread:
     * - Resident Admin: Can view any ticket belonging to their society.
     * - Normal Resident: Can view only their own ticket.
     */
    public function show($id)
    {
        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user) ?: getSocietyId();

        $isResidentAdmin = has_permission('ticket_show')
            || has_permission('ticket_index')
            || has_permission('ticket_reply')
            || has_permission('ticket_status_update')
            || has_permission('resident_index')
            || $user->role === 'super_admin';

        $query = Ticket::with(['replies.user', 'user.details.flat', 'society', 'assignee'])->where('id', $id);

        if ($isResidentAdmin && $societyId) {
            $query->where('society_id', $societyId);
        } else {
            $query->where('user_id', $user->id);
        }

        $ticket = $query->firstOrFail();

        return view('member.tickets.show', compact('ticket', 'isResidentAdmin'));
    }

    /**
     * Post a reply to ticket thread.
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user) ?: getSocietyId();

        $isResidentAdmin = has_permission('ticket_reply')
            || has_permission('ticket_index')
            || has_permission('ticket_status_update')
            || has_permission('resident_index')
            || $user->role === 'super_admin';

        $query = Ticket::where('id', $id);

        if ($isResidentAdmin && $societyId) {
            $query->where('society_id', $societyId);
        } else {
            $query->where('user_id', $user->id);
        }

        $ticket = $query->firstOrFail();

        $this->ticketService->replyTicket(
            $ticket,
            $user,
            $request->input('message'),
            $request->file('attachment')
        );

        return back()->with('success', 'Reply posted successfully.');
    }

    /**
     * Update ticket status (Resident Admin).
     */
    public function updateStatus(Request $request, $id)
    {
        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user) ?: getSocietyId();

        $isResidentAdmin = has_permission('ticket_status_update')
            || has_permission('ticket_edit')
            || has_permission('ticket_index')
            || has_permission('resident_index')
            || $user->role === 'super_admin';

        abort_unless($isResidentAdmin, 403, 'Unauthorized to update ticket status.');

        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'resolution_notes' => 'nullable|string|max:1000',
        ]);

        $ticket = Ticket::where('id', $id)->where('society_id', $societyId)->firstOrFail();

        $this->ticketService->updateStatus(
            $ticket,
            $user,
            $request->input('status'),
            $request->input('resolution_notes')
        );

        return back()->with('success', 'Ticket status updated to ' . ucfirst(str_replace('_', ' ', $request->input('status'))) . '.');
    }

    /**
     * Close ticket.
     */
    public function close($id)
    {
        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user) ?: getSocietyId();

        $isResidentAdmin = has_permission('ticket_status_update')
            || has_permission('resident_index')
            || $user->role === 'super_admin';

        $query = Ticket::where('id', $id);

        if ($isResidentAdmin && $societyId) {
            $query->where('society_id', $societyId);
        } else {
            $query->where('user_id', $user->id);
        }

        $ticket = $query->firstOrFail();

        $this->ticketService->updateStatus($ticket, $user, 'closed', 'Closed by resident/management.');

        return back()->with('success', 'Ticket closed.');
    }
}
