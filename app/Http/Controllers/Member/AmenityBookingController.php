<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\AmenityBooking;
use App\Services\AmenityBookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AmenityBookingController extends Controller
{
    protected AmenityBookingService $bookingService;

    public function __construct(AmenityBookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Determine if current user has Resident Admin or Super Admin privileges.
     */
    protected function isResidentAdmin(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->role === 'super_admin'
            || has_permission('amenity_booking_index')
            || has_permission('amenity_booking_approve')
            || has_permission('amenity_create')
            || has_permission('amenity_edit')
            || has_permission('amenity_delete')
            || has_permission('resident_index');
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
                'isResidentAdmin' => false,
                'pendingTotalCount' => 0,
                'error' => 'You are not assigned to any society.',
            ]);
        }

        $isResidentAdmin = $this->isResidentAdmin();

        $query = Amenity::where('society_id', $societyId)
            ->withCount([
                'bookings',
                'bookings as pending_bookings_count' => function ($q) {
                    $q->where('status', 'pending');
                }
            ]);

        // Regular residents only see active amenities; admins see all (to edit/activate)
        if (!$isResidentAdmin) {
            $query->active();
        }

        $amenities = $query->orderBy('name')->get();

        $pendingTotalCount = 0;
        if ($isResidentAdmin) {
            $pendingTotalCount = AmenityBooking::where('society_id', $societyId)
                ->where('status', 'pending')
                ->count();
        }

        $upcomingBookings = AmenityBooking::with('amenity')
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('booking_date', '>=', Carbon::today())
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->get();

        return view('member.amenities.index', compact('amenities', 'upcomingBookings', 'societyId', 'isResidentAdmin', 'pendingTotalCount'));
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

    /**
     * Show create amenity form for Resident Admin.
     */
    public function create()
    {
        abort_unless($this->isResidentAdmin(), 403, 'Unauthorized to create amenities.');

        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user);
        if (!$societyId) {
            return back()->with('error', 'You are not assigned to any society.');
        }

        $daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        return view('member.amenities.form', compact('daysOfWeek', 'societyId'));
    }

    /**
     * Store new amenity created by Resident Admin.
     */
    public function store(Request $request)
    {
        abort_unless($this->isResidentAdmin(), 403, 'Unauthorized to create amenities.');

        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user);
        if (!$societyId) {
            return back()->with('error', 'You are not assigned to any society.');
        }

        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'required|in:active,inactive',
            'booking_mode' => 'required|in:automatic,admin_approval',
            'opening_time' => 'required',
            'closing_time' => 'required',
            'available_days' => 'nullable|array',
            'slot_duration_minutes' => 'required|integer|min:15|max:480',
            'max_booking_duration_minutes' => 'required|integer|min:15|max:1440',
            'advance_booking_days' => 'required|integer|min:1|max:365',
        ]);

        $data = $request->except(['_token', 'image']);
        $data['society_id'] = $societyId;
        $data['fee'] = 0;
        $data['available_days'] = $request->input('available_days', ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('amenities', 'public');
        }

        Amenity::create($data);

        return redirect()->route('member.amenities.index')->with('success', 'Amenity created successfully.');
    }

    /**
     * Show edit amenity form for Resident Admin.
     */
    public function edit($id)
    {
        abort_unless($this->isResidentAdmin(), 403, 'Unauthorized to edit amenities.');

        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user);

        $amenity = Amenity::where('id', $id)->where('society_id', $societyId)->firstOrFail();
        $daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        return view('member.amenities.form', compact('amenity', 'daysOfWeek', 'societyId'));
    }

    /**
     * Update amenity for Resident Admin.
     */
    public function update(Request $request, $id)
    {
        abort_unless($this->isResidentAdmin(), 403, 'Unauthorized to update amenities.');

        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user);

        $amenity = Amenity::where('id', $id)->where('society_id', $societyId)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'required|in:active,inactive',
            'booking_mode' => 'required|in:automatic,admin_approval',
            'opening_time' => 'required',
            'closing_time' => 'required',
            'available_days' => 'nullable|array',
            'slot_duration_minutes' => 'required|integer|min:15|max:480',
            'max_booking_duration_minutes' => 'required|integer|min:15|max:1440',
            'advance_booking_days' => 'required|integer|min:1|max:365',
        ]);

        $data = $request->except(['_token', '_method', 'image']);
        $data['fee'] = 0;
        $data['available_days'] = $request->input('available_days', ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']);

        if ($request->hasFile('image')) {
            if ($amenity->image) {
                Storage::disk('public')->delete($amenity->image);
            }
            $data['image'] = $request->file('image')->store('amenities', 'public');
        }

        $amenity->update($data);

        return redirect()->route('member.amenities.index')->with('success', 'Amenity updated successfully.');
    }

    /**
     * Delete amenity for Resident Admin.
     */
    public function destroy($id)
    {
        abort_unless($this->isResidentAdmin(), 403, 'Unauthorized to delete amenities.');

        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user);

        $amenity = Amenity::where('id', $id)->where('society_id', $societyId)->firstOrFail();

        if ($amenity->image) {
            Storage::disk('public')->delete($amenity->image);
        }

        $amenity->delete();

        return redirect()->route('member.amenities.index')->with('success', 'Amenity deleted successfully.');
    }

    /**
     * Manage all bookings of the society for Resident Admin.
     */
    public function allBookings(Request $request)
    {
        abort_unless($this->isResidentAdmin(), 403, 'Unauthorized to view society amenity bookings.');

        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user) ?: (function_exists('getSocietyId') ? getSocietyId() : null);

        $amenities = Amenity::where('society_id', $societyId)->orderBy('name')->get();

        $query = AmenityBooking::with(['amenity', 'user'])
            ->where('society_id', $societyId)
            ->latest('id');

        if ($request->filled('amenity_id')) {
            $query->where('amenity_id', $request->input('amenity_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date')) {
            $query->where('booking_date', $request->input('date'));
        }

        $bookings = $query->paginate(20);

        return view('member.amenities.bookings', compact('bookings', 'amenities', 'societyId'));
    }

    /**
     * Approve pending booking (Resident Admin).
     */
    public function approveBooking(Request $request, $id)
    {
        abort_unless($this->isResidentAdmin(), 403, 'Unauthorized to approve amenity bookings.');

        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user) ?: (function_exists('getSocietyId') ? getSocietyId() : null);

        $booking = AmenityBooking::where('id', $id)->where('society_id', $societyId)->firstOrFail();

        try {
            $this->bookingService->approveBooking($booking, $user);
            return back()->with('success', 'Booking approved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject pending booking (Resident Admin).
     */
    public function rejectBooking(Request $request, $id)
    {
        abort_unless($this->isResidentAdmin(), 403, 'Unauthorized to reject amenity bookings.');

        $request->validate(['reason' => 'required|string|max:500']);

        $user = auth()->user();
        $societyId = AmenityBookingService::getUserSocietyId($user) ?: (function_exists('getSocietyId') ? getSocietyId() : null);

        $booking = AmenityBooking::where('id', $id)->where('society_id', $societyId)->firstOrFail();

        try {
            $this->bookingService->rejectBooking($booking, $user, $request->input('reason'));
            return back()->with('success', 'Booking rejected.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
