<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\AmenityBooking;
use App\Services\AmenityBookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AmenityBookingController extends Controller
{
    protected AmenityBookingService $bookingService;

    public function __construct(AmenityBookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * View all available society amenities and upcoming bookings.
     */
    public function index()
    {
        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user);

        if (!$societyId) {
            return view('member.amenities.index', [
                'amenities' => collect(),
                'upcomingBookings' => collect(),
                'error' => 'You are not assigned to any society.',
            ]);
        }

        $amenities = Amenity::where('society_id', $societyId)
            ->active()
            ->orderBy('name')
            ->get();

        $upcomingBookings = AmenityBooking::with('amenity')
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('booking_date', '>=', Carbon::today())
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->get();

        return view('member.amenities.index', compact('amenities', 'upcomingBookings', 'societyId'));
    }

    /**
     * Show booking slot picker page for a specific amenity.
     */
    public function bookView($id)
    {
        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user);

        $amenity = Amenity::where('id', $id)->where('society_id', $societyId)->active()->firstOrFail();

        $defaultDate = Carbon::tomorrow()->format('Y-m-d');
        $initialSlots = $this->bookingService->getAvailableSlots($amenity, $defaultDate);

        return view('member.amenities.book', compact('amenity', 'defaultDate', 'initialSlots'));
    }

    /**
     * AJAX endpoint to fetch slot availability for a selected date.
     */
    public function getSlotsAjax(Request $request, $id)
    {
        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user);

        $amenity = Amenity::where('id', $id)->where('society_id', $societyId)->active()->firstOrFail();
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));

        $slotsData = $this->bookingService->getAvailableSlots($amenity, $date);

        return response()->json($slotsData);
    }

    /**
     * Handle booking submission from resident dashboard.
     */
    public function bookSubmit(Request $request, $id)
    {
        $request->validate([
            'booking_date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();

        try {
            $booking = $this->bookingService->createBooking(
                $user,
                (int) $id,
                $request->input('booking_date'),
                $request->input('start_time'),
                $request->input('end_time'),
                $request->input('admin_notes')
            );

            $msg = $booking->status === 'confirmed'
                ? 'Your amenity booking is confirmed!'
                : 'Your booking request has been submitted and is pending admin approval.';

            return redirect()->route('member.amenities.my-bookings')->with('success', $msg);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            \Log::error('Member bookSubmit error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to complete booking. Please try another slot.');
        }
    }

    /**
     * Show resident's booking history.
     */
    public function myBookings(Request $request)
    {
        $user = auth()->user();

        $query = AmenityBooking::with('amenity')
            ->where('user_id', $user->id)
            ->latest('booking_date');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $bookings = $query->paginate(15);

        return view('member.amenities.my_bookings', compact('bookings'));
    }

    /**
     * Cancel an existing booking.
     */
    public function cancel(Request $request, $id)
    {
        $user = auth()->user();
        $booking = AmenityBooking::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        try {
            $this->bookingService->cancelBooking($booking, $user, $request->input('reason'));
            return back()->with('success', 'Booking cancelled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
