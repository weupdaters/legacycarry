@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
  {{-- Header --}}
  <div class="py-3 d-flex justify-content-between align-items-center">
    <div>
      <h4 class="fw-semibold mb-1">
        <i class="bi bi-grid-fill me-1"></i> Amenity Management
      </h4>
      <p class="text-muted small mb-0">Configure society amenities, opening hours, booking modes, and slots.</p>
    </div>

    <div class="d-flex gap-2">
      <a href="{{ route('admin.amenities.bookings') }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-calendar-check me-1"></i> View All Bookings
      </a>
      <a href="{{ route('admin.amenities.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Add Amenity
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

  {{-- Filter Card --}}
  <div class="card mb-3">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('admin.amenities.index') }}" class="row g-2 align-items-center">
        <div class="col-md-4">
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
          <select name="status" class="form-select form-select-sm">
            <option value="">-- All Statuses --</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-secondary btn-sm w-100">Filter</button>
        </div>
        <div class="col-md-2">
          <a href="{{ route('admin.amenities.index') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
        </div>
      </form>
    </div>
  </div>

  {{-- Table Card --}}
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Image</th>
              <th>Amenity</th>
              <th>Society</th>
              <th>Timings</th>
              <th>Booking Mode</th>
              <th>Slot / Fee</th>
              <th>Status</th>
              <th>Total Bookings</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($amenities as $amenity)
              <tr>
                <td style="width: 70px;">
                  @if($amenity->image)
                    <img src="{{ asset('storage/' . $amenity->image) }}" class="rounded" width="55" height="55" style="object-fit: cover;">
                  @else
                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                      <i class="bi bi-image text-muted"></i>
                    </div>
                  @endif
                </td>
                <td>
                  <span class="fw-semibold">{{ $amenity->name }}</span>
                  @if($amenity->description)
                    <p class="text-muted small mb-0 text-truncate" style="max-width: 200px;">{{ $amenity->description }}</p>
                  @endif
                </td>
                <td>{{ $amenity->society?->name ?? '-' }}</td>
                <td>
                  <small class="badge bg-light text-dark border">
                    {{ substr($amenity->opening_time, 0, 5) }} - {{ substr($amenity->closing_time, 0, 5) }}
                  </small>
                </td>
                <td>
                  @if($amenity->booking_mode === 'automatic')
                    <span class="badge bg-success">Auto Confirmed</span>
                  @else
                    <span class="badge bg-warning text-dark">Admin Approval</span>
                  @endif
                </td>
                <td>
                  <div><small>{{ $amenity->slot_duration_minutes }} mins</small></div>
                  <small class="text-muted">{{ $amenity->fee > 0 ? '₹' . number_format($amenity->fee, 2) : 'Free' }}</small>
                </td>
                <td>
                  <span class="badge bg-{{ $amenity->status === 'active' ? 'success' : 'secondary' }}">
                    {{ ucfirst($amenity->status) }}
                  </span>
                </td>
                <td>
                  <span class="badge bg-info">{{ $amenity->bookings_count }} bookings</span>
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm">
                    <a href="{{ route('admin.amenities.edit', $amenity->id) }}" class="btn btn-outline-secondary" title="Edit">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form method="POST" action="{{ route('admin.amenities.destroy', $amenity->id) }}" onsubmit="return confirm('Are you sure you want to delete this amenity?');" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline-danger" title="Delete">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-center py-4 text-muted">No amenities found. Click "Add Amenity" to configure one.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $amenities->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
