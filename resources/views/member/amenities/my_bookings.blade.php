@extends('member.layouts.app')

@section('title', 'My Amenity Bookings')

@section('content')
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="fw-bold text-dark mb-1">My Amenity Bookings</h3>
      <p class="text-muted small mb-0">Track confirmation status, upcoming schedules, and booking history.</p>
    </div>
    <a href="{{ route('member.amenities.index') }}" class="btn btn-success rounded-pill px-4">
      <i class="mdi mdi-plus me-1"></i> Book New Amenity
    </a>
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

  {{-- Filter Nav Tabs --}}
  <div class="d-flex gap-2 mb-3">
    <a href="{{ route('member.amenities.my-bookings') }}" class="btn btn-sm {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }} rounded-pill px-3">
      All Bookings
    </a>
    <a href="{{ route('member.amenities.my-bookings', ['status' => 'confirmed']) }}" class="btn btn-sm {{ request('status') == 'confirmed' ? 'btn-success' : 'btn-outline-secondary' }} rounded-pill px-3">
      Confirmed
    </a>
    <a href="{{ route('member.amenities.my-bookings', ['status' => 'pending']) }}" class="btn btn-sm {{ request('status') == 'pending' ? 'btn-warning text-dark' : 'btn-outline-secondary' }} rounded-pill px-3">
      Pending Approval
    </a>
    <a href="{{ route('member.amenities.my-bookings', ['status' => 'cancelled']) }}" class="btn btn-sm {{ request('status') == 'cancelled' ? 'btn-secondary' : 'btn-outline-secondary' }} rounded-pill px-3">
      Cancelled / Rejected
    </a>
  </div>

  <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4">Booking ID</th>
              <th>Amenity</th>
              <th>Booking Date</th>
              <th>Time Slot</th>
              <th>Status</th>
              <th>Notes / Reason</th>
              <th class="text-end pe-4">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($bookings as $booking)
              <tr>
                <td class="ps-4 fw-medium text-muted">#{{ $booking->id }}</td>
                <td>
                  <span class="fw-bold text-dark">{{ $booking->amenity?->name }}</span>
                  @if($booking->amenity?->fee > 0)
                    <div><small class="text-muted">₹{{ number_format($booking->amenity->fee, 2) }}</small></div>
                  @endif
                </td>
                <td class="fw-medium text-dark">{{ $booking->booking_date->format('D, d M Y') }}</td>
                <td>
                  <span class="badge bg-light text-dark border">
                    {{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}
                  </span>
                </td>
                <td>
                  @if($booking->status === 'confirmed')
                    <span class="badge bg-soft-success text-success rounded-pill px-3 py-1">Confirmed</span>
                  @elseif($booking->status === 'pending')
                    <span class="badge bg-soft-warning text-warning rounded-pill px-3 py-1">Pending Approval</span>
                  @elseif($booking->status === 'rejected')
                    <span class="badge bg-soft-danger text-danger rounded-pill px-3 py-1">Rejected</span>
                  @elseif($booking->status === 'cancelled')
                    <span class="badge bg-secondary rounded-pill px-3 py-1">Cancelled</span>
                  @endif
                </td>
                <td>
                  @if($booking->rejection_reason)
                    <small class="text-danger"><strong>Rejected:</strong> {{ $booking->rejection_reason }}</small>
                  @elseif($booking->admin_notes)
                    <small class="text-muted">{{ $booking->admin_notes }}</small>
                  @else
                    <small class="text-muted">-</small>
                  @endif
                </td>
                <td class="text-end pe-4">
                  @if($booking->canCancel())
                    <form method="POST" action="{{ route('member.amenities.cancel', $booking->id) }}" onsubmit="return confirm('Are you sure you want to cancel this booking?');" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                        Cancel Booking
                      </button>
                    </form>
                  @else
                    <span class="text-muted small">Completed / Locked</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                  <i class="mdi mdi-calendar-remove mdi-36px d-block mb-2 text-secondary"></i>
                  No bookings found in this view.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="p-3">
        {{ $bookings->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
