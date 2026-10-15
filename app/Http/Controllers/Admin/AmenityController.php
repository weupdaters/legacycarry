<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\AmenityBooking;
use App\Models\Society;
use App\Services\AmenityBookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AmenityController extends Controller
{
    protected AmenityBookingService $bookingService;

    public function __construct(AmenityBookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * List all amenities with filters.
     */
    public function index(Request $request)
    {
        $societies = Society::orderBy('name')->get();

        $query = Amenity::with('society')->withCount('bookings');

        if ($request->filled('society_id')) {
            $query->where('society_id', $request->input('society_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $amenities = $query->latest()->paginate(15);

        return view('admin.amenities.index', compact('amenities', 'societies'));
    }

    /**
     * Show create amenity form.
     */
    public function create()
    {
        $societies = Society::orderBy('name')->get();
        $daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        return view('admin.amenities.form', compact('societies', 'daysOfWeek'));
    }

    /**
     * Store newly created amenity.
     */
    public function store(Request $request)
    {
        $request->validate([
            'society_id' => 'required|exists:societies,id',
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
            'fee' => 'nullable|numeric|min:0',
        ]);

        $data = $request->except(['_token', 'image']);
        $data['fee'] = $request->input('fee', 0);
        $data['available_days'] = $request->input('available_days', ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('amenities', 'public');
        }

        Amenity::create($data);

        return redirect()->route('admin.amenities.index')->with('success', 'Amenity created successfully.');
    }

    /**
     * Show edit amenity form.
     */
    public function edit($id)
    {
        $amenity = Amenity::findOrFail($id);
        $societies = Society::orderBy('name')->get();
        $daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        return view('admin.amenities.form', compact('amenity', 'societies', 'daysOfWeek'));
    }

    /**
     * Update existing amenity.
     */
    public function update(Request $request, $id)
    {
        $amenity = Amenity::findOrFail($id);

        $request->validate([
            'society_id' => 'required|exists:societies,id',
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
            'fee' => 'nullable|numeric|min:0',
        ]);

        $data = $request->except(['_token', '_method', 'image']);
        $data['fee'] = $request->input('fee', 0);
        $data['available_days'] = $request->input('available_days', ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']);

        if ($request->hasFile('image')) {
            if ($amenity->image) {
                Storage::disk('public')->delete($amenity->image);
            }
            $data['image'] = $request->file('image')->store('amenities', 'public');
        }

        $amenity->update($data);

        return redirect()->route('admin.amenities.index')->with('success', 'Amenity updated successfully.');
    }

    /**
     * Delete amenity.
     */
    public function destroy($id)
    {
        $amenity = Amenity::findOrFail($id);
        if ($amenity->image) {
            Storage::disk('public')->delete($amenity->image);
        }
        $amenity->delete();

        return redirect()->route('admin.amenities.index')->with('success', 'Amenity deleted successfully.');
    }

    /**
     * List all booking requests across society with filters.
     */
    public function bookings(Request $request)
    {
        $societies = Society::orderBy('name')->get();
        $amenities = Amenity::orderBy('name')->get();

        $query = AmenityBooking::with(['amenity', 'user', 'society'])
            ->latest('booking_date');

        if ($request->filled('society_id')) {
            $query->where('society_id', $request->input('society_id'));
        }

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

        return view('admin.amenities.bookings', compact('bookings', 'societies', 'amenities'));
    }

    /**
     * Approve pending booking.
     */
    public function approveBooking(Request $request, $id)
    {
        $booking = AmenityBooking::findOrFail($id);

        try {
            $this->bookingService->approveBooking($booking, auth()->user());
            return back()->with('success', 'Booking approved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject pending booking.
     */
    public function rejectBooking(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        $booking = AmenityBooking::findOrFail($id);

        try {
            $this->bookingService->rejectBooking($booking, auth()->user(), $request->input('reason'));
            return back()->with('success', 'Booking rejected.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel an active booking.
     */
    public function cancelBooking(Request $request, $id)
    {
        $booking = AmenityBooking::findOrFail($id);

        try {
            $this->bookingService->cancelBooking($booking, auth()->user(), $request->input('reason'));
            return back()->with('success', 'Booking cancelled.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
