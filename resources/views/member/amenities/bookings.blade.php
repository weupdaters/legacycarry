@extends('member.layouts.app')

@section('title', 'Society Amenity Bookings')

@section('content')
<div class="container-fluid py-4">
  {{-- Header --}}
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
      <h3 class="fw-bold mb-1 text-dark">Society Amenity Bookings</h3>
      <p class="text-muted mb-0">Review, approve, or reject amenity booking requests submitted by residents in your society.</p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('member.amenities.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
        <i class="mdi mdi-arrow-left me-1"></i> Back to Amenities
      </a>
      @if(has_permission('amenity_create'))
        <a href="{{ route('member.amenities.create') }}" class="btn btn-success rounded-pill px-4">
          <i class="mdi mdi-plus me-1"></i> Add Amenity
        </a>
      @endif
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

  {{-- Filter Card --}}
  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('member.amenities.bookings.all') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label small fw-semibold text-muted">Filter by Amenity</label>
          <select name="amenity_id" class="form-select rounded-3">
            <option value="">All Amenities</option>
            @foreach($amenities as $am)
              <option value="{{ $am->id }}" {{ request('amenity_id') == $am->id ? 'selected' : '' }}>
                {{ $am->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label small fw-semibold text-muted">Filter by Status</label>
          <select name="status" class="form-select rounded-3">
            <option value="">All Statuses</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label small fw-semibold text-muted">Date</label>
          <input type="date" name="date" class="form-control rounded-3" value="{{ request('date') }}">
        </div>

        <div class="col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-primary rounded-3 w-100">
            <i class="mdi mdi-filter me-1"></i> Filter
          </button>
          <a href="{{ route('member.amenities.bookings.all') }}" class="btn btn-light rounded-3" title="Clear Filters">
            <i class="mdi mdi-refresh"></i>
          </a>
        </div>
      </form>
    </div>
  </div>

  {{-- Bookings Table --}}
  <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4">#</th>
              <th>Resident</th>
              <th>Amenity</th>
              <th>Booking Date</th>
              <th>Slot Time</th>
              <th>Status</th>
              <th>Booked On</th>
              <th class="text-end pe-4">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($bookings as $booking)
              <tr>
                <td class="ps-4 text-muted fw-semibold">#{{ $booking->id }}</td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar-sm rounded-circle bg-soft-primary text-primary fw-bold d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px;">
                      {{ strtoupper(substr($booking->user?->name ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                      <div class="fw-semibold text-dark">{{ $booking->user?->name ?? 'Unknown User' }}</div>
                      <small class="text-muted">{{ $booking->user?->email ?? $booking->user?->mobile ?? '' }}</small>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="fw-semibold text-dark">{{ $booking->amenity?->name ?? 'Deleted Amenity' }}</span>
                </td>
                <td>
                  <span class="text-dark fw-semibold">
                    {{ \Carbon\Carbon::parse($booking->booking_date)->format('D, d M Y') }}
                  </span>
                </td>
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
                    <span class="badge bg-soft-danger text-danger rounded-pill px-3 py-1" title="{{ $booking->rejection_reason }}">Rejected</span>
                  @elseif($booking->status === 'cancelled')
                    <span class="badge bg-soft-secondary text-secondary rounded-pill px-3 py-1">Cancelled</span>
                  @endif
                </td>
                <td>
                  <small class="text-muted">{{ $booking->created_at ? $booking->created_at->format('d M, h:i A') : '-' }}</small>
                </td>
                <td class="text-end pe-4">
                  @if($booking->status === 'pending')
                    <form method="POST" action="{{ route('member.amenities.bookings.approve', $booking->id) }}" class="d-inline" onsubmit="return confirm('Approve this amenity booking?');">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 me-1" title="Approve">
                        <i class="mdi mdi-check"></i> Approve
                      </button>
                    </form>

                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $booking->id }}">
                      <i class="mdi mdi-close"></i> Reject
                    </button>

                    <!-- Reject Modal -->
                    <div class="modal fade text-start" id="rejectModal{{ $booking->id }}" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4 border-0 shadow">
                          <form method="POST" action="{{ route('member.amenities.bookings.reject', $booking->id) }}">
                            @csrf
                            <div class="modal-header border-0 pb-0">
                              <h5 class="modal-title fw-bold text-dark">Reject Booking #{{ $booking->id }}</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                              <p class="text-muted small">
                                Rejecting booking for <strong>{{ $booking->user?->name }}</strong> for <strong>{{ $booking->amenity?->name }}</strong> on {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}.
                              </p>
                              <div class="mb-3">
                                <label class="form-label fw-semibold text-dark">Reason for Rejection <span class="text-danger">*</span></label>
                                <textarea name="reason" class="form-control rounded-3" rows="3" placeholder="Provide a reason so the resident understands why..." required></textarea>
                              </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                              <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                              <button type="submit" class="btn btn-danger rounded-pill px-4">Confirm Rejection</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  @elseif($booking->status === 'rejected' && $booking->rejection_reason)
                    <span class="small text-muted fst-italic" title="{{ $booking->rejection_reason }}">
                      Reason: {{ Str::limit($booking->rejection_reason, 25) }}
                    </span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center py-5 text-muted">
                  <i class="mdi mdi-calendar-blank-outline mdi-48px text-secondary d-block mb-2"></i>
                  <h6 class="fw-semibold">No bookings found</h6>
                  <p class="small mb-0">There are no resident bookings matching the selected criteria.</p>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="p-3">
        {{ $bookings->appends(request()->query())->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
