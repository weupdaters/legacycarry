@extends('member.layouts.app')

@section('title', 'Amenity Booking')

@section('content')
<div class="container-fluid py-4">
  {{-- Hero Banner --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
        <div class="card-body p-4 text-white d-flex justify-content-between align-items-center">
          <div>
            <h3 class="fw-bold mb-1">Society Amenities</h3>
            <p class="opacity-75 mb-0">Browse and book facilities in your society such as Clubhouse, Gym, Pool, and Hall.</p>
          </div>
          <div>
            <a href="{{ route('member.amenities.my-bookings') }}" class="btn btn-white text-success fw-bold rounded-pill px-4">
              <i class="mdi mdi-history me-1"></i> My Bookings
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
      <i class="mdi mdi-check-circle me-2"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
      <i class="mdi mdi-alert-circle me-2"></i> {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- Upcoming Active Bookings Preview --}}
  @if($upcomingBookings->isNotEmpty())
    <div class="card border-0 shadow-sm rounded-4 mb-4">
      <div class="card-header bg-white border-0 py-3 px-4">
        <h5 class="fw-bold text-dark mb-0">
          <i class="mdi mdi-calendar-clock text-success me-2"></i> Your Upcoming Bookings
        </h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-4">Amenity</th>
                <th>Date</th>
                <th>Time Slot</th>
                <th>Status</th>
                <th class="text-end pe-4">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($upcomingBookings as $ub)
                <tr>
                  <td class="ps-4 fw-semibold text-dark">{{ $ub->amenity?->name }}</td>
                  <td>{{ $ub->booking_date->format('D, d M Y') }}</td>
                  <td>
                    <span class="badge bg-light text-dark border">
                      {{ substr($ub->start_time, 0, 5) }} - {{ substr($ub->end_time, 0, 5) }}
                    </span>
                  </td>
                  <td>
                    @if($ub->status === 'confirmed')
                      <span class="badge bg-soft-success text-success rounded-pill px-3">Confirmed</span>
                    @else
                      <span class="badge bg-soft-warning text-warning rounded-pill px-3">Pending Approval</span>
                    @endif
                  </td>
                  <td class="text-end pe-4">
                    @if($ub->canCancel())
                      <form method="POST" action="{{ route('member.amenities.cancel', $ub->id) }}" onsubmit="return confirm('Cancel this booking?');" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">Cancel</button>
                      </form>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  @endif

  {{-- Available Amenities Grid --}}
  <h4 class="fw-bold text-dark mb-3">Available Facilities</h4>
  <div class="row">
    @forelse($amenities as $amenity)
      <div class="col-md-6 col-lg-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden transition-hover">
          <div style="height: 180px; position: relative;" class="bg-light">
            @if($amenity->image)
              <img src="{{ asset('storage/' . $amenity->image) }}" class="w-100 h-100" style="object-fit: cover;">
            @else
              <div class="d-flex align-items-center justify-content-center w-100 h-100 bg-soft-success text-success">
                <i class="mdi mdi-domain mdi-48px"></i>
              </div>
            <span class="position-absolute top-0 end-0 m-3 badge {{ $amenity->booking_mode === 'automatic' ? 'bg-success' : 'bg-warning text-dark' }} rounded-pill px-3 py-2 shadow-sm">
              <i class="mdi {{ $amenity->booking_mode === 'automatic' ? 'mdi-lightning-bolt' : 'mdi-clock-outline' }} me-1"></i>
              {{ $amenity->booking_mode === 'automatic' ? 'Instant Booking' : 'Requires Approval' }}
            </span>
          </div>

          <div class="card-body p-4 d-flex flex-column">
            <h5 class="fw-bold text-dark mb-1">{{ $amenity->name }}</h5>
            <p class="text-muted small mb-3 line-clamp-2">{{ Str::limit($amenity->description, 90) ?: 'No specific rules provided.' }}</p>

            <div class="bg-light rounded-3 p-3 mb-3 small">
              <div class="d-flex justify-content-between mb-1">
                <span class="text-muted"><i class="mdi mdi-clock-outline me-1"></i> Timings:</span>
                <span class="fw-semibold text-dark">{{ substr($amenity->opening_time, 0, 5) }} - {{ substr($amenity->closing_time, 0, 5) }}</span>
              </div>
              <div class="d-flex justify-content-between mb-1">
                <span class="text-muted"><i class="mdi mdi-timer-sand me-1"></i> Slot Duration:</span>
                <span class="fw-semibold text-dark">{{ $amenity->slot_duration_minutes }} mins</span>
              </div>
              <div class="d-flex justify-content-between">
                <span class="text-muted"><i class="mdi mdi-check-decagram-outline me-1"></i> Confirmation:</span>
                <span class="fw-semibold text-dark">{{ $amenity->booking_mode === 'automatic' ? 'Instant' : 'Admin Approval' }}</span>
              </div>
            </div>

            <div class="mt-auto">
              <a href="{{ route('member.amenities.book', $amenity->id) }}" class="btn btn-success w-100 rounded-pill fw-semibold py-2">
                <i class="mdi mdi-calendar-plus me-1"></i> Book Slot
              </a>
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
          <i class="mdi mdi-domain-off mdi-48px mb-2 text-secondary"></i>
          <h5>No Amenities Available</h5>
          <p class="mb-0">Your society management has not published any bookable amenities yet.</p>
        </div>
      </div>
    @endforelse
  </div>
</div>
@endsection
