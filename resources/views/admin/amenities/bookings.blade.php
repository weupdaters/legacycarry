@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
  {{-- Header --}}
  <div class="py-3 d-flex justify-content-between align-items-center">
    <div>
      <h4 class="fw-semibold mb-1">
        <i class="bi bi-calendar-check me-1"></i> Amenity Booking Requests & History
      </h4>
      <p class="text-muted small mb-0">Review resident booking requests, approve or reject with reasons, and monitor slot schedules.</p>
    </div>
    <div>
      <a href="{{ route('admin.amenities.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-grid me-1"></i> Manage Amenities
      </a>
    </div>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- Filters --}}
  <div class="card mb-3">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('admin.amenities.bookings') }}" class="row g-2">
        <div class="col-md-3">
          <select name="society_id" class="form-select form-select-sm">
            <option value="">-- All Societies --</option>
            @foreach($societies as $soc)
              <option value="{{ $soc->id }}" {{ request('society_id') == $soc->id ? 'selected' : '' }}>
                {{ $soc->name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <select name="amenity_id" class="form-select form-select-sm">
            <option value="">-- All Amenities --</option>
            @foreach($amenities as $am)
              <option value="{{ $am->id }}" {{ request('amenity_id') == $am->id ? 'selected' : '' }}>
                {{ $am->name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <select name="status" class="form-select form-select-sm">
            <option value="">-- All Statuses --</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
          </select>
        </div>
        <div class="col-md-2">
          <input type="date" name="date" class="form-control form-select-sm" value="{{ request('date') }}" placeholder="Filter Date">
        </div>
        <div class="col-md-2 d-flex gap-1">
          <button type="submit" class="btn btn-secondary btn-sm flex-fill">Filter</button>
          <a href="{{ route('admin.amenities.bookings') }}" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
        </div>
      </form>
    </div>
  </div>

  {{-- Bookings Table --}}
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Amenity</th>
              <th>Resident</th>
              <th>Date & Time Slot</th>
              <th>Status</th>
              <th>Admin Action / Notes</th>
              <th>Created At</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($bookings as $booking)
              <tr>
                <td>#{{ $booking->id }}</td>
                <td>
                  <span class="fw-semibold">{{ $booking->amenity?->name ?? '-' }}</span>
                  <div><small class="text-muted">{{ $booking->society?->name ?? '-' }}</small></div>
                </td>
                <td>
                  <div class="fw-medium">{{ $booking->user?->name ?? '-' }}</div>
                  <small class="text-muted">{{ $booking->user?->phone ?? $booking->user?->email }}</small>
                </td>
                <td>
                  <span class="fw-medium text-primary">{{ $booking->booking_date->format('d M, Y') }}</span>
                  <div>
                    <span class="badge bg-light text-dark border">
                      {{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}
                    </span>
                  </div>
                </td>
                <td>
                  @if($booking->status === 'confirmed')
                    <span class="badge bg-success">Confirmed</span>
                  @elseif($booking->status === 'pending')
                    <span class="badge bg-warning text-dark">Pending Approval</span>
                  @elseif($booking->status === 'rejected')
                    <span class="badge bg-danger">Rejected</span>
                  @elseif($booking->status === 'cancelled')
                    <span class="badge bg-secondary">Cancelled</span>
                  @endif
                </td>
                <td>
                  @if($booking->rejection_reason)
                    <small class="text-danger d-block"><strong>Reason:</strong> {{ $booking->rejection_reason }}</small>
                  @endif
                  @if($booking->admin_notes)
                    <small class="text-muted d-block"><strong>Notes:</strong> {{ $booking->admin_notes }}</small>
                  @endif
                </td>
                <td><small>{{ $booking->created_at->format('d M, Y H:i') }}</small></td>
                <td class="text-center">
                  @if($booking->status === 'pending')
                    <div class="btn-group btn-group-sm">
                      <form method="POST" action="{{ route('admin.amenities.bookings.approve', $booking->id) }}" class="d-inline" onsubmit="return confirm('Approve this booking?');">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm" title="Approve">
                          <i class="bi bi-check-circle me-1"></i> Approve
                        </button>
                      </form>
                      <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $booking->id }}" title="Reject">
                        <i class="bi bi-x-circle me-1"></i> Reject
                      </button>
                    </div>

                    {{-- Reject Modal --}}
                    <div class="modal fade text-start" id="rejectModal{{ $booking->id }}" tabindex="-1">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <form method="POST" action="{{ route('admin.amenities.bookings.reject', $booking->id) }}">
                            @csrf
                            <div class="modal-header">
                              <h5 class="modal-title">Reject Booking #{{ $booking->id }}</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                              <p class="mb-2"><strong>Amenity:</strong> {{ $booking->amenity?->name }}</p>
                              <p class="mb-2"><strong>Resident:</strong> {{ $booking->user?->name }}</p>
                              <p class="mb-3"><strong>Slot:</strong> {{ $booking->booking_date->format('d M, Y') }} ({{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }})</p>
                              <div class="mb-3">
                                <label class="form-label fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                                <textarea name="reason" class="form-control" rows="3" required placeholder="Provide reason for resident notification..."></textarea>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                              <button type="submit" class="btn btn-danger btn-sm">Confirm Rejection</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  @elseif($booking->status === 'confirmed')
                    <form method="POST" action="{{ route('admin.amenities.bookings.cancel', $booking->id) }}" onsubmit="return confirm('Cancel this confirmed booking?');" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-outline-secondary btn-sm" title="Cancel Booking">
                        Cancel
                      </button>
                    </form>
                  @else
                    <span class="text-muted small">-</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center py-4 text-muted">No booking records found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $bookings->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
