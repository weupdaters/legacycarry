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
</style>

<div class="container-fluid">
  {{-- Header --}}
  <div class="py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
      <h4 class="fw-semibold mb-1">
        <i class="bi bi-grid-fill me-1 text-primary"></i> Amenity Management
      </h4>
      <p class="text-muted small mb-0">Configure society amenities, opening hours, booking modes, and slots.</p>
    </div>

    <div class="d-flex flex-wrap gap-2">
      <button type="button" class="btn btn-outline-success btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#amenityAnalyticsModal">
        <i class="bi bi-bar-chart-fill me-1"></i> Report & Analytics
      </button>
      <a href="{{ route('admin.amenities.bookings') }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-calendar-check me-1"></i> View All Bookings
      </a>
      <a href="{{ route('admin.amenities.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Add Amenity
      </a>
    </div>
  </div>

  {{-- 4 Animated Report Analytics Cards --}}
  <div class="row g-3 mb-3">
    {{-- Card 1: Total Facilities --}}
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-4 h-100 bg-white report-stat-card" data-bs-toggle="modal" data-bs-target="#amenityAnalyticsModal" style="cursor: pointer;">
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
            <span class="text-success fw-semibold"><i class="bi bi-eye me-1"></i>Report</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Card 2: Total Bookings --}}
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-4 h-100 bg-white report-stat-card" data-bs-toggle="modal" data-bs-target="#amenityAnalyticsModal" style="cursor: pointer;">
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
            <span class="text-info fw-semibold"><i class="bi bi-graph-up me-1"></i>Analytics</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Card 3: Pending Approvals --}}
    <div class="col-sm-6 col-xl-3">
      <a href="{{ route('admin.amenities.bookings', ['status' => 'pending']) }}" class="text-decoration-none">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white report-stat-card">
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
                <span class="badge bg-warning text-dark rounded-pill font-11 px-2 py-1">Needs Action</span>
              @else
                <span class="badge bg-success-subtle text-success rounded-pill font-11 px-2 py-1">All Clear</span>
              @endif
            </div>
            <div class="mt-2 text-muted small d-flex align-items-center justify-content-between">
              <span>Awaiting decision</span>
              <span class="text-warning fw-semibold"><i class="bi bi-arrow-right me-1"></i>Review</span>
            </div>
          </div>
        </div>
      </a>
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
              <th>Slot Duration</th>
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
                  <small class="fw-semibold">{{ $amenity->slot_duration_minutes }} mins</small>
                </td>
                <td>
                  <span class="badge bg-{{ $amenity->status === 'active' ? 'success' : 'secondary' }}">
                    {{ ucfirst($amenity->status) }}
                  </span>
                </td>
                <td>
                  <a href="{{ route('admin.amenities.bookings', ['amenity_id' => $amenity->id, 'society_id' => $amenity->society_id]) }}" class="badge bg-info text-decoration-none px-3 py-2 shadow-sm" title="Click to view & approve bookings for {{ $amenity->name }}">
                    <i class="bi bi-calendar-check me-1"></i> {{ $amenity->bookings_count }} bookings
                  </a>
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm">
                    <a href="{{ route('admin.amenities.bookings', ['amenity_id' => $amenity->id, 'society_id' => $amenity->society_id]) }}" class="btn btn-outline-info" title="View & Approve Bookings">
                      <i class="bi bi-calendar-check"></i>
                    </a>
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

  {{-- Amenity Analytics & Report Deep Dive Modal --}}
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
@endsection
