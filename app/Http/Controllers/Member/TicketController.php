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
     * List authenticated resident's tickets.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user);

        if (!$societyId) {
            return view('member.tickets.index', [
                'tickets' => collect(),
                'error' => 'You are not assigned to any society.',
            ]);
        }

        $query = Ticket::where('user_id', $user->id)->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $tickets = $query->paginate(15);

        return view('member.tickets.index', compact('tickets'));
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
     * Show ticket conversation thread.
     */
    public function show($id)
    {
        $user = auth()->user();
        $ticket = Ticket::with(['replies.user'])->where('id', $id)->where('user_id', $user->id)->firstOrFail();

        return view('member.tickets.show', compact('ticket'));
    }

    /**
     * Post a reply to ticket.
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $user = auth()->user();
        $ticket = Ticket::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        $this->ticketService->replyTicket(
            $ticket,
            $user,
            $request->input('message'),
            $request->file('attachment')
        );

        return back()->with('success', 'Reply posted successfully.');
    }

    /**
     * Close ticket.
     */
    public function close($id)
    {
        $user = auth()->user();
        $ticket = Ticket::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        $this->ticketService->updateStatus($ticket, $user, 'closed', 'Closed by resident.');

        return back()->with('success', 'Ticket closed.');
    }
}
