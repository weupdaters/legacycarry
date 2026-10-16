@extends('member.layouts.app')

@section('title', 'Book ' . $amenity->name)

@section('content')
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="fw-bold text-dark mb-1">Book {{ $amenity->name }}</h3>
      <p class="text-muted small mb-0">Select your preferred date and available time slot to submit a booking.</p>
    </div>
    <a href="{{ route('member.amenities.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
      <i class="mdi mdi-arrow-left me-1"></i> Back to Amenities
    </a>
  </div>

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
      <i class="mdi mdi-alert-circle me-2"></i> {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="row">
    {{-- Left: Amenity Details Card --}}
    <div class="col-lg-4 mb-4">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        @if($amenity->image)
          <img src="{{ asset('storage/' . $amenity->image) }}" class="w-100" style="height: 200px; object-fit: cover;">
        @endif
        <div class="card-body p-4">
          <h4 class="fw-bold text-dark mb-2">{{ $amenity->name }}</h4>
          <p class="text-muted small mb-3">{{ $amenity->description ?: 'Enjoy society facility with standard community etiquette.' }}</p>

          <ul class="list-group list-group-flush small">
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Opening Hours:</span>
              <span class="fw-semibold">{{ substr($amenity->opening_time, 0, 5) }} - {{ substr($amenity->closing_time, 0, 5) }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Slot Duration:</span>
              <span class="fw-semibold">{{ $amenity->slot_duration_minutes }} Minutes</span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Confirmation Mode:</span>
              <span class="fw-semibold text-{{ $amenity->booking_mode === 'automatic' ? 'success' : 'warning' }}">
                {{ $amenity->booking_mode === 'automatic' ? 'Instant Automatic' : 'Admin Approval Required' }}
              </span>
            </li>
            @if($amenity->fee > 0)
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Fee:</span>
              <span class="fw-bold text-primary">₹{{ number_format($amenity->fee, 2) }}</span>
            </li>
            @endif
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Advance Limit:</span>
              <span class="fw-semibold">Up to {{ $amenity->advance_booking_days }} Days in advance</span>
            </li>
          </ul>
        </div>
      </div>
    </div>

    {{-- Right: Date & Slot Availability Picker --}}
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
        <h5 class="fw-bold text-dark mb-3"><i class="mdi mdi-calendar-month text-success me-2"></i> 1. Choose Booking Date</h5>

        @php
          $minDate = \Carbon\Carbon::today()->format('Y-m-d');
          $maxDate = \Carbon\Carbon::today()->addDays($amenity->advance_booking_days)->format('Y-m-d');
        @endphp

        <div class="row align-items-center mb-4">
          <div class="col-md-6">
            <input type="date" id="bookingDateInput" class="form-control form-control-lg rounded-3"
              value="{{ $defaultDate }}" min="{{ $minDate }}" max="{{ $maxDate }}">
          </div>
          <div class="col-md-6">
            <small class="text-muted">Available from {{ \Carbon\Carbon::parse($minDate)->format('d M') }} to {{ \Carbon\Carbon::parse($maxDate)->format('d M Y') }}.</small>
          </div>
        </div>

        <h5 class="fw-bold text-dark mb-3"><i class="mdi mdi-clock-outline text-success me-2"></i> 2. Select Time Slot</h5>
        
        <div id="loadingSlots" class="text-center py-4 d-none">
          <div class="spinner-border text-success" role="status">
            <span class="visually-hidden">Loading slots...</span>
          </div>
          <p class="small text-muted mt-2">Checking real-time slot availability...</p>
        </div>

        <div id="slotsGrid" class="row g-2 mb-4">
          {{-- Populated by JavaScript --}}
        </div>

        {{-- Booking Submission Form --}}
        <form method="POST" action="{{ route('member.amenities.book.submit', $amenity->id) }}" id="bookingForm" class="d-none">
          @csrf
          <input type="hidden" name="booking_date" id="formBookingDate" value="{{ $defaultDate }}">
          <input type="hidden" name="start_time" id="formStartTime">
          <input type="hidden" name="end_time" id="formEndTime">

          <div class="card bg-light border-0 rounded-4 p-4 mb-3">
            <h6 class="fw-bold text-dark mb-2">Booking Summary</h6>
            <div class="d-flex justify-content-between mb-2 small">
              <span>Amenity:</span>
              <strong class="text-dark">{{ $amenity->name }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2 small">
              <span>Date:</span>
              <strong class="text-dark" id="summaryDate">-</strong>
            </div>
            <div class="d-flex justify-content-between mb-3 small">
              <span>Selected Slot:</span>
              <strong class="text-success fs-6" id="summarySlot">-</strong>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-semibold">Note for Management (Optional)</label>
              <input type="text" name="admin_notes" class="form-control rounded-3" placeholder="Purpose or specific request (e.g. Birthday gathering)">
            </div>

            <button type="submit" class="btn btn-success btn-lg rounded-pill fw-bold w-100 shadow-sm" id="confirmBookingBtn">
              Confirm & Book Slot
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const dateInput = document.getElementById('bookingDateInput');
  const slotsGrid = document.getElementById('slotsGrid');
  const loadingIndicator = document.getElementById('loadingSlots');
  const bookingForm = document.getElementById('bookingForm');
  const formBookingDate = document.getElementById('formBookingDate');
  const formStartTime = document.getElementById('formStartTime');
  const formEndTime = document.getElementById('formEndTime');
  const summaryDate = document.getElementById('summaryDate');
  const summarySlot = document.getElementById('summarySlot');

  function fetchSlots(selectedDate) {
    loadingIndicator.classList.remove('d-none');
    slotsGrid.innerHTML = '';
    bookingForm.classList.add('d-none');

    fetch(`{{ url('residents/amenities') }}/{{ $amenity->id }}/slots-ajax?date=${selectedDate}`)
      .then(response => response.json())
      .then(data => {
        loadingIndicator.classList.add('d-none');
        if (!data.is_available_date) {
          slotsGrid.innerHTML = `
            <div class="col-12">
              <div class="alert alert-warning rounded-4 border-0 small">
                <i class="mdi mdi-calendar-remove me-1"></i> ${data.message || 'No slots available for this date.'}
              </div>
            </div>
          `;
          return;
        }

        if (!data.slots || data.slots.length === 0) {
          slotsGrid.innerHTML = `
            <div class="col-12">
              <div class="alert alert-secondary rounded-4 border-0 small">
                No slots configured for this amenity.
              </div>
            </div>
          `;
          return;
        }

        data.slots.forEach(slot => {
          const col = document.createElement('div');
          col.className = 'col-sm-6 col-md-4';

          const isAvail = slot.is_available;
          const statusText = isAvail ? 'Available' : (slot.reason || 'Unavailable');
          const borderClass = isAvail ? 'border-success slot-btn-available' : 'border-light bg-light text-muted opacity-60';

          col.innerHTML = `
            <button type="button" class="btn w-100 p-3 rounded-4 border text-start slot-btn ${borderClass}"
              ${isAvail ? '' : 'disabled'}
              data-start="${slot.start_time}" data-end="${slot.end_time}">
              <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold ${isAvail ? 'text-dark' : 'text-muted'}">${slot.start_time} - ${slot.end_time}</span>
                <span class="badge ${isAvail ? 'bg-soft-success text-success' : 'bg-secondary'} rounded-pill">${statusText}</span>
              </div>
            </button>
          `;
          slotsGrid.appendChild(col);
        });
      })
      .catch(err => {
        loadingIndicator.classList.add('d-none');
        slotsGrid.innerHTML = '<div class="col-12 text-danger small">Failed to load time slots. Please refresh.</div>';
      });
  }

  // Slot click handler
  slotsGrid.addEventListener('click', function (e) {
    const slotBtn = e.target.closest('.slot-btn-available');
    if (!slotBtn) return;

    // Remove active highlight from all
    document.querySelectorAll('.slot-btn-available').forEach(b => {
      b.classList.remove('btn-success', 'text-white');
      b.classList.add('border-success');
    });

    slotBtn.classList.remove('border-success');
    slotBtn.classList.add('btn-success', 'text-white');

    const start = slotBtn.dataset.start;
    const end = slotBtn.dataset.end;
    const dateVal = dateInput.value;

    formBookingDate.value = dateVal;
    formStartTime.value = start;
    formEndTime.value = end;

    summaryDate.textContent = dateVal;
    summarySlot.textContent = `${start} - ${end}`;

    bookingForm.classList.remove('d-none');
    bookingForm.scrollIntoView({ behavior: 'smooth' });
  });

  dateInput.addEventListener('change', function () {
    fetchSlots(this.value);
  });

  // Initial load
  fetchSlots(dateInput.value);
});
</script>
@endsection
