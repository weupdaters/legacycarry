<?php

namespace App\Services;

use App\Models\Amenity;
use App\Models\AmenityBooking;
use App\Models\ResidentAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AmenityBookingService
{
    /**
     * Resolve effective society ID for a user.
     */
    public static function getUserSocietyId(User $user): ?int
    {
        if (!empty($user->user_society_id)) {
            return (int) $user->user_society_id;
        }

        if (!empty($user->details?->society_id)) {
            return (int) $user->details->society_id;
        }

        $assignment = ResidentAssignment::where('user_id', $user->id)->first();
        if (!empty($assignment?->society_id)) {
            return (int) $assignment->society_id;
        }

        return null;
    }

    /**
     * Compute available time slots for an amenity on a given date.
     */
    public function getAvailableSlots(Amenity $amenity, string $dateString, ?int $excludeBookingId = null): array
    {
        $date = Carbon::parse($dateString)->startOfDay();
        $today = Carbon::today();

        // 1. Cannot book past dates
        if ($date->lt($today)) {
            return [
                'date' => $dateString,
                'is_available_date' => false,
                'message' => 'Cannot view or book slots for past dates.',
                'slots' => [],
            ];
        }

        // 2. Advance booking window check
        $maxAdvanceDate = $today->copy()->addDays($amenity->advance_booking_days);
        if ($date->gt($maxAdvanceDate)) {
            return [
                'date' => $dateString,
                'is_available_date' => false,
                'message' => "Bookings can only be made up to {$amenity->advance_booking_days} days in advance.",
                'slots' => [],
            ];
        }

        // 3. Day of week availability check
        $dayName = $date->format('D'); // e.g. Mon, Tue
        if (!$amenity->isAvailableOnDay($dayName)) {
            return [
                'date' => $dateString,
                'is_available_date' => false,
                'message' => "Amenity is closed on {$date->format('l')}s.",
                'slots' => [],
            ];
        }

        // 4. Fetch existing booked or pending slots for this day
        $existingBookings = AmenityBooking::where('amenity_id', $amenity->id)
            ->where('booking_date', $dateString)
            ->whereIn('status', ['pending', 'confirmed'])
            ->when($excludeBookingId, fn($q) => $q->where('id', '!=', $excludeBookingId))
            ->get(['id', 'start_time', 'end_time', 'status', 'user_id']);

        // 5. Generate slots between opening_time and closing_time
        $openTime = Carbon::parse($dateString . ' ' . $amenity->opening_time);
        $closeTime = Carbon::parse($dateString . ' ' . $amenity->closing_time);
        $durationMinutes = $amenity->slot_duration_minutes ?: 60;

        $slots = [];
        $currentTime = $openTime->copy();
        $now = Carbon::now();

        while ($currentTime->copy()->addMinutes($durationMinutes)->lte($closeTime)) {
            $slotStart = $currentTime->format('H:i:s');
            $slotStartCarbon = $currentTime->copy();
            $slotEnd = $currentTime->copy()->addMinutes($durationMinutes)->format('H:i:s');
            $slotEndCarbon = $currentTime->copy()->addMinutes($durationMinutes);

            // Check if slot has already passed today
            $isPastSlot = $date->isToday() && $slotStartCarbon->lt($now);

            // Check overlap with existing pending/confirmed bookings
            // Overlap condition: max(start1, start2) < min(end1, end2)
            $conflictingBooking = $existingBookings->first(function ($booking) use ($slotStart, $slotEnd) {
                return $slotStart < $booking->end_time && $slotEnd > $booking->start_time;
            });

            $isAvailable = !$isPastSlot && !$conflictingBooking;
            $unavailableReason = null;

            if ($isPastSlot) {
                $unavailableReason = 'Time has passed';
            } elseif ($conflictingBooking) {
                $unavailableReason = $conflictingBooking->status === 'confirmed' ? 'Already booked' : 'Booking pending';
            }

            $slots[] = [
                'start_time' => substr($slotStart, 0, 5),
                'end_time' => substr($slotEnd, 0, 5),
                'is_available' => $isAvailable,
                'status' => $conflictingBooking ? $conflictingBooking->status : ($isPastSlot ? 'past' : 'available'),
                'reason' => $unavailableReason,
            ];

            $currentTime->addMinutes($durationMinutes);
        }

        return [
            'date' => $dateString,
            'is_available_date' => true,
            'amenity_name' => $amenity->name,
            'booking_mode' => $amenity->booking_mode,
            'fee' => (float) $amenity->fee,
            'slots' => $slots,
        ];
    }

    /**
     * Create a new booking request with concurrency lock and society verification.
     */
    public function createBooking(User $user, int $amenityId, string $dateString, string $startTime, string $endTime, ?string $adminNotes = null): AmenityBooking
    {
        return DB::transaction(function () use ($user, $amenityId, $dateString, $startTime, $endTime, $adminNotes) {
            // Standardize time format to H:i:s
            $startTimeNorm = strlen($startTime) === 5 ? $startTime . ':00' : $startTime;
            $endTimeNorm = strlen($endTime) === 5 ? $endTime . ':00' : $endTime;

            $amenity = Amenity::lockForUpdate()->findOrFail($amenityId);

            if ($amenity->status !== 'active') {
                throw new InvalidArgumentException('This amenity is currently inactive and cannot be booked.');
            }

            // Society verification
            $userSocietyId = self::getUserSocietyId($user);
            if (!$userSocietyId || (int) $userSocietyId !== (int) $amenity->society_id) {
                throw new InvalidArgumentException('You do not belong to the society for this amenity.');
            }

            $date = Carbon::parse($dateString)->startOfDay();
            $today = Carbon::today();

            if ($date->lt($today)) {
                throw new InvalidArgumentException('Bookings cannot be made for past dates.');
            }

            $maxAdvanceDate = $today->copy()->addDays($amenity->advance_booking_days);
            if ($date->gt($maxAdvanceDate)) {
                throw new InvalidArgumentException("Bookings can only be made up to {$amenity->advance_booking_days} days in advance.");
            }

            if (!$amenity->isAvailableOnDay($date->format('D'))) {
                throw new InvalidArgumentException("This amenity is closed on {$date->format('l')}s.");
            }

            // Check if slot is within amenity opening hours
            $startCarbon = Carbon::parse($dateString . ' ' . $startTimeNorm);
            $endCarbon = Carbon::parse($dateString . ' ' . $endTimeNorm);

            if ($endCarbon->lte($startCarbon)) {
                throw new InvalidArgumentException('End time must be after start time.');
            }

            if ($date->isToday() && $startCarbon->lt(Carbon::now())) {
                throw new InvalidArgumentException('Cannot book a time slot that has already passed today.');
            }

            $openCarbon = Carbon::parse($dateString . ' ' . $amenity->opening_time);
            $closeCarbon = Carbon::parse($dateString . ' ' . $amenity->closing_time);

            if ($startCarbon->lt($openCarbon) || $endCarbon->gt($closeCarbon)) {
                throw new InvalidArgumentException("Booking time must be between {$amenity->opening_time} and {$amenity->closing_time}.");
            }

            $durationMinutes = $startCarbon->diffInMinutes($endCarbon);
            if ($durationMinutes > $amenity->max_booking_duration_minutes) {
                throw new InvalidArgumentException("Booking duration ({$durationMinutes} mins) exceeds maximum allowed ({$amenity->max_booking_duration_minutes} mins).");
            }

            // CRITICAL CONCURRENCY LOCK: Check overlapping pending/confirmed bookings
            $overlap = AmenityBooking::where('amenity_id', $amenity->id)
                ->where('booking_date', $dateString)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where(function ($q) use ($startTimeNorm, $endTimeNorm) {
                    $q->where('start_time', '<', $endTimeNorm)
                      ->where('end_time', '>', $startTimeNorm);
                })
                ->lockForUpdate()
                ->first();

            if ($overlap) {
                throw new InvalidArgumentException('The selected time slot is unavailable or already booked. Please choose another time.');
            }

            // Determine status based on booking mode
            $status = $amenity->booking_mode === 'automatic' ? 'confirmed' : 'pending';
            $approvedAt = $status === 'confirmed' ? now() : null;

            $booking = AmenityBooking::create([
                'society_id' => $amenity->society_id,
                'amenity_id' => $amenity->id,
                'user_id' => $user->id,
                'booking_date' => $dateString,
                'start_time' => $startTimeNorm,
                'end_time' => $endTimeNorm,
                'status' => $status,
                'admin_notes' => $adminNotes,
                'approved_at' => $approvedAt,
            ]);

            // Notifications
            if ($status === 'confirmed') {
                SocietyNotificationService::notifyUser(
                    $user,
                    'Amenity Booking Confirmed',
                    "Your booking for {$amenity->name} on {$dateString} from " . substr($startTimeNorm, 0, 5) . " to " . substr($endTimeNorm, 0, 5) . " is confirmed.",
                    'amenity_booking_confirmed',
                    ['booking_id' => $booking->id, 'amenity_id' => $amenity->id]
                );
            } else {
                // Pending admin approval
                SocietyNotificationService::notifyUser(
                    $user,
                    'Amenity Booking Request Submitted',
                    "Your booking request for {$amenity->name} on {$dateString} has been submitted for admin approval.",
                    'amenity_booking_pending',
                    ['booking_id' => $booking->id, 'amenity_id' => $amenity->id]
                );

                SocietyNotificationService::notifyAdmins(
                    $amenity->society_id,
                    'New Amenity Booking Request',
                    "{$user->name} requested a booking for {$amenity->name} on {$dateString}.",
                    'admin_amenity_booking_pending',
                    ['booking_id' => $booking->id, 'amenity_id' => $amenity->id]
                );
            }

            return $booking;
        });
    }

    /**
     * Approve a pending booking request with concurrency lock.
     */
    public function approveBooking(AmenityBooking $booking, User $admin): AmenityBooking
    {
        return DB::transaction(function () use ($booking, $admin) {
            $lockedBooking = AmenityBooking::lockForUpdate()->findOrFail($booking->id);

            if ($lockedBooking->status !== 'pending') {
                throw new InvalidArgumentException("Only pending bookings can be approved. Current status: {$lockedBooking->status}.");
            }

            $bookingDateStr = \Carbon\Carbon::parse($lockedBooking->booking_date)->format('Y-m-d');

            // Re-verify no conflicting confirmed booking was created in parallel
            $overlap = AmenityBooking::where('amenity_id', $lockedBooking->amenity_id)
                ->where('booking_date', $bookingDateStr)
                ->where('id', '!=', $lockedBooking->id)
                ->where('status', 'confirmed')
                ->where(function ($q) use ($lockedBooking) {
                    $q->where('start_time', '<', $lockedBooking->end_time)
                      ->where('end_time', '>', $lockedBooking->start_time);
                })
                ->lockForUpdate()
                ->first();

            if ($overlap) {
                throw new InvalidArgumentException('Cannot approve booking: an overlapping confirmed booking already exists for this slot.');
            }

            $lockedBooking->update([
                'status' => 'confirmed',
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);

            // Notify resident
            SocietyNotificationService::notifyUser(
                $lockedBooking->user,
                'Amenity Booking Approved!',
                "Your booking request for {$lockedBooking->amenity->name} on " . $bookingDateStr . " has been approved.",
                'amenity_booking_approved',
                ['booking_id' => $lockedBooking->id]
            );

            return $lockedBooking;
        });
    }

    /**
     * Reject a pending booking request.
     */
    public function rejectBooking(AmenityBooking $booking, User $admin, string $reason): AmenityBooking
    {
        return DB::transaction(function () use ($booking, $admin, $reason) {
            $lockedBooking = AmenityBooking::lockForUpdate()->findOrFail($booking->id);

            if (!in_array($lockedBooking->status, ['pending', 'confirmed'])) {
                throw new InvalidArgumentException("Cannot reject a booking with status '{$lockedBooking->status}'.");
            }

            $lockedBooking->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
                'approved_by' => $admin->id,
            ]);

            SocietyNotificationService::notifyUser(
                $lockedBooking->user,
                'Amenity Booking Rejected',
                "Your booking for {$lockedBooking->amenity->name} was rejected. Reason: {$reason}",
                'amenity_booking_rejected',
                ['booking_id' => $lockedBooking->id, 'reason' => $reason]
            );

            return $lockedBooking;
        });
    }

    /**
     * Cancel a booking by resident or admin.
     */
    public function cancelBooking(AmenityBooking $booking, User $user, ?string $reason = null): AmenityBooking
    {
        return DB::transaction(function () use ($booking, $user, $reason) {
            $lockedBooking = AmenityBooking::lockForUpdate()->findOrFail($booking->id);

            // User must be owner or authorized admin
            $isOwner = (int) $lockedBooking->user_id === (int) $user->id;
            $isAdmin = $user->role === 'super_admin' || has_permission('amenity_booking_approve');

            if (!$isOwner && !$isAdmin) {
                throw new InvalidArgumentException('You are not authorized to cancel this booking.');
            }

            if (!$lockedBooking->canCancel() && !$isAdmin) {
                throw new InvalidArgumentException('This booking cannot be cancelled because it has already started or concluded.');
            }

            $lockedBooking->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'admin_notes' => $reason ? ($lockedBooking->admin_notes . "\nCancellation note: " . $reason) : $lockedBooking->admin_notes,
            ]);

            // Notify parties
            if ($isOwner) {
                SocietyNotificationService::notifyAdmins(
                    $lockedBooking->society_id,
                    'Amenity Booking Cancelled',
                    "Resident {$user->name} cancelled their booking for {$lockedBooking->amenity->name}.",
                    'amenity_booking_cancelled_by_user',
                    ['booking_id' => $lockedBooking->id]
                );
            } else {
                SocietyNotificationService::notifyUser(
                    $lockedBooking->user,
                    'Amenity Booking Cancelled by Admin',
                    "Your booking for {$lockedBooking->amenity->name} was cancelled by the society admin.",
                    'amenity_booking_cancelled_by_admin',
                    ['booking_id' => $lockedBooking->id]
                );
            }

            return $lockedBooking;
        });
    }
}
