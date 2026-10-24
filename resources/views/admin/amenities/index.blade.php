@extends('admin.layouts.app')

@section('content')
<style>
  .report-stat-card {
    transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(0, 0, 0, 0.06) !important;
    border-radius: 1rem !important;
  }
  .report-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 28px -4px rgba(16, 185, 129, 0.16), 0 8px 16px -4px rgba(0, 0, 0, 0.06) !important;
    border-color: rgba(16, 185, 129, 0.35) !important;
  }
  .stat-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
  }
  .pulse-badge {
    animation: pulseGlow 1.8s infinite;
  }
  @keyframes pulseGlow {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.08); opacity: 0.85; }
    100% { transform: scale(1); opacity: 1; }
  }
  .pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    animation: pulseDotAnim 1.8s infinite;
  }
  @keyframes pulseDotAnim {
    0% { transform: scale(0.9); opacity: 0.6; }
    50% { transform: scale(1.3); opacity: 1; }
    100% { transform: scale(0.9); opacity: 0.6; }
  }
  .nav-tabs-custom {
    border-bottom: 2px solid #e2e8f0;
    gap: 8px;
  }
  .nav-tabs-custom .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    padding: 12px 20px;
    font-weight: 600;
    font-size: 14px;
    color: #64748b;
    border-radius: 0;
    transition: all 0.2s ease;
    background: transparent;
  }
  .nav-tabs-custom .nav-link:hover {
    color: #0f172a;
    border-color: #cbd5e1;
  }
  .nav-tabs-custom .nav-link.active {
    color: #2563eb;
    border-color: #2563eb;
    background: transparent;
  }
  .slot-pill {
    transition: all 0.2s ease;
    border-radius: 8px;
    font-weight: 600;
  }
  .image-popup-hover {
    cursor: pointer;
    position: relative;
    display: inline-block;
  }
  .image-zoom-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.4);
    opacity: 0;
    transition: opacity 0.2s ease;
  }
  .image-popup-hover:hover .image-zoom-overlay {
    opacity: 1;
  }
</style>

<div class="container-fluid">
  {{-- Header --}}
  <div class="py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
      <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
        <i class="bi bi-grid-fill text-primary"></i> Amenity Management
      </h4>
      <p class="text-muted small mb-0">Configure society amenities, opening hours, booking modes, and manage reservations.</p>
    </div>

    <div class="d-flex flex-wrap gap-2">
      {{-- HIGH VISIBILITY EASY BOOKING BUTTON --}}
      <button type="button" class="btn btn-success btn-sm fw-bold shadow-sm px-3" onclick="openQuickBook()">
        <i class="bi bi-calendar-plus-fill me-1"></i> + New Booking
      </button>

      <a href="{{ route('admin.amenities.bookings') }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-calendar-check me-1"></i> View All Bookings
      </a>

      <button type="button" class="btn btn-outline-info btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#amenityAnalyticsModal">
        <i class="bi bi-bar-chart-fill me-1"></i> Report & Analytics
      </button>

      <a href="{{ route('admin.amenities.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Add Amenity
      </a>
    </div>
  </div>

  {{-- 4 Animated Report Analytics Cards --}}
  <div class="row g-3 mb-3">
    {{-- Card 1: Total Facilities --}}
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-4 h-100 bg-white report-stat-card" onclick="switchTab('amenities')" style="cursor: pointer;">
        <div class="card-body p-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="text-muted small fw-semibold text-uppercase">Total Facilities</span>
            <div class="stat-icon-box" style="background: #dcfce7; color: #059669;">
              <i class="bi bi-building"></i>
            </div>
          </div>
          <div class="d-flex align-items-baseline gap-2">
            <h3 class="fw-bold mb-0 text-dark">{{ $analytics['total_facilities'] ?? $amenities->total() }}</h3>
            <span class="badge bg-success-subtle text-success rounded-pill font-11 px-2 py-1">
              {{ $analytics['active_facilities'] ?? 0 }} Active
            </span>
          </div>
          <div class="mt-2 text-muted small d-flex align-items-center justify-content-between">
            <span>Configured amenities</span>
            <span class="text-success fw-semibold"><i class="bi bi-eye me-1"></i>View List</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Card 2: Total Bookings --}}
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-4 h-100 bg-white report-stat-card" onclick="switchTab('recent')" style="cursor: pointer;">
        <div class="card-body p-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="text-muted small fw-semibold text-uppercase">Total Bookings</span>
            <div class="stat-icon-box" style="background: #e0f2fe; color: #0284c7;">
              <i class="bi bi-calendar2-check"></i>
            </div>
          </div>
          <div class="d-flex align-items-baseline gap-2">
            <h3 class="fw-bold mb-0 text-dark">{{ $analytics['total_bookings'] ?? 0 }}</h3>
            <span class="badge bg-info-subtle text-info rounded-pill font-11 px-2 py-1">
              {{ $analytics['this_month_bookings'] ?? 0 }} This Month
            </span>
          </div>
          <div class="mt-2 text-muted small d-flex align-items-center justify-content-between">
            <span>{{ $analytics['today_bookings'] ?? 0 }} booked today</span>
            <span class="text-info fw-semibold"><i class="bi bi-list-check me-1"></i>Recent Log</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Card 3: Pending Approvals (CLICK DIRECTLY SWITCHES TO NEW BOOKINGS TAB) --}}
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-4 h-100 bg-white report-stat-card" onclick="switchTab('pending')" style="cursor: pointer;">
        <div class="card-body p-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="text-muted small fw-semibold text-uppercase">Pending Approvals</span>
            <div class="stat-icon-box {{ ($analytics['pending_bookings'] ?? 0) > 0 ? 'pulse-badge' : '' }}" style="background: #fef3c7; color: #d97706;">
              <i class="bi bi-clock-history"></i>
            </div>
          </div>
          <div class="d-flex align-items-baseline gap-2">
            <h3 class="fw-bold mb-0 text-warning">{{ $analytics['pending_bookings'] ?? 0 }}</h3>
            @if(($analytics['pending_bookings'] ?? 0) > 0)
              <span class="badge bg-warning text-dark rounded-pill font-11 px-2 py-1">
                <span class="pulse-dot bg-danger me-1"></span> Needs Action
              </span>
            @else
              <span class="badge bg-success-subtle text-success rounded-pill font-11 px-2 py-1">All Clear</span>
            @endif
          </div>
          <div class="mt-2 text-muted small d-flex align-items-center justify-content-between">
            <span>Awaiting decision</span>
            <span class="text-warning fw-bold"><i class="bi bi-arrow-right-circle me-1"></i>Review Here →</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Card 4: Confirmed Bookings --}}
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-4 h-100 bg-white report-stat-card" data-bs-toggle="modal" data-bs-target="#amenityAnalyticsModal" style="cursor: pointer;">
        <div class="card-body p-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="text-muted small fw-semibold text-uppercase">Confirmed Bookings</span>
            <div class="stat-icon-box" style="background: #ecfdf5; color: #10b981;">
              <i class="bi bi-patch-check-fill"></i>
            </div>
          </div>
          <div class="d-flex align-items-baseline gap-2">
            <h3 class="fw-bold mb-0 text-success">{{ $analytics['confirmed_bookings'] ?? 0 }}</h3>
            @php
              $totalAdminB = $analytics['total_bookings'] ?? 0;
              $confRateAdmin = $totalAdminB > 0 ? round((($analytics['confirmed_bookings'] ?? 0) / $totalAdminB) * 100) : 0;
            @endphp
            <span class="badge bg-success text-white rounded-pill font-11 px-2 py-1">
              {{ $confRateAdmin }}% Rate
            </span>
          </div>
          <div class="mt-2 text-muted small d-flex align-items-center justify-content-between">
            <span>Successfully booked</span>
            <span class="text-success fw-semibold"><i class="bi bi-pie-chart-fill me-1"></i>Breakdown</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- MAIN WORKSPACE TABS --}}
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-3 px-4 pb-0">
      <ul class="nav nav-tabs-custom" id="amenityTab" role="tablist">
        {{-- TAB 1: AMENITIES LIST --}}
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="amenities-tab" data-bs-toggle="tab" data-bs-target="#amenities-tab-pane" type="button" role="tab">
            <i class="bi bi-grid me-1"></i> Facilities Directory
            <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1">{{ $amenities->total() }}</span>
          </button>
        </li>

        {{-- TAB 2: NEW & PENDING BOOKINGS (EASY VIEW FOR ADMIN) --}}
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-tab-pane" type="button" role="tab">
            <i class="bi bi-bell-fill text-warning me-1"></i> New & Pending Bookings
            @if(($analytics['pending_bookings'] ?? 0) > 0)
              <span class="badge bg-warning text-dark rounded-pill ms-1 pulse-badge">
                {{ $analytics['pending_bookings'] }} Action Needed
              </span>
            @else
              <span class="badge bg-light text-muted border rounded-pill ms-1">0</span>
            @endif
          </button>
        </li>

        {{-- TAB 3: RECENT BOOKINGS LOG --}}
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="recent-tab" data-bs-toggle="tab" data-bs-target="#recent-tab-pane" type="button" role="tab">
            <i class="bi bi-clock-history me-1"></i> Recent Bookings Log
            <span class="badge bg-light text-dark border rounded-pill ms-1">{{ $recentBookings->count() }}</span>
          </button>
        </li>
      </ul>
    </div>

    <div class="card-body p-4">
      <div class="tab-content" id="amenityTabContent">

        {{-- PANE 1: AMENITIES DIRECTORY --}}
        <div class="tab-pane fade show active" id="amenities-tab-pane" role="tabpanel" tabindex="0">
          {{-- Filter Row --}}
          <form method="GET" action="{{ route('admin.amenities.index') }}" class="row g-2 align-items-center mb-4">
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

          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>Image</th>
                  <th>Amenity</th>
                  <th>Society</th>
                  <th>Timings</th>
                  <th>Booking Mode</th>
                  <th>Slot Duration</th>
                  <th>Status</th>
                  <th>Total Bookings</th>
                  <th class="text-center" style="min-width: 160px;">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($amenities as $amenity)
                  <tr>
                    <td style="width: 70px;">
                      @if($amenity->image)
                        <div class="image-popup-hover rounded overflow-hidden" 
                             data-preview-image="{{ asset('storage/' . $amenity->image) }}"
                             data-preview-title="{{ $amenity->name }}"
                             title="Click to view full image">
                          <img src="{{ asset('storage/' . $amenity->image) }}" class="rounded shadow-sm border img-popup-clickable" width="55" height="55" style="object-fit: cover;" alt="{{ $amenity->name }}">
                          <div class="image-zoom-overlay rounded d-flex align-items-center justify-content-center">
                            <i class="bi bi-arrows-fullscreen text-white" style="font-size: 13px;"></i>
                          </div>
                        </div>
                      @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                          <i class="bi bi-image text-muted"></i>
                        </div>
                      @endif
                    </td>
                    <td>
                      <span class="fw-bold text-dark d-block">{{ $amenity->name }}</span>
                      @if($amenity->description)
                        <p class="text-muted small mb-0 text-truncate" style="max-width: 220px;">{{ $amenity->description }}</p>
                      @endif
                    </td>
                    <td>
                      <span class="badge bg-light border text-dark">{{ $amenity->society?->name ?? '-' }}</span>
                    </td>
                    <td>
                      <small class="badge bg-light text-dark border">
                        {{ substr($amenity->opening_time, 0, 5) }} - {{ substr($amenity->closing_time, 0, 5) }}
                      </small>
                    </td>
                    <td>
                      @if($amenity->booking_mode === 'automatic')
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Auto Confirmed</span>
                      @else
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Admin Approval</span>
                      @endif
                    </td>
                    <td>
                      <small class="fw-semibold">{{ $amenity->slot_duration_minutes }} mins</small>
                    </td>
                    <td>
                      <span class="badge bg-{{ $amenity->status === 'active' ? 'success' : 'secondary' }}">
                        {{ ucfirst($amenity->status) }}
                      </span>
                    </td>
                    <td>
                      <a href="{{ route('admin.amenities.bookings', ['amenity_id' => $amenity->id, 'society_id' => $amenity->society_id]) }}" class="badge bg-info text-decoration-none px-3 py-2 shadow-sm" title="Click to view bookings">
                        <i class="bi bi-calendar-check me-1"></i> {{ $amenity->bookings_count }} bookings
                      </a>
                    </td>
                    <td class="text-center">
                      <div class="d-flex align-items-center justify-content-center gap-1">
                        {{-- QUICK BOOK DIRECT BUTTON --}}
                        <button type="button" class="btn btn-sm btn-success px-2 py-1 fw-semibold" style="font-size: 11px;" onclick="openQuickBook({{ $amenity->id }})" title="Create Booking for this Amenity">
                          <i class="bi bi-calendar-plus me-1"></i> Book
                        </button>

                        <a href="{{ route('admin.amenities.bookings', ['amenity_id' => $amenity->id, 'society_id' => $amenity->society_id]) }}" class="btn btn-sm btn-outline-info px-2 py-1" title="View Bookings">
                          <i class="bi bi-calendar-check"></i>
                        </a>
                        <a href="{{ route('admin.amenities.edit', $amenity->id) }}" class="btn btn-sm btn-outline-secondary px-2 py-1" title="Edit">
                          <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.amenities.destroy', $amenity->id) }}" onsubmit="return confirm('Are you sure you want to delete this amenity?');" class="d-inline">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-1" title="Delete">
                            <i class="bi bi-trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                      <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                      No amenities found. Click "Add Amenity" to configure one.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="mt-3">
            {{ $amenities->links() }}
          </div>
        </div>

        {{-- PANE 2: NEW & PENDING BOOKINGS (EASY INLINE APPROVE / REJECT) --}}
        <div class="tab-pane fade" id="pending-tab-pane" role="tabpanel" tabindex="0">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-clock-history text-warning me-1"></i> Pending Reservation Approvals
              </h5>
              <p class="text-muted small mb-0">Approve or reject pending resident requests instantly without navigating away</p>
            </div>
            <button type="button" class="btn btn-sm btn-success fw-bold px-3" onclick="openQuickBook()">
              <i class="bi bi-plus-circle me-1"></i> + New Booking
            </button>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Booking ID</th>
                  <th>Facility / Amenity</th>
                  <th>Resident Details</th>
                  <th>Society</th>
                  <th>Schedule</th>
                  <th>Requested On</th>
                  <th class="text-center" style="min-width: 170px;">Action Decision</th>
                </tr>
              </thead>
              <tbody>
                @forelse($pendingBookings as $booking)
                  <tr>
                    <td>
                      <span class="badge bg-light border text-dark font-monospace fw-bold">#BK-{{ $booking->id }}</span>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        @if($booking->amenity?->image)
                          <img src="{{ asset('storage/' . $booking->amenity->image) }}" class="rounded" width="40" height="40" style="object-fit: cover;">
                        @else
                          <div class="bg-info-subtle text-info rounded p-2 text-center" style="width: 40px; height: 40px;">
                            🏊
                          </div>
                        @endif
                        <div>
                          <span class="fw-bold text-dark d-block">{{ $booking->amenity?->name ?? 'Facility' }}</span>
                          <span class="badge bg-warning-subtle text-warning font-10">Needs Approval</span>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="fw-semibold text-dark d-block">{{ $booking->user?->name ?? 'Resident' }}</span>
                      <span class="text-muted small d-block"><i class="bi bi-envelope me-1"></i>{{ $booking->user?->email ?? '—' }}</span>
                      @if($booking->user?->phone)
                        <span class="text-muted small d-block"><i class="bi bi-telephone me-1"></i>{{ $booking->user->phone }}</span>
                      @endif
                      @if($booking->user?->details?->flat)
                        <span class="badge bg-light border text-dark font-11 mt-1">Flat: {{ $booking->user->details->flat->flat_number }}</span>
                      @endif
                    </td>
                    <td>
                      <span class="badge bg-light border text-dark">{{ $booking->society?->name ?? 'Society' }}</span>
                    </td>
                    <td>
                      <span class="fw-bold text-dark d-block font-13">
                        <i class="bi bi-calendar-event me-1 text-primary"></i>
                        {{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('D, M d, Y') : '—' }}
                      </span>
                      <span class="badge bg-info-subtle text-info mt-1">
                        <i class="bi bi-clock me-1"></i>
                        {{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}
                      </span>
                    </td>
                    <td>
                      <span class="text-muted small">{{ $booking->created_at ? $booking->created_at->diffForHumans() : '—' }}</span>
                    </td>
                    <td class="text-center">
                      <div class="d-flex justify-content-center gap-2">
                        {{-- 1-CLICK APPROVE --}}
                        <form method="POST" action="{{ route('admin.amenities.bookings.approve', $booking->id) }}" onsubmit="return confirm('Approve booking for {{ $booking->user?->name }}?');">
                          @csrf
                          <button type="submit" class="btn btn-sm btn-success fw-bold px-3 py-1 shadow-sm" style="font-size: 12px;">
                            <i class="bi bi-check-lg me-1"></i> Approve
                          </button>
                        </form>

                        {{-- 1-CLICK REJECT MODAL --}}
                        <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1 fw-bold" style="font-size: 12px;" onclick="openRejectModal({{ $booking->id }}, '{{ addslashes($booking->user?->name ?? 'Resident') }}', '{{ addslashes($booking->amenity?->name ?? 'Facility') }}')">
                          <i class="bi bi-x-lg me-1"></i> Reject
                        </button>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center py-5">
                      <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                        <i class="bi bi-check2-circle fs-3"></i>
                      </div>
                      <h6 class="fw-bold text-dark mb-1">All Caught Up!</h6>
                      <p class="text-muted small mb-0">No pending amenity bookings waiting for approval right now.</p>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        {{-- PANE 3: ALL RECENT BOOKINGS LOG --}}
        <div class="tab-pane fade" id="recent-tab-pane" role="tabpanel" tabindex="0">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-calendar2-check text-primary me-1"></i> Recent Amenity Bookings Log
              </h5>
              <p class="text-muted small mb-0">Overview of the last 30 reservations across all society facilities</p>
            </div>
            <a href="{{ route('admin.amenities.bookings') }}" class="btn btn-sm btn-outline-primary">
              Full Registry →
            </a>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Booking ID</th>
                  <th>Facility</th>
                  <th>Resident</th>
                  <th>Society</th>
                  <th>Date & Time</th>
                  <th>Status</th>
                  <th>Created</th>
                </tr>
              </thead>
              <tbody>
                @forelse($recentBookings as $booking)
                  <tr>
                    <td>
                      <span class="badge bg-light border text-dark font-monospace">#BK-{{ $booking->id }}</span>
                    </td>
                    <td>
                      <span class="fw-semibold text-dark">{{ $booking->amenity?->name ?? 'Facility' }}</span>
                    </td>
                    <td>
                      <span class="fw-semibold text-dark">{{ $booking->user?->name ?? 'Resident' }}</span>
                      <small class="text-muted d-block">{{ $booking->user?->email }}</small>
                    </td>
                    <td>
                      <span class="badge bg-light border text-dark">{{ $booking->society?->name ?? 'Society' }}</span>
                    </td>
                    <td>
                      <span class="fw-bold font-12 d-block">{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') : '—' }}</span>
                      <span class="text-muted font-11">{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}</span>
                    </td>
                    <td>
                      @if($booking->status === 'confirmed' || $booking->status === 'approved')
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Confirmed</span>
                      @elseif($booking->status === 'pending')
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">Pending</span>
                      @elseif($booking->status === 'rejected')
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Rejected</span>
                      @else
                        <span class="badge bg-secondary-subtle text-secondary px-2 py-1">{{ ucfirst($booking->status) }}</span>
                      @endif
                    </td>
                    <td>
                      <span class="text-muted small">{{ $booking->created_at ? $booking->created_at->format('M d, h:i A') : '—' }}</span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center py-4 text-muted">No reservations found.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>

  {{-- ========================================================================= --}}
  {{-- 1. QUICK BOOK AMENITY MODAL (SUPER SIMPLE BOOKING FOR ADMIN)             --}}
  {{-- ========================================================================= --}}
  <div class="modal fade" id="quickBookModal" tabindex="-1" aria-labelledby="quickBookModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        {{-- Modal Header --}}
        <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
          <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
              <i class="bi bi-calendar-plus-fill fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold text-white mb-0" id="quickBookModalLabel">
                Easy Amenity Booking
              </h5>
              <small class="text-white-50 font-12">Create an instant reservation for any resident</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        {{-- Modal Body Form --}}
        <form method="POST" action="{{ route('admin.amenities.quick-book') }}" id="quickBookForm">
          @csrf
          <div class="modal-body p-4 bg-light">
            {{-- Step 1: Select Facility --}}
            <div class="mb-3">
              <label class="form-label fw-bold text-dark small">1. Select Amenity / Facility <span class="text-danger">*</span></label>
              <select name="amenity_id" id="qb_amenity_id" class="form-select" required onchange="loadQuickBookSlots()">
                <option value="">-- Choose Facility --</option>
                @foreach($activeAmenitiesList as $am)
                  <option value="{{ $am->id }}" data-society="{{ $am->society_id }}" data-duration="{{ $am->slot_duration_minutes }}">
                    {{ $am->name }} ({{ $am->society?->name ?? 'Society' }})
                  </option>
                @endforeach
              </select>
            </div>

            {{-- Step 2: Select Resident --}}
            <div class="mb-3">
              <label class="form-label fw-bold text-dark small">2. Select Resident / Customer <span class="text-danger">*</span></label>
              <select name="user_id" id="qb_user_id" class="form-select" required>
                <option value="">-- Choose Resident --</option>
                @foreach($residents as $res)
                  @php
                    $flat = $res->details?->flat?->flat_number ?? null;
                  @endphp
                  <option value="{{ $res->id }}" data-society="{{ $res->user_society_id }}">
                    {{ $res->name }} ({{ $res->email }}) {{ $flat ? '— Flat ' . $flat : '' }}
                  </option>
                @endforeach
              </select>
            </div>

            {{-- Step 3: Select Date --}}
            <div class="mb-3">
              <label class="form-label fw-bold text-dark small">3. Reservation Date <span class="text-danger">*</span></label>
              <input type="date" name="booking_date" id="qb_booking_date" class="form-control" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required onchange="loadQuickBookSlots()">
            </div>

            {{-- Step 4: Available Slot Selection --}}
            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label fw-bold text-dark small mb-0">4. Select Time Slot <span class="text-danger">*</span></label>
                <span id="qb_selected_slot_info" class="badge bg-success-subtle text-success font-11">No slot selected</span>
              </div>
              
              <div id="qb_slots_loading" style="display: none;" class="text-center py-2 text-muted small">
                <span class="spinner-border spinner-border-sm me-1 text-primary"></span> Loading available slots...
              </div>

              <div id="qb_slots_container" class="p-3 bg-white border rounded-3 text-center" style="max-height: 180px; overflow-y: auto;">
                <span class="text-muted small">Please choose an amenity above to see available time slots.</span>
              </div>

              {{-- Hidden fields for selected start and end time --}}
              <input type="hidden" name="start_time" id="qb_start_time" required>
              <input type="hidden" name="end_time" id="qb_end_time" required>
            </div>

            {{-- Step 5: Instant Confirmation Switch --}}
            <div class="mb-3 p-3 bg-white border rounded-3">
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" name="booking_status" value="confirmed" id="qb_auto_approve" checked>
                <label class="form-check-label fw-bold text-dark font-13" for="qb_auto_approve">
                  <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Instantly Approve Booking
                </label>
                <small class="text-muted d-block font-11">When checked, the slot is immediately confirmed without pending review.</small>
              </div>
            </div>

            {{-- Step 6: Notes (Optional) --}}
            <div class="mb-1">
              <label class="form-label fw-semibold text-muted small">Admin Remarks / Notes (Optional)</label>
              <input type="text" name="admin_notes" class="form-control form-control-sm" placeholder="e.g. Requested via reception / phone">
            </div>
          </div>

          {{-- Modal Footer --}}
          <div class="modal-footer bg-white border-top px-4 py-3 d-flex justify-content-between">
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success fw-bold btn-sm rounded-pill px-4 shadow-sm" id="qb_submit_btn">
              <i class="bi bi-check-circle-fill me-1"></i> Confirm & Book Facility
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ========================================================================= --}}
  {{-- 2. REJECT BOOKING MODAL (1-CLICK REJECT WITH REASON)                      --}}
  {{-- ========================================================================= --}}
  <div class="modal fade" id="rejectBookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content border-0 shadow-lg rounded-4">
        <form method="POST" id="rejectBookingForm" action="">
          @csrf
          <div class="modal-header bg-danger text-white py-2 px-3">
            <h6 class="modal-title fw-bold mb-0"><i class="bi bi-x-circle me-1"></i> Reject Booking</h6>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-3">
            <p class="small text-muted mb-2">Rejecting request for <strong id="rejectModalTarget" class="text-dark"></strong>.</p>
            <label class="form-label small fw-bold text-dark">Reason for Rejection <span class="text-danger">*</span></label>
            <textarea name="reason" class="form-control form-control-sm" rows="3" placeholder="e.g. Facility under maintenance, slot conflict" required></textarea>
          </div>
          <div class="modal-footer py-2 px-3 bg-light">
            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-danger fw-bold">Confirm Reject</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ========================================================================= --}}
  {{-- 3. AMENITY ANALYTICS & REPORT DEEP DIVE MODAL                             --}}
  {{-- ========================================================================= --}}
  <div class="modal fade" id="amenityAnalyticsModal" tabindex="-1" aria-labelledby="amenityAnalyticsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        {{-- Modal Header --}}
        <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #3b82f6 100%);">
          <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
              <i class="bi bi-bar-chart-line-fill font-18"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold text-white mb-0" id="amenityAnalyticsModalLabel">
                Amenity & Booking Analytics Report
              </h5>
              <small class="text-white-50 font-12">System-wide usage statistics & booking performance</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        {{-- Modal Body --}}
        <div class="modal-body p-4 bg-light">
          {{-- KPI Summary Grid --}}
          <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
              <div class="bg-white p-3 rounded-3 shadow-sm border text-center h-100">
                <div class="text-muted small fw-semibold text-uppercase">Total Facilities</div>
                <div class="fs-3 fw-bold text-dark mt-1">{{ $analytics['total_facilities'] ?? $amenities->total() }}</div>
                <small class="badge bg-success-subtle text-success rounded-pill font-11 mt-1">
                  {{ $analytics['active_facilities'] ?? 0 }} Active
                </small>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="bg-white p-3 rounded-3 shadow-sm border text-center h-100">
                <div class="text-muted small fw-semibold text-uppercase">Total Bookings</div>
                <div class="fs-3 fw-bold text-primary mt-1">{{ $analytics['total_bookings'] ?? 0 }}</div>
                <small class="text-muted small">All records</small>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="bg-white p-3 rounded-3 shadow-sm border text-center h-100">
                <div class="text-muted small fw-semibold text-uppercase">This Month</div>
                <div class="fs-3 fw-bold text-info mt-1">{{ $analytics['this_month_bookings'] ?? 0 }}</div>
                <small class="text-muted small">Current month</small>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="bg-white p-3 rounded-3 shadow-sm border text-center h-100">
                <div class="text-muted small fw-semibold text-uppercase">Today's Bookings</div>
                <div class="fs-3 fw-bold text-success mt-1">{{ $analytics['today_bookings'] ?? 0 }}</div>
                <small class="text-muted small">{{ \Carbon\Carbon::today()->format('d M Y') }}</small>
              </div>
            </div>
          </div>

          {{-- Booking Status Distribution Breakdown --}}
          @php
            $totAdminB = max($analytics['total_bookings'] ?? 0, 1);
            $confAdminPct = round((($analytics['confirmed_bookings'] ?? 0) / $totAdminB) * 100);
            $pendAdminPct = round((($analytics['pending_bookings'] ?? 0) / $totAdminB) * 100);
            $rejAdminPct = round((($analytics['rejected_bookings'] ?? 0) / $totAdminB) * 100);
            $cancAdminPct = round((($analytics['cancelled_bookings'] ?? 0) / $totAdminB) * 100);
          @endphp
          <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold text-dark mb-0">
                  <i class="bi bi-pie-chart-fill text-primary me-1"></i> Booking Status Distribution
                </h6>
                <small class="text-muted">{{ $analytics['total_bookings'] ?? 0 }} Total Bookings</small>
              </div>

              {{-- Stacked Progress Bar --}}
              <div class="progress mb-3 rounded-pill" style="height: 12px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $confAdminPct }}%;" title="Confirmed: {{ $confAdminPct }}%"></div>
                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pendAdminPct }}%;" title="Pending: {{ $pendAdminPct }}%"></div>
                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $rejAdminPct }}%;" title="Rejected: {{ $rejAdminPct }}%"></div>
                <div class="progress-bar bg-secondary" role="progressbar" style="width: {{ $cancAdminPct }}%;" title="Cancelled: {{ $cancAdminPct }}%"></div>
              </div>

              {{-- Legend Pills --}}
              <div class="row g-2 text-center small">
                <div class="col-6 col-md-3">
                  <div class="p-2 rounded bg-success-subtle border border-success-subtle">
                    <div class="fw-bold text-success">{{ $analytics['confirmed_bookings'] ?? 0 }}</div>
                    <span class="text-muted">Confirmed ({{ $confAdminPct }}%)</span>
                  </div>
                </div>
                <div class="col-6 col-md-3">
                  <div class="p-2 rounded bg-warning-subtle border border-warning-subtle">
                    <div class="fw-bold text-warning">{{ $analytics['pending_bookings'] ?? 0 }}</div>
                    <span class="text-muted">Pending ({{ $pendAdminPct }}%)</span>
                  </div>
                </div>
                <div class="col-6 col-md-3">
                  <div class="p-2 rounded bg-danger-subtle border border-danger-subtle">
                    <div class="fw-bold text-danger">{{ $analytics['rejected_bookings'] ?? 0 }}</div>
                    <span class="text-muted">Rejected ({{ $rejAdminPct }}%)</span>
                  </div>
                </div>
                <div class="col-6 col-md-3">
                  <div class="p-2 rounded bg-secondary-subtle border border-secondary-subtle">
                    <div class="fw-bold text-secondary">{{ $analytics['cancelled_bookings'] ?? 0 }}</div>
                    <span class="text-muted">Cancelled ({{ $cancAdminPct }}%)</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Popular Facilities Leaderboard --}}
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3">
              <h6 class="fw-bold text-dark mb-3">
                <i class="bi bi-trophy-fill text-warning me-1"></i> Top Booked Amenities
              </h6>

              @if(isset($analytics['popular_amenities']) && $analytics['popular_amenities']->isNotEmpty())
                <div class="table-responsive">
                  <table class="table table-sm table-hover align-middle mb-0 small">
                    <thead class="table-light">
                      <tr>
                        <th>#</th>
                        <th>Amenity</th>
                        <th>Society</th>
                        <th>Mode</th>
                        <th class="text-end">Total Bookings</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($analytics['popular_amenities'] as $idx => $pop)
                        <tr>
                          <td class="fw-bold text-muted">{{ $idx + 1 }}</td>
                          <td>
                            <span class="fw-semibold text-dark">{{ $pop->name }}</span>
                          </td>
                          <td class="text-muted">{{ $pop->society?->name ?? '-' }}</td>
                          <td>
                            @if($pop->booking_mode === 'automatic')
                              <span class="badge bg-success-subtle text-success rounded-pill font-11">Auto</span>
                            @else
                              <span class="badge bg-warning-subtle text-warning rounded-pill font-11">Approval</span>
                            @endif
                          </td>
                          <td class="text-end">
                            <a href="{{ route('admin.amenities.bookings', ['amenity_id' => $pop->id]) }}" class="badge bg-primary text-decoration-none px-2 py-1">
                              {{ $pop->bookings_count }} bookings
                            </a>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @else
                <div class="text-center py-3 text-muted small">
                  No booking activity recorded yet.
                </div>
              @endif
            </div>
          </div>
        </div>

        {{-- Modal Footer --}}
        <div class="modal-footer bg-white border-top px-4 py-3 d-flex justify-content-between align-items-center">
          <small class="text-muted small">
            <i class="bi bi-clock me-1"></i> Live dynamic report
          </small>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-3 btn-sm" onclick="window.print()">
              <i class="bi bi-printer me-1"></i> Print / Save Report
            </button>
            <button type="button" class="btn btn-primary rounded-pill px-4 btn-sm fw-bold" data-bs-dismiss="modal">
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  function switchTab(tabName) {
    if (tabName === 'pending') {
      const trigger = document.getElementById('pending-tab');
      if (trigger) {
        const tab = new bootstrap.Tab(trigger);
        tab.show();
        trigger.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    } else if (tabName === 'recent') {
      const trigger = document.getElementById('recent-tab');
      if (trigger) {
        const tab = new bootstrap.Tab(trigger);
        tab.show();
        trigger.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    } else {
      const trigger = document.getElementById('amenities-tab');
      if (trigger) {
        const tab = new bootstrap.Tab(trigger);
        tab.show();
        trigger.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }
  }

  function openRejectModal(bookingId, residentName, amenityName) {
    const form = document.getElementById('rejectBookingForm');
    form.action = `/admin/amenities/bookings/${bookingId}/reject`;
    document.getElementById('rejectModalTarget').innerText = `${amenityName} for ${residentName}`;
    const modal = new bootstrap.Modal(document.getElementById('rejectBookingModal'));
    modal.show();
  }

  function openQuickBook(amenityId = null) {
    if (amenityId) {
      document.getElementById('qb_amenity_id').value = amenityId;
    }
    loadQuickBookSlots();
    const modal = new bootstrap.Modal(document.getElementById('quickBookModal'));
    modal.show();
  }

  function loadQuickBookSlots() {
    const amenityId = document.getElementById('qb_amenity_id').value;
    const date = document.getElementById('qb_booking_date').value;
    const container = document.getElementById('qb_slots_container');
    const loading = document.getElementById('qb_slots_loading');
    const slotInfo = document.getElementById('qb_selected_slot_info');
    
    // Reset hidden values
    document.getElementById('qb_start_time').value = '';
    document.getElementById('qb_end_time').value = '';
    slotInfo.className = 'badge bg-secondary-subtle text-secondary font-11';
    slotInfo.innerText = 'No slot selected';

    if (!amenityId || !date) {
      container.innerHTML = '<span class="text-muted small">Please select an amenity and date to view time slots.</span>';
      return;
    }

    loading.style.display = 'block';
    container.innerHTML = '';

    fetch(`/admin/amenities/${amenityId}/slots-ajax?date=${date}`)
      .then(res => res.json())
      .then(data => {
        loading.style.display = 'none';

        if (!data.is_available_date) {
          container.innerHTML = `<div class="alert alert-warning py-2 px-3 small mb-0">${data.message || 'Facility closed on this date.'}</div>`;
          return;
        }

        if (!data.slots || data.slots.length === 0) {
          container.innerHTML = '<div class="alert alert-secondary py-2 px-3 small mb-0">No time slots configured.</div>';
          return;
        }

        let html = '<div class="d-flex flex-wrap gap-2 justify-content-center">';
        data.slots.forEach(slot => {
          if (slot.is_available) {
            html += `<button type="button" class="btn btn-outline-primary btn-sm slot-pill" onclick="selectSlot('${slot.start_time}', '${slot.end_time}', this)">
                       <i class="bi bi-clock me-1"></i>${slot.start_time} - ${slot.end_time}
                     </button>`;
          } else {
            html += `<button type="button" class="btn btn-light btn-sm text-muted border" disabled style="opacity:0.6; font-size:11px;">
                       ${slot.start_time} - ${slot.end_time} <small>(${slot.reason || 'Booked'})</small>
                     </button>`;
          }
        });
        html += '</div>';
        container.innerHTML = html;
      })
      .catch(err => {
        loading.style.display = 'none';
        container.innerHTML = '<span class="text-danger small">Error loading slots. Please check facility timings.</span>';
      });
  }

  function selectSlot(start, end, btn) {
    document.querySelectorAll('.slot-pill').forEach(b => {
      b.classList.remove('btn-primary', 'text-white');
      b.classList.add('btn-outline-primary');
    });
    btn.classList.remove('btn-outline-primary');
    btn.classList.add('btn-primary', 'text-white');

    document.getElementById('qb_start_time').value = start;
    document.getElementById('qb_end_time').value = end;

    const slotInfo = document.getElementById('qb_selected_slot_info');
    slotInfo.className = 'badge bg-success text-white font-11';
    slotInfo.innerText = `Selected: ${start} - ${end}`;
  }

  // Pre-open specific tab if requested via query parameter
  document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    if (tabParam === 'pending') {
      switchTab('pending');
    } else if (tabParam === 'recent' || tabParam === 'bookings') {
      switchTab('recent');
    }
  });
</script>
@endpush
@endsection
