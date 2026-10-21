@extends('member.layouts.app')

@section('title', 'Society Amenities')

@section('content')
<style>
  .amenity-card-hover {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .amenity-card-hover:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px -4px rgba(16, 185, 129, 0.15), 0 8px 16px -4px rgba(0, 0, 0, 0.06) !important;
  }
  .amenity-row-hover {
    transition: all 0.2s ease-in-out;
  }
  .amenity-row-hover:hover {
    background-color: #f0fdf4 !important;
  }
  .btn-theme-white {
    background: #ffffff !important;
    color: #059669 !important;
    font-weight: 700 !important;
    border: none !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12) !important;
    transition: all 0.2s ease-in-out !important;
  }
  .btn-theme-white:hover {
    background: #f8fafc !important;
    color: #047857 !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.18) !important;
  }
  .pulse-badge {
    animation: pulseGlow 1.8s infinite;
  }
  @keyframes pulseGlow {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.08); opacity: 0.85; }
    100% { transform: scale(1); opacity: 1; }
  }
  .view-tab.active {
    background-color: #059669 !important;
    color: #ffffff !important;
    border-color: #059669 !important;
  }
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
    font-size: 24px;
    flex-shrink: 0;
  }
</style>

<div class="container-fluid py-4">
  {{-- Hero Header --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #065f46 0%, #059669 50%, #10b981 100%);">
        <div class="card-body p-4 text-white d-flex flex-wrap justify-content-between align-items-center gap-3">
          <div>
            <div class="d-flex align-items-center gap-2 mb-1">
              <span class="badge bg-white text-success px-3 py-1 rounded-pill fw-bold">Society Facilities</span>
              @if($isResidentAdmin)
                <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold">
                  <i class="mdi mdi-shield-account me-1"></i> Admin Access
                </span>
              @endif
            </div>
            <h3 class="fw-bold mb-1 text-white">Society Amenities & Bookings</h3>
            <p class="opacity-75 mb-0 font-14">
              Explore club facilities, reserve your slots instantly, or manage society bookings seamlessly.
            </p>
          </div>

          {{-- Quick Header Action Buttons (Matches App Theme Pattern) --}}
          <div class="d-flex flex-wrap gap-2 align-items-center">
            <button type="button" 
                    class="btn fw-bold rounded-pill px-3 shadow-sm btn-theme-white"
                    data-bs-toggle="modal" 
                    data-bs-target="#amenityAnalyticsModal">
              <i class="mdi mdi-chart-box-outline me-1"></i> Report & Analytics
            </button>

            <a href="{{ Route::has('member.amenities.my-bookings') ? route('member.amenities.my-bookings') : url('residents/amenities/my-bookings') }}" 
               class="btn fw-bold rounded-pill px-3 shadow-sm" 
               style="background: #ffffff !important; color: #059669 !important;">
              <i class="mdi mdi-history me-1"></i> My Bookings
              @if(isset($upcomingBookings) && $upcomingBookings->count() > 0)
                <span class="badge bg-success text-white ms-1 rounded-pill">{{ $upcomingBookings->count() }}</span>
              @endif
            </a>

            @if($isResidentAdmin)
              <a href="{{ Route::has('member.amenities.bookings.all') ? route('member.amenities.bookings.all') : url('residents/amenities/bookings/all') }}" 
                 class="btn btn-outline-light fw-bold rounded-pill px-3">
                <i class="mdi mdi-clipboard-list-outline me-1"></i> Manage Bookings
                @if(isset($pendingTotalCount) && $pendingTotalCount > 0)
                  <span class="badge bg-warning text-dark ms-1 pulse-badge">{{ $pendingTotalCount }} Pending</span>
                @endif
              </a>

              <a href="{{ Route::has('member.amenities.create') ? route('member.amenities.create') : url('residents/amenities/create') }}" 
                 class="btn fw-bold rounded-pill px-3 shadow-sm" 
                 style="background: #ffffff !important; color: #059669 !important;">
                <i class="mdi mdi-plus me-1"></i> Add Amenity
              </a>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- 4 Animated Report Analytics Cards --}}
  <div class="row g-3 mb-4">
    {{-- Card 1: Total Facilities --}}
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-4 h-100 bg-white report-stat-card" data-bs-toggle="modal" data-bs-target="#amenityAnalyticsModal" style="cursor: pointer;">
        <div class="card-body p-3 p-md-4">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <span class="text-muted font-13 fw-semibold text-uppercase tracking-wider">Total Facilities</span>
            <div class="stat-icon-box" style="background: #dcfce7; color: #059669;">
              <i class="mdi mdi-domain"></i>
            </div>
          </div>
          <div class="d-flex align-items-baseline gap-2">
            <h2 class="fw-bold mb-0 text-dark">{{ $analytics['total_facilities'] ?? $amenities->count() }}</h2>
            <span class="badge bg-soft-success text-success rounded-pill font-11 px-2 py-1">
              {{ $analytics['active_facilities'] ?? 0 }} Active
            </span>
          </div>
          <div class="mt-2 text-muted font-12 d-flex align-items-center justify-content-between">
            <span>Bookable amenities</span>
            <span class="text-success fw-semibold"><i class="mdi mdi-eye-outline me-1"></i>Analytics</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Card 2: Total Bookings --}}
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-4 h-100 bg-white report-stat-card" data-bs-toggle="modal" data-bs-target="#amenityAnalyticsModal" style="cursor: pointer;">
        <div class="card-body p-3 p-md-4">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <span class="text-muted font-13 fw-semibold text-uppercase tracking-wider">Total Bookings</span>
            <div class="stat-icon-box" style="background: #e0f2fe; color: #0284c7;">
              <i class="mdi mdi-calendar-check"></i>
            </div>
          </div>
          <div class="d-flex align-items-baseline gap-2">
            <h2 class="fw-bold mb-0 text-dark">{{ $analytics['total_bookings'] ?? 0 }}</h2>
            <span class="badge bg-soft-info text-info rounded-pill font-11 px-2 py-1">
              {{ $analytics['this_month_bookings'] ?? 0 }} This Month
            </span>
          </div>
          <div class="mt-2 text-muted font-12 d-flex align-items-center justify-content-between">
            <span>{{ $analytics['today_bookings'] ?? 0 }} reserved today</span>
            <span class="text-info fw-semibold"><i class="mdi mdi-chart-line me-1"></i>Report</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Card 3: Pending Approvals --}}
    <div class="col-sm-6 col-xl-3">
      @if($isResidentAdmin)
        <a href="{{ Route::has('member.amenities.bookings.all') ? route('member.amenities.bookings.all', ['status' => 'pending']) : url('residents/amenities/bookings/all?status=pending') }}" class="text-decoration-none">
      @else
        <div data-bs-toggle="modal" data-bs-target="#amenityAnalyticsModal" style="cursor: pointer;">
      @endif
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white report-stat-card">
          <div class="card-body p-3 p-md-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <span class="text-muted font-13 fw-semibold text-uppercase tracking-wider">Pending Approvals</span>
              <div class="stat-icon-box {{ ($analytics['pending_bookings'] ?? 0) > 0 ? 'pulse-badge' : '' }}" style="background: #fef3c7; color: #d97706;">
                <i class="mdi mdi-clock-alert-outline"></i>
              </div>
            </div>
            <div class="d-flex align-items-baseline gap-2">
              <h2 class="fw-bold mb-0 text-warning">{{ $analytics['pending_bookings'] ?? 0 }}</h2>
              @if(($analytics['pending_bookings'] ?? 0) > 0)
                <span class="badge bg-warning text-dark rounded-pill font-11 px-2 py-1">Needs Action</span>
              @else
                <span class="badge bg-soft-success text-success rounded-pill font-11 px-2 py-1">All Clear</span>
              @endif
            </div>
            <div class="mt-2 text-muted font-12 d-flex align-items-center justify-content-between">
              <span>Awaiting decision</span>
              <span class="text-warning fw-semibold"><i class="mdi mdi-arrow-right me-1"></i>Review</span>
            </div>
          </div>
        </div>
      @if($isResidentAdmin)
        </a>
      @else
        </div>
      @endif
    </div>

    {{-- Card 4: Confirmed Bookings --}}
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-4 h-100 bg-white report-stat-card" data-bs-toggle="modal" data-bs-target="#amenityAnalyticsModal" style="cursor: pointer;">
        <div class="card-body p-3 p-md-4">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <span class="text-muted font-13 fw-semibold text-uppercase tracking-wider">Confirmed Bookings</span>
            <div class="stat-icon-box" style="background: #ecfdf5; color: #10b981;">
              <i class="mdi mdi-check-decagram"></i>
            </div>
          </div>
          <div class="d-flex align-items-baseline gap-2">
            <h2 class="fw-bold mb-0 text-success">{{ $analytics['confirmed_bookings'] ?? 0 }}</h2>
            @php
              $totalB = $analytics['total_bookings'] ?? 0;
              $confRate = $totalB > 0 ? round((($analytics['confirmed_bookings'] ?? 0) / $totalB) * 100) : 0;
            @endphp
            <span class="badge bg-success text-white rounded-pill font-11 px-2 py-1">
              {{ $confRate }}% Rate
            </span>
          </div>
          <div class="mt-2 text-muted font-12 d-flex align-items-center justify-content-between">
            <span>Successfully reserved</span>
            <span class="text-success fw-semibold"><i class="mdi mdi-chart-donut me-1"></i>Breakdown</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Session Notifications --}}
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
  @if(isset($upcomingBookings) && $upcomingBookings->isNotEmpty())
    <div class="card border-0 shadow-sm rounded-4 mb-4">
      <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold text-dark mb-0">
          <i class="mdi mdi-calendar-clock text-success me-2"></i> Your Upcoming Reservations
        </h5>
        <a href="{{ Route::has('member.amenities.my-bookings') ? route('member.amenities.my-bookings') : url('residents/amenities/my-bookings') }}" class="small fw-semibold text-success text-decoration-none">
          View All <i class="mdi mdi-chevron-right"></i>
        </a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light font-13">
              <tr>
                <th class="ps-4">Amenity</th>
                <th>Booking Date</th>
                <th>Slot Time</th>
                <th>Status</th>
                <th class="text-end pe-4">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($upcomingBookings as $ub)
                <tr>
                  <td class="ps-4 fw-bold text-dark">{{ $ub->amenity?->name }}</td>
                  <td>{{ $ub->booking_date->format('D, d M Y') }}</td>
                  <td>
                    <span class="badge bg-light text-dark border">
                      {{ substr($ub->start_time, 0, 5) }} - {{ substr($ub->end_time, 0, 5) }}
                    </span>
                  </td>
                  <td>
                    @if($ub->status === 'confirmed')
                      <span class="badge bg-soft-success text-success rounded-pill px-3 py-1">Confirmed</span>
                    @else
                      <span class="badge bg-soft-warning text-warning rounded-pill px-3 py-1">Pending Approval</span>
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

  {{-- Amenities Section Controls --}}
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
    <div>
      <h4 class="fw-bold text-dark mb-0">
        <i class="mdi mdi-domain me-1 text-success"></i> Society Facilities
        <span class="badge bg-light text-dark border ms-1 rounded-pill font-13">{{ $amenities->count() }} Total</span>
      </h4>
    </div>

    {{-- Filter, Search & View Toggle --}}
    <div class="d-flex flex-wrap align-items-center gap-2">
      <div class="input-group input-group-sm" style="width: 240px;">
        <span class="input-group-text bg-white border-end-0 rounded-start-pill text-muted">
          <i class="mdi mdi-magnify"></i>
        </span>
        <input type="text" id="amenitySearch" class="form-control border-start-0 rounded-end-pill" placeholder="Search facility...">
      </div>

      <div class="btn-group btn-group-sm" role="group">
        <button type="button" class="btn btn-outline-secondary view-tab active" id="btnTableView" onclick="switchView('table')">
          <i class="mdi mdi-table me-1"></i> Table View
        </button>
        <button type="button" class="btn btn-outline-secondary view-tab" id="btnGridView" onclick="switchView('grid')">
          <i class="mdi mdi-view-grid me-1"></i> Grid View
        </button>
      </div>

      <button type="button" class="btn btn-outline-success btn-sm fw-bold rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#amenityAnalyticsModal">
        <i class="mdi mdi-chart-box-outline me-1"></i> Report & Analytics
      </button>

      @if($isResidentAdmin)
        <a href="{{ Route::has('member.amenities.create') ? route('member.amenities.create') : url('residents/amenities/create') }}" 
           class="btn btn-success btn-sm fw-bold rounded-pill px-3 shadow-sm">
          <i class="mdi mdi-plus me-1"></i> Add Amenity
        </a>
      @endif
    </div>
  </div>

  {{-- VIEW 1: Modern Interactive Table View (Default) --}}
  <div id="amenitiesTableView" class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="amenitiesTable">
          <thead class="table-light font-13 text-secondary text-uppercase">
            <tr>
              <th class="ps-4 py-3">Facility</th>
              <th class="py-3">Timings & Duration</th>
              <th class="py-3">Mode</th>
              <th class="py-3">Status</th>
              @if($isResidentAdmin)
                <th class="py-3 text-center">Bookings</th>
              @endif
              <th class="py-3 text-end pe-4" style="min-width: 280px;">Action Controls</th>
            </tr>
          </thead>
          <tbody>
            @forelse($amenities as $amenity)
              <tr class="amenity-row-hover amenity-item" data-name="{{ strtolower($amenity->name) }}">
                {{-- Facility Info with Thumbnail --}}
                <td class="ps-4 py-3">
                  <div class="d-flex align-items-center">
                    <div class="me-3 position-relative" style="width: 52px; height: 52px; flex-shrink: 0;">
                      @if($amenity->image)
                        <div class="image-popup-hover w-100 h-100 rounded-3 overflow-hidden"
                             data-preview-image="{{ asset('storage/' . $amenity->image) }}"
                             data-preview-title="{{ $amenity->name }}"
                             title="Click to preview image">
                          <img src="{{ asset('storage/' . $amenity->image) }}" class="rounded-3 w-100 h-100 border img-popup-clickable" style="object-fit: cover;" alt="{{ $amenity->name }}">
                          <div class="image-zoom-overlay rounded-3 d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-arrow-expand-all text-white font-14"></i>
                          </div>
                        </div>
                      @else
                        <div class="rounded-3 w-100 h-100 bg-soft-success text-success d-flex align-items-center justify-content-center border border-success-subtle">
                          <i class="mdi mdi-domain mdi-24px"></i>
                        </div>
                      @endif
                    </div>
                    <div>
                      <div class="fw-bold text-dark font-15 mb-0">{{ $amenity->name }}</div>
                      <small class="text-muted text-truncate d-inline-block font-12" style="max-width: 240px;">
                        {{ $amenity->description ?: 'Available for all verified residents.' }}
                      </small>
                    </div>
                  </div>
                </td>

                {{-- Timings & Slot --}}
                <td class="py-3">
                  <div class="d-flex flex-column gap-1">
                    <span class="badge bg-light text-dark border font-12 fw-semibold text-start">
                      <i class="mdi mdi-clock-outline text-success me-1"></i>
                      {{ substr($amenity->opening_time, 0, 5) }} - {{ substr($amenity->closing_time, 0, 5) }}
                    </span>
                    <small class="text-muted font-11">
                      <i class="mdi mdi-timer-sand me-1"></i> Slot: {{ $amenity->slot_duration_minutes }} mins
                    </small>
                  </div>
                </td>

                {{-- Booking Mode --}}
                <td class="py-3">
                  @if($amenity->booking_mode === 'automatic')
                    <span class="badge bg-soft-success text-success rounded-pill px-3 py-1 font-12">
                      <i class="mdi mdi-lightning-bolt me-1"></i> Instant
                    </span>
                  @else
                    <span class="badge bg-soft-warning text-warning rounded-pill px-3 py-1 font-12">
                      <i class="mdi mdi-account-clock-outline me-1"></i> Admin Approval
                    </span>
                  @endif
                </td>

                {{-- Status --}}
                <td class="py-3">
                  @if($amenity->status === 'active')
                    <span class="badge bg-success rounded-pill px-3 py-1 font-12">
                      <i class="mdi mdi-check-circle me-1"></i> Active
                    </span>
                  @else
                    <span class="badge bg-danger rounded-pill px-3 py-1 font-12">
                      <i class="mdi mdi-close-circle me-1"></i> Inactive
                    </span>
                  @endif
                </td>

                {{-- Bookings Count (For Admin) --}}
                @if($isResidentAdmin)
                  <td class="py-3 text-center">
                    <a href="{{ Route::has('member.amenities.bookings.all') ? route('member.amenities.bookings.all', ['amenity_id' => $amenity->id]) : url('residents/amenities/bookings/all?amenity_id=' . $amenity->id) }}" 
                       class="text-decoration-none">
                      <span class="badge bg-soft-primary text-primary rounded-pill px-3 py-1 font-12 fw-bold">
                        {{ $amenity->bookings_count ?? 0 }} Total
                      </span>
                      @if(($amenity->pending_bookings_count ?? 0) > 0)
                        <div class="mt-1">
                          <span class="badge bg-warning text-dark rounded-pill px-2 py-0 font-11 pulse-badge">
                            {{ $amenity->pending_bookings_count }} Pending
                          </span>
                        </div>
                      @endif
                    </a>
                  </td>
                @endif

                {{-- Action Buttons --}}
                <td class="py-3 text-end pe-4">
                  <div class="d-inline-flex flex-wrap gap-2 justify-content-end align-items-center">
                    {{-- Book Slot Button --}}
                    @if($amenity->status === 'active')
                      <a href="{{ Route::has('member.amenities.book') ? route('member.amenities.book', $amenity->id) : url('residents/amenities/' . $amenity->id . '/book') }}" 
                         class="btn btn-success btn-sm rounded-pill fw-bold px-3 py-1 shadow-sm" 
                         title="Book a slot for this amenity">
                        <i class="mdi mdi-calendar-plus me-1"></i> Book Slot
                      </a>
                    @else
                      <button class="btn btn-secondary btn-sm rounded-pill px-3 py-1 opacity-50" disabled>
                        Closed
                      </button>
                    @endif

                    @if($isResidentAdmin)
                      {{-- View All Bookings for this Amenity Button --}}
                      <a href="{{ Route::has('member.amenities.bookings.all') ? route('member.amenities.bookings.all', ['amenity_id' => $amenity->id]) : url('residents/amenities/bookings/all?amenity_id=' . $amenity->id) }}" 
                         class="btn btn-outline-info btn-sm rounded-pill fw-bold px-3 py-1" 
                         title="Manage and approve bookings for {{ $amenity->name }}">
                        <i class="mdi mdi-clipboard-list-outline me-1"></i> Bookings
                        @if(($amenity->bookings_count ?? 0) > 0)
                          <span class="badge bg-info text-white ms-1 rounded-pill">{{ $amenity->bookings_count }}</span>
                        @endif
                      </a>

                      {{-- Edit Amenity Button --}}
                      <a href="{{ Route::has('member.amenities.edit') ? route('member.amenities.edit', $amenity->id) : url('residents/amenities/' . $amenity->id . '/edit') }}" 
                         class="btn btn-outline-primary btn-sm rounded-pill fw-bold px-3 py-1" 
                         title="Edit details of {{ $amenity->name }}">
                        <i class="mdi mdi-pencil me-1"></i> Edit
                      </a>

                      {{-- Delete Amenity Button --}}
                      <form method="POST" action="{{ Route::has('member.amenities.destroy') ? route('member.amenities.destroy', $amenity->id) : url('residents/amenities/' . $amenity->id) }}" 
                            class="d-inline" 
                            onsubmit="return confirm('Are you sure you want to permanently delete \'{{ addslashes($amenity->name) }}\'? This will also delete related bookings.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill fw-bold px-3 py-1" title="Delete Amenity">
                          <i class="mdi mdi-delete me-1"></i> Delete
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="{{ $isResidentAdmin ? '6' : '5' }}" class="text-center py-5 text-muted">
                  <i class="mdi mdi-domain-off mdi-48px text-secondary d-block mb-2"></i>
                  <h6 class="fw-bold">No Amenities Found</h6>
                  <p class="small mb-3">No facilities are currently registered for your society.</p>
                  @if($isResidentAdmin)
                    <a href="{{ Route::has('member.amenities.create') ? route('member.amenities.create') : url('residents/amenities/create') }}" class="btn btn-success btn-sm rounded-pill px-4 fw-bold shadow-sm">
                      <i class="mdi mdi-plus me-1"></i> Add First Amenity
                    </a>
                  @endif
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- VIEW 2: Modern Animated Cards Grid (Alternative) --}}
  <div id="amenitiesGridView" class="row d-none">
    @forelse($amenities as $amenity)
      <div class="col-md-6 col-lg-4 mb-4 amenity-item" data-name="{{ strtolower($amenity->name) }}">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden amenity-card-hover">
          <div style="height: 190px; position: relative;" class="bg-light">
            @if($amenity->image)
              <div class="image-popup-hover w-100 h-100" 
                   data-preview-image="{{ asset('storage/' . $amenity->image) }}"
                   data-preview-title="{{ $amenity->name }}"
                   title="Click to preview image">
                <img src="{{ asset('storage/' . $amenity->image) }}" class="w-100 h-100 img-popup-clickable" style="object-fit: cover;" alt="{{ $amenity->name }}">
                <div class="image-zoom-overlay d-flex align-items-center justify-content-center">
                  <i class="mdi mdi-arrow-expand-all text-white font-24"></i>
                </div>
              </div>
            @else
              <div class="d-flex align-items-center justify-content-center w-100 h-100 bg-soft-success text-success">
                <i class="mdi mdi-domain mdi-48px"></i>
              </div>
            @endif

            <span class="position-absolute top-0 end-0 m-3 badge {{ $amenity->booking_mode === 'automatic' ? 'bg-success' : 'bg-warning text-dark' }} rounded-pill px-3 py-2 shadow-sm font-12 fw-bold">
              <i class="mdi {{ $amenity->booking_mode === 'automatic' ? 'mdi-lightning-bolt' : 'mdi-clock-outline' }} me-1"></i>
              {{ $amenity->booking_mode === 'automatic' ? 'Instant Booking' : 'Requires Approval' }}
            </span>

            @if($amenity->status === 'inactive')
              <span class="position-absolute top-0 start-0 m-3 badge bg-danger rounded-pill px-3 py-2 shadow-sm font-12 fw-bold">
                Inactive
              </span>
            @endif
          </div>

          <div class="card-body p-4 d-flex flex-column">
            <h5 class="fw-bold text-dark mb-1">{{ $amenity->name }}</h5>
            <p class="text-muted small mb-3 line-clamp-2">{{ Str::limit($amenity->description, 85) ?: 'Available for all society residents.' }}</p>

            <div class="bg-light rounded-3 p-3 mb-3 small">
              <div class="d-flex justify-content-between mb-1">
                <span class="text-muted"><i class="mdi mdi-clock-outline me-1"></i> Timings:</span>
                <span class="fw-bold text-dark">{{ substr($amenity->opening_time, 0, 5) }} - {{ substr($amenity->closing_time, 0, 5) }}</span>
              </div>
              <div class="d-flex justify-content-between mb-1">
                <span class="text-muted"><i class="mdi mdi-timer-sand me-1"></i> Slot Duration:</span>
                <span class="fw-bold text-dark">{{ $amenity->slot_duration_minutes }} mins</span>
              </div>
              <div class="d-flex justify-content-between">
                <span class="text-muted"><i class="mdi mdi-calendar-badge me-1"></i> Advance Limit:</span>
                <span class="fw-bold text-dark">{{ $amenity->advance_booking_days }} days</span>
              </div>
            </div>

            {{-- Action Buttons on Card --}}
            <div class="mt-auto d-flex flex-column gap-2">
              @if($amenity->status === 'active')
                <a href="{{ Route::has('member.amenities.book') ? route('member.amenities.book', $amenity->id) : url('residents/amenities/' . $amenity->id . '/book') }}" 
                   class="btn btn-success w-100 rounded-pill fw-bold py-2 shadow-sm">
                  <i class="mdi mdi-calendar-plus me-1"></i> Book Slot
                </a>
              @else
                <button class="btn btn-secondary w-100 rounded-pill fw-bold py-2 opacity-50" disabled>
                  Closed / Maintenance
                </button>
              @endif

              @if($isResidentAdmin)
                <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                  {{-- Bookings button --}}
                  <a href="{{ Route::has('member.amenities.bookings.all') ? route('member.amenities.bookings.all', ['amenity_id' => $amenity->id]) : url('residents/amenities/bookings/all?amenity_id=' . $amenity->id) }}" 
                     class="btn btn-outline-info btn-sm rounded-pill fw-bold flex-grow-1 px-3 py-1" 
                     title="View Bookings for {{ $amenity->name }}">
                    <i class="mdi mdi-clipboard-list-outline me-1"></i> Bookings
                    <span class="badge bg-info text-white ms-1">{{ $amenity->bookings_count ?? 0 }}</span>
                  </a>

                  {{-- Edit button --}}
                  <a href="{{ Route::has('member.amenities.edit') ? route('member.amenities.edit', $amenity->id) : url('residents/amenities/' . $amenity->id . '/edit') }}" 
                     class="btn btn-outline-primary btn-sm rounded-pill fw-bold px-3 py-1" 
                     title="Edit {{ $amenity->name }}">
                    <i class="mdi mdi-pencil me-1"></i> Edit
                  </a>

                  {{-- Delete button --}}
                  <form method="POST" action="{{ Route::has('member.amenities.destroy') ? route('member.amenities.destroy', $amenity->id) : url('residents/amenities/' . $amenity->id) }}" 
                        class="d-inline" 
                        onsubmit="return confirm('Are you sure you want to delete \'{{ addslashes($amenity->name) }}\'?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill fw-bold px-3 py-1" title="Delete Amenity">
                      <i class="mdi mdi-delete me-1"></i> Delete
                    </button>
                  </form>
                </div>
              @endif
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
  {{-- Amenity Analytics & Report Deep Dive Modal --}}
  <div class="modal fade" id="amenityAnalyticsModal" tabindex="-1" aria-labelledby="amenityAnalyticsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        {{-- Modal Header --}}
        <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, #065f46 0%, #059669 100%);">
          <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
              <i class="mdi mdi-chart-box-outline mdi-20px"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold text-white mb-0" id="amenityAnalyticsModalLabel">
                Facilities & Booking Analytics Report
              </h5>
              <small class="text-white-50 font-12">Real-time usage metrics and booking distribution</small>
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
                <div class="text-muted font-12 fw-semibold text-uppercase">Total Facilities</div>
                <div class="fs-3 fw-bold text-dark mt-1">{{ $analytics['total_facilities'] ?? $amenities->count() }}</div>
                <small class="badge bg-soft-success text-success rounded-pill font-11 mt-1">
                  {{ $analytics['active_facilities'] ?? 0 }} Active
                </small>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="bg-white p-3 rounded-3 shadow-sm border text-center h-100">
                <div class="text-muted font-12 fw-semibold text-uppercase">Total Bookings</div>
                <div class="fs-3 fw-bold text-primary mt-1">{{ $analytics['total_bookings'] ?? 0 }}</div>
                <small class="text-muted font-11">All time records</small>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="bg-white p-3 rounded-3 shadow-sm border text-center h-100">
                <div class="text-muted font-12 fw-semibold text-uppercase">This Month</div>
                <div class="fs-3 fw-bold text-info mt-1">{{ $analytics['this_month_bookings'] ?? 0 }}</div>
                <small class="text-muted font-11">Current cycle</small>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="bg-white p-3 rounded-3 shadow-sm border text-center h-100">
                <div class="text-muted font-12 fw-semibold text-uppercase">Today's Bookings</div>
                <div class="fs-3 fw-bold text-success mt-1">{{ $analytics['today_bookings'] ?? 0 }}</div>
                <small class="text-muted font-11">{{ \Carbon\Carbon::today()->format('d M Y') }}</small>
              </div>
            </div>
          </div>

          {{-- Booking Status Distribution Breakdown --}}
          @php
            $totB = max($analytics['total_bookings'] ?? 0, 1);
            $confPct = round((($analytics['confirmed_bookings'] ?? 0) / $totB) * 100);
            $pendPct = round((($analytics['pending_bookings'] ?? 0) / $totB) * 100);
            $rejPct = round((($analytics['rejected_bookings'] ?? 0) / $totB) * 100);
            $cancPct = round((($analytics['cancelled_bookings'] ?? 0) / $totB) * 100);
          @endphp
          <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold text-dark mb-0">
                  <i class="mdi mdi-chart-donut text-success me-1"></i> Booking Status Distribution
                </h6>
                <small class="text-muted">{{ $analytics['total_bookings'] ?? 0 }} Total Requests</small>
              </div>

              {{-- Stacked Progress Bar --}}
              <div class="progress mb-3 rounded-pill" style="height: 12px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $confPct }}%;" title="Confirmed: {{ $confPct }}%"></div>
                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pendPct }}%;" title="Pending: {{ $pendPct }}%"></div>
                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $rejPct }}%;" title="Rejected: {{ $rejPct }}%"></div>
                <div class="progress-bar bg-secondary" role="progressbar" style="width: {{ $cancPct }}%;" title="Cancelled: {{ $cancPct }}%"></div>
              </div>

              {{-- Legend Pills --}}
              <div class="row g-2 text-center font-12">
                <div class="col-6 col-md-3">
                  <div class="p-2 rounded bg-soft-success border border-success-subtle">
                    <div class="fw-bold text-success">{{ $analytics['confirmed_bookings'] ?? 0 }}</div>
                    <span class="text-muted">Confirmed ({{ $confPct }}%)</span>
                  </div>
                </div>
                <div class="col-6 col-md-3">
                  <div class="p-2 rounded bg-soft-warning border border-warning-subtle">
                    <div class="fw-bold text-warning">{{ $analytics['pending_bookings'] ?? 0 }}</div>
                    <span class="text-muted">Pending ({{ $pendPct }}%)</span>
                  </div>
                </div>
                <div class="col-6 col-md-3">
                  <div class="p-2 rounded bg-soft-danger border border-danger-subtle">
                    <div class="fw-bold text-danger">{{ $analytics['rejected_bookings'] ?? 0 }}</div>
                    <span class="text-muted">Rejected ({{ $rejPct }}%)</span>
                  </div>
                </div>
                <div class="col-6 col-md-3">
                  <div class="p-2 rounded bg-soft-secondary border border-secondary-subtle">
                    <div class="fw-bold text-secondary">{{ $analytics['cancelled_bookings'] ?? 0 }}</div>
                    <span class="text-muted">Cancelled ({{ $cancPct }}%)</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Popular Facilities Leaderboard --}}
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3">
              <h6 class="fw-bold text-dark mb-3">
                <i class="mdi mdi-trophy-outline text-warning me-1"></i> Most Utilized Facilities
              </h6>

              @if(isset($analytics['popular_amenities']) && $analytics['popular_amenities']->isNotEmpty())
                <div class="table-responsive">
                  <table class="table table-sm table-hover align-middle mb-0 font-13">
                    <thead class="table-light">
                      <tr>
                        <th>#</th>
                        <th>Facility</th>
                        <th>Mode</th>
                        <th>Slot Duration</th>
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
                          <td>
                            @if($pop->booking_mode === 'automatic')
                              <span class="badge bg-soft-success text-success rounded-pill font-11">Auto</span>
                            @else
                              <span class="badge bg-soft-warning text-warning rounded-pill font-11">Approval</span>
                            @endif
                          </td>
                          <td class="text-muted">{{ $pop->slot_duration_minutes }} mins</td>
                          <td class="text-end">
                            <span class="badge bg-success text-white rounded-pill px-2 py-1">
                              {{ $pop->bookings_count }} bookings
                            </span>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @else
                <div class="text-center py-3 text-muted font-13">
                  No booking activity recorded yet.
                </div>
              @endif
            </div>
          </div>
        </div>

        {{-- Modal Footer --}}
        <div class="modal-footer bg-white border-top px-4 py-3 d-flex justify-content-between align-items-center">
          <small class="text-muted font-12">
            <i class="mdi mdi-clock-outline me-1"></i> Updated in real-time
          </small>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-3 btn-sm" onclick="window.print()">
              <i class="mdi mdi-printer me-1"></i> Print / Save Report
            </button>
            <button type="button" class="btn btn-success rounded-pill px-4 btn-sm fw-bold" data-bs-dismiss="modal">
              Done
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  function switchView(mode) {
    const tableView = document.getElementById('amenitiesTableView');
    const gridView = document.getElementById('amenitiesGridView');
    const btnTable = document.getElementById('btnTableView');
    const btnGrid = document.getElementById('btnGridView');

    if (mode === 'table') {
      tableView.classList.remove('d-none');
      gridView.classList.add('d-none');
      btnTable.classList.add('active');
      btnGrid.classList.remove('active');
    } else {
      tableView.classList.add('d-none');
      gridView.classList.remove('d-none');
      btnGrid.classList.add('active');
      btnTable.classList.remove('active');
    }
  }

  // Real-time instant search filter
  document.getElementById('amenitySearch')?.addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase().trim();
    const items = document.querySelectorAll('.amenity-item');
    items.forEach(function(item) {
      const name = item.getAttribute('data-name') || '';
      if (name.includes(query)) {
        item.style.display = '';
      } else {
        item.style.display = 'none';
      }
    });
  });
</script>
@endsection
