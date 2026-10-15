<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\AmenityBooking;
use App\Services\AmenityBookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class AmenityApiController extends Controller
{
    protected AmenityBookingService $bookingService;

    public function __construct(AmenityBookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * List active amenities for the authenticated resident's society.
     * GET /api/v1/amenities
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

        $amenities = Amenity::where('society_id', $societyId)
            ->active()
            ->orderBy('name')
            ->get()
            ->map(function ($amenity) {
                return [
                    'id' => $amenity->id,
                    'name' => $amenity->name,
                    'description' => $amenity->description,
                    'image' => $amenity->image_url,
                    'booking_mode' => $amenity->booking_mode,
                    'opening_time' => substr($amenity->opening_time, 0, 5),
                    'closing_time' => substr($amenity->closing_time, 0, 5),
                    'available_days' => $amenity->available_days,
                    'slot_duration_minutes' => $amenity->slot_duration_minutes,
                    'max_booking_duration_minutes' => $amenity->max_booking_duration_minutes,
                    'advance_booking_days' => $amenity->advance_booking_days,
                    'fee' => (float) $amenity->fee,
                ];
            });

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Amenities fetched successfully',
            'data' => $amenities,
        ]);
    }

    /**
     * Get single amenity details.
     * GET /api/v1/amenities/{id}
     */
    public function show($id): JsonResponse
    {
        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user);

        $amenity = Amenity::where('id', $id)->first();

        if (!$amenity) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Amenity not found',
            ], 404);
        }

        if ($user->role !== 'super_admin' && (int) $amenity->society_id !== (int) $societyId) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Unauthorized access to this society amenity.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Amenity details fetched successfully',
            'data' => [
                'id' => $amenity->id,
                'society_id' => $amenity->society_id,
                'name' => $amenity->name,
                'description' => $amenity->description,
                'image' => $amenity->image_url,
                'status' => $amenity->status,
                'booking_mode' => $amenity->booking_mode,
                'opening_time' => substr($amenity->opening_time, 0, 5),
                'closing_time' => substr($amenity->closing_time, 0, 5),
                'available_days' => $amenity->available_days,
                'slot_duration_minutes' => $amenity->slot_duration_minutes,
                'max_booking_duration_minutes' => $amenity->max_booking_duration_minutes,
                'advance_booking_days' => $amenity->advance_booking_days,
                'fee' => (float) $amenity->fee,
            ],
        ]);
    }

    /**
     * Get slot availability for an amenity on a specific date.
     * GET /api/v1/amenities/{id}/availability?date=YYYY-MM-DD
     */
    public function availability(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $amenity = Amenity::find($id);
        if (!$amenity) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Amenity not found',
            ], 404);
        }

        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user);
        if ($user->role !== 'super_admin' && (int) $amenity->society_id !== (int) $societyId) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Unauthorized access to this amenity.',
            ], 403);
        }

        $availability = $this->bookingService->getAvailableSlots($amenity, $request->input('date'));

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Slot availability fetched successfully',
            'data' => $availability,
        ]);
    }

    /**
     * Create an amenity booking.
     * POST /api/v1/amenity-bookings
     */
    public function book(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amenity_id' => 'required|exists:amenities,id',
            'booking_date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();

        try {
            $booking = $this->bookingService->createBooking(
                $user,
                (int) $request->input('amenity_id'),
                $request->input('booking_date'),
                $request->input('start_time'),
                $request->input('end_time'),
                $request->input('admin_notes')
            );

            return response()->json([
                'success' => true,
                'status' => true,
                'message' => $booking->status === 'confirmed'
                    ? 'Booking confirmed successfully!'
                    : 'Booking request submitted successfully and is pending admin approval.',
                'data' => [
                    'id' => $booking->id,
                    'amenity_id' => $booking->amenity_id,
                    'amenity_name' => $booking->amenity->name,
                    'booking_date' => $booking->booking_date->format('Y-m-d'),
                    'start_time' => substr($booking->start_time, 0, 5),
                    'end_time' => substr($booking->end_time, 0, 5),
                    'status' => $booking->status,
                    'fee' => (float) $booking->amenity->fee,
                    'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
                ],
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => $e->getMessage(),
                'errors' => [
                    'slot' => [$e->getMessage()],
                ],
            ], 409); // 409 Conflict for overlapping slot or invalid state
        } catch (\Throwable $e) {
            \Log::error('Amenity book exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'An error occurred while processing your booking.',
            ], 500);
        }
    }

    /**
     * List authenticated resident's bookings.
     * GET /api/v1/amenity-bookings
     */
    public function myBookings(Request $request): JsonResponse
    {
        $user = auth()->user();

        $query = AmenityBooking::with('amenity')
            ->where('user_id', $user->id)
            ->latest('booking_date');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $bookings = $query->paginate(15);

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Bookings fetched successfully',
            'data' => $bookings->getCollection()->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'amenity_id' => $booking->amenity_id,
                    'amenity_name' => $booking->amenity?->name,
                    'booking_date' => $booking->booking_date->format('Y-m-d'),
                    'start_time' => substr($booking->start_time, 0, 5),
                    'end_time' => substr($booking->end_time, 0, 5),
                    'status' => $booking->status,
                    'rejection_reason' => $booking->rejection_reason,
                    'can_cancel' => $booking->canCancel(),
                    'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'pagination' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    /**
     * Get single booking detail.
     * GET /api/v1/amenity-bookings/{id}
     */
    public function bookingDetails($id): JsonResponse
    {
        $user = auth()->user();
        $booking = AmenityBooking::with('amenity', 'user')->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        $isOwner = (int) $booking->user_id === (int) $user->id;
        $isAdmin = $user->role === 'super_admin' || has_permission('amenity_booking_index');

        if (!$isOwner && !$isAdmin) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Unauthorized to view this booking.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Booking details fetched successfully',
            'data' => [
                'id' => $booking->id,
                'amenity' => [
                    'id' => $booking->amenity->id,
                    'name' => $booking->amenity->name,
                    'image' => $booking->amenity->image_url,
                    'fee' => (float) $booking->amenity->fee,
                ],
                'resident' => [
                    'id' => $booking->user->id,
                    'name' => $booking->user->name,
                    'phone' => $booking->user->phone,
                ],
                'booking_date' => $booking->booking_date->format('Y-m-d'),
                'start_time' => substr($booking->start_time, 0, 5),
                'end_time' => substr($booking->end_time, 0, 5),
                'status' => $booking->status,
                'rejection_reason' => $booking->rejection_reason,
                'admin_notes' => $booking->admin_notes,
                'approved_at' => optional($booking->approved_at)->format('Y-m-d H:i:s'),
                'cancelled_at' => optional($booking->cancelled_at)->format('Y-m-d H:i:s'),
                'can_cancel' => $booking->canCancel(),
                'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Cancel a booking.
     * POST /api/v1/amenity-bookings/{id}/cancel
     */
    public function cancelBooking(Request $request, $id): JsonResponse
    {
        $user = auth()->user();
        $booking = AmenityBooking::find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        try {
            $reason = $request->input('reason');
            $updatedBooking = $this->bookingService->cancelBooking($booking, $user, $reason);

            return response()->json([
                'success' => true,
                'status' => true,
                'message' => 'Booking cancelled successfully.',
                'data' => [
                    'id' => $updatedBooking->id,
                    'status' => $updatedBooking->status,
                    'cancelled_at' => $updatedBooking->cancelled_at->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Admin: Approve a pending booking.
     * POST /api/v1/admin/amenity-bookings/{id}/approve
     */
    public function approveBooking(Request $request, $id): JsonResponse
    {
        $user = auth()->user();
        if ($user->role !== 'super_admin' && !has_permission('amenity_booking_approve')) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Insufficient permission to approve bookings.',
            ], 403);
        }

        $booking = AmenityBooking::find($id);
        if (!$booking) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        try {
            $approvedBooking = $this->bookingService->approveBooking($booking, $user);

            return response()->json([
                'success' => true,
                'status' => true,
                'message' => 'Booking approved successfully.',
                'data' => [
                    'id' => $approvedBooking->id,
                    'status' => $approvedBooking->status,
                    'approved_at' => $approvedBooking->approved_at->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => $e->getMessage(),
            ], 409);
        }
    }

    /**
     * Admin: Reject a pending booking.
     * POST /api/v1/admin/amenity-bookings/{id}/reject
     */
    public function rejectBooking(Request $request, $id): JsonResponse
    {
        $user = auth()->user();
        if ($user->role !== 'super_admin' && !has_permission('amenity_booking_approve')) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Insufficient permission to reject bookings.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $booking = AmenityBooking::find($id);
        if (!$booking) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        try {
            $rejectedBooking = $this->bookingService->rejectBooking($booking, $user, $request->input('reason'));

            return response()->json([
                'success' => true,
                'status' => true,
                'message' => 'Booking rejected successfully.',
                'data' => [
                    'id' => $rejectedBooking->id,
                    'status' => $rejectedBooking->status,
                    'rejection_reason' => $rejectedBooking->rejection_reason,
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
