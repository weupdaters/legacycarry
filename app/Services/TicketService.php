<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class TicketService
{
    /**
     * Create a new complaint / support ticket for a resident.
     * Automatically assigns to the resident's society.
     */
    public function createTicket(User $user, array $data, $attachment = null): Ticket
    {
        return DB::transaction(function () use ($user, $data, $attachment) {
            $societyId = AmenityBookingService::getUserSocietyId($user);

            if (!$societyId) {
                throw new InvalidArgumentException('You are not assigned to any society.');
            }

            // Generate unique human-readable ticket number (e.g. TKT-20261015-A1B2)
            $ticketNumber = 'TKT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
            while (Ticket::where('ticket_number', $ticketNumber)->exists()) {
                $ticketNumber = 'TKT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
            }

            $attachmentPath = null;
            if ($attachment instanceof UploadedFile) {
                $attachmentPath = $attachment->store('tickets', 'public');
            } elseif (is_string($attachment) && !empty($attachment)) {
                $attachmentPath = $attachment;
            }

            $ticket = Ticket::create([
                'ticket_number' => $ticketNumber,
                'society_id' => $societyId,
                'user_id' => $user->id,
                'category' => $data['category'],
                'title' => $data['title'],
                'description' => $data['description'],
                'priority' => $data['priority'] ?? 'medium',
                'status' => 'open',
                'attachment' => $attachmentPath,
            ]);

            // Notify Society Admins
            SocietyNotificationService::notifyAdmins(
                $societyId,
                "New Ticket #{$ticket->ticket_number}",
                "{$user->name} raised a {$ticket->category} ticket: {$ticket->title}",
                'new_ticket_created',
                ['ticket_id' => $ticket->id, 'ticket_number' => $ticket->ticket_number]
            );

            // Confirm to Resident
            SocietyNotificationService::notifyUser(
                $user,
                "Ticket #{$ticket->ticket_number} Submitted",
                "Your complaint '{$ticket->title}' has been submitted to your society management.",
                'ticket_submitted',
                ['ticket_id' => $ticket->id, 'ticket_number' => $ticket->ticket_number]
            );

            return $ticket;
        });
    }

    /**
     * Post a reply to a ticket thread.
     */
    public function replyTicket(Ticket $ticket, User $user, string $message, $attachment = null, bool $isInternal = false): TicketReply
    {
        return DB::transaction(function () use ($ticket, $user, $message, $attachment, $isInternal) {
            $attachmentPath = null;
            if ($attachment instanceof UploadedFile) {
                $attachmentPath = $attachment->store('tickets/replies', 'public');
            }

            $reply = TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'message' => $message,
                'attachment' => $attachmentPath,
                'is_internal' => $isInternal,
            ]);

            $isResident = (int) $ticket->user_id === (int) $user->id;

            // If resident replied to a resolved ticket, auto re-open
            if ($isResident && in_array($ticket->status, ['resolved', 'closed'])) {
                $ticket->update(['status' => 'open']);
            }

            // Notification
            if ($isResident) {
                SocietyNotificationService::notifyAdmins(
                    $ticket->society_id,
                    "New Reply on Ticket #{$ticket->ticket_number}",
                    "Resident {$user->name} replied on ticket: {$ticket->title}",
                    'ticket_reply_from_resident',
                    ['ticket_id' => $ticket->id]
                );
            } else {
                // Admin replied -> Notify resident
                if (!$isInternal) {
                    SocietyNotificationService::notifyUser(
                        $ticket->user,
                        "Update on Ticket #{$ticket->ticket_number}",
                        "Management replied: " . Str::limit($message, 100),
                        'ticket_reply_from_admin',
                        ['ticket_id' => $ticket->id]
                    );
                }
            }

            return $reply;
        });
    }

    /**
     * Update ticket status (open -> in_progress -> resolved -> closed).
     */
    public function updateStatus(Ticket $ticket, User $user, string $status, ?string $resolutionNotes = null): Ticket
    {
        return DB::transaction(function () use ($ticket, $user, $status, $resolutionNotes) {
            $updateData = ['status' => $status];

            if ($resolutionNotes !== null) {
                $updateData['resolution_notes'] = $resolutionNotes;
            }

            if ($status === 'resolved' && empty($ticket->resolved_at)) {
                $updateData['resolved_at'] = now();
            }

            if ($status === 'closed' && empty($ticket->closed_at)) {
                $updateData['closed_at'] = now();
            }

            $ticket->update($updateData);

            // Notify Resident
            $statusLabel = ucfirst(str_replace('_', ' ', $status));
            SocietyNotificationService::notifyUser(
                $ticket->user,
                "Ticket #{$ticket->ticket_number} {$statusLabel}",
                "Your ticket status has been updated to '{$statusLabel}'." . ($resolutionNotes ? " Note: {$resolutionNotes}" : ""),
                'ticket_status_changed',
                ['ticket_id' => $ticket->id, 'status' => $status]
            );

            return $ticket;
        });
    }

    /**
     * Assign ticket to an admin or staff member.
     */
    public function assignTicket(Ticket $ticket, User $admin, int $assigneeId): Ticket
    {
        $assignee = User::findOrFail($assigneeId);
        $ticket->update(['assigned_to' => $assignee->id]);

        SocietyNotificationService::notifyUser(
            $assignee,
            "Ticket #{$ticket->ticket_number} Assigned to You",
            "You have been assigned to handle ticket: {$ticket->title}",
            'ticket_assigned',
            ['ticket_id' => $ticket->id]
        );

        return $ticket;
    }
}
