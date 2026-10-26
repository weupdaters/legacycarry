@extends('admin.layouts.app')
@section('title', 'System Control Hub')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    .stat-hover-card {
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        border: 1px solid rgba(226, 232, 240, 0.8) !important;
    }
    .stat-hover-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 26px rgba(15, 23, 42, 0.08) !important;
        border-color: #cbd5e1 !important;
    }
    .pulse-dot-admin {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        animation: pulseAdmin 2s infinite;
    }
    @keyframes pulseAdmin {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1.1); box-shadow: 0 0 0 5px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    .badge-subtle-primary { background: #e0e7ff; color: #4338ca; }
    .badge-subtle-success { background: #dcfce7; color: #15803d; }
    .badge-subtle-warning { background: #fef3c7; color: #92400e; }
    .badge-subtle-danger { background: #fee2e2; color: #b91c1c; }
    .badge-subtle-info { background: #e0f2fe; color: #0369a1; }
    .badge-subtle-purple { background: #f3e8ff; color: #7e22ce; }
</style>

<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column mb-2">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-bold m-0">System Control Center</h4>
            <p class="text-muted mb-0 fs-13"><span id="live-dot" class="badge bg-success-subtle text-success me-1 fs-10"><span class="pulse-dot-admin bg-success me-1"></span> LIVE</span>Operational Intelligence • <span id="current-time"></span></p>
        </div>
        <div class="ms-auto mt-2 mt-sm-0 d-flex gap-2">
            @if(Route::has('admin.dashboard'))<a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-light border fw-bold"><i data-feather="refresh-cw" class="icon-xxs me-1 text-success"></i>Refresh</a>@endif
        </div>
    </div>

    {{-- LAUNCHPAD --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2 px-3">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <span class="text-muted fw-bold fs-10 text-uppercase me-1">⚡ Quick Launch:</span>
                        @if(Route::has('admin.amenities.index'))<a href="{{ route('admin.amenities.index') }}" class="btn btn-xs bg-info-subtle text-info fw-bold border-0 px-3 py-1"><i data-feather="layers" class="icon-xxs me-1"></i>Amenities</a>@endif
                        @if(Route::has('admin.polls.create'))<a href="{{ route('admin.polls.create') }}" class="btn btn-xs bg-primary-subtle text-primary fw-bold border-0 px-3 py-1"><i data-feather="check-square" class="icon-xxs me-1"></i>New Poll</a>@endif
                        @if(Route::has('admin.reports.index'))<a href="{{ route('admin.reports.index') }}" class="btn btn-xs bg-danger-subtle text-danger fw-bold border-0 px-3 py-1"><i data-feather="alert-triangle" class="icon-xxs me-1"></i>Bug Reports</a>@endif
                        @if(Route::has('admin.tickets.index'))<a href="{{ route('admin.tickets.index') }}" class="btn btn-xs bg-warning-subtle text-warning fw-bold border-0 px-3 py-1"><i data-feather="message-circle" class="icon-xxs me-1"></i>Tickets</a>@endif
                        @if(has_permission('notice_create'))<a href="{{ route('admin.notices.create') }}" class="btn btn-xs bg-primary-subtle text-primary fw-bold border-0 px-3 py-1"><i data-feather="plus" class="icon-xxs me-1"></i>Notice</a>@endif
                        @if(has_permission('guard_create'))<a href="{{ route('guards.create') }}" class="btn btn-xs bg-info-subtle text-info fw-bold border-0 px-3 py-1"><i data-feather="shield" class="icon-xxs me-1"></i>Guard</a>@endif
                        @if(has_permission('society_create'))<a href="{{ route('societies.create') }}" class="btn btn-xs bg-warning-subtle text-warning fw-bold border-0 px-3 py-1"><i data-feather="home" class="icon-xxs me-1"></i>Society</a>@endif
                        @if(has_permission('resident_assign_create'))<a href="{{ route('resident.assign.create') }}" class="btn btn-xs bg-danger-subtle text-danger fw-bold border-0 px-3 py-1"><i data-feather="user-plus" class="icon-xxs me-1"></i>Assign</a>@endif
                        @if(has_permission('service_create'))<a href="{{ route('services.create') }}" class="btn btn-xs bg-dark-subtle text-dark fw-bold border-0 px-3 py-1"><i data-feather="tool" class="icon-xxs me-1"></i>Service</a>@endif
                        @if(has_permission('shift_index'))<a href="{{ route('shift.guards.index') }}" class="btn btn-xs bg-secondary-subtle text-secondary fw-bold border-0 px-3 py-1"><i data-feather="clock" class="icon-xxs me-1"></i>Shifts</a>@endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- STAT WIDGETS ROW --}}
    <div class="row g-3 mb-4">
        @php $statCards = [
            ['label'=>'Total Revenue','val'=>'₹'.number_format($totalRevenue),'icon'=>'credit-card','color'=>'success','sub'=>'Financial Flow','route'=>'admin.payments.index'],
            ['label'=>'Amenities & Facilities','val'=>number_format($totalAmenities ?? 0),'icon'=>'layers','color'=>'info','sub'=>($pendingAmenityBookings ?? 0).' Pending Bookings','route'=>'admin.amenities.index'],
            ['label'=>'Community Polls','val'=>number_format($totalPolls ?? 0),'icon'=>'check-square','color'=>'primary','sub'=>($activePolls ?? 0).' Active • '.($totalPollVotes ?? 0).' Votes','route'=>'admin.polls.index'],
            ['label'=>'Reported Issues','val'=>number_format($totalReports ?? 0),'icon'=>'alert-triangle','color'=>'danger','sub'=>($pendingReports ?? 0).' Pending Review','route'=>'admin.reports.index'],
            ['label'=>'Society Tickets','val'=>number_format($totalTickets ?? 0),'icon'=>'message-circle','color'=>'warning','sub'=>($pendingTickets ?? 0).' Open Complaints','route'=>'admin.tickets.index'],
            ['label'=>'Residents','val'=>number_format($totalResidents),'icon'=>'users','color'=>'primary','sub'=>$pendingVerifications.' Pending KYC','route'=>'resident.assign.index'],
            ['label'=>'Active Jobs','val'=>$activeJobs,'icon'=>'briefcase','color'=>'warning','sub'=>$totalJobs.' Total Posts','route'=>'jobs.index'],
            ['label'=>'Societies','val'=>$totalSocieties,'icon'=>'home','color'=>'info','sub'=>'Managed Sites','route'=>'societies.index'],
            ['label'=>'Guards','val'=>$totalGuards,'icon'=>'shield','color'=>'danger','sub'=>$activeGuardsOnShift.' On Shift','route'=>'shift.guards.index'],
            ['label'=>'Job Applicants','val'=>$jobApplicants,'icon'=>'user-check','color'=>'dark','sub'=>'Total Applications','route'=>'jobs.index'],
            ['label'=>'Notices','val'=>$totalNotices,'icon'=>'bell','color'=>'secondary','sub'=>'Broadcasts Sent','route'=>'admin.notices.index'],
            ['label'=>'Visitor Passes','val'=>$totalPasses,'icon'=>'key','color'=>'primary','sub'=>'Access Granted','route'=>'guards.all-pass-history'],
        ]; @endphp
        @foreach($statCards as $sc)
        <div class="col-xl-3 col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100 stat-hover-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div class="bg-{{ $sc['color'] }}-subtle rounded p-2"><i data-feather="{{ $sc['icon'] }}" class="text-{{ $sc['color'] }} icon-sm"></i></div>
                        @if(Route::has($sc['route']))<a href="{{ route($sc['route']) }}" class="btn btn-xs btn-primary fw-bold px-3 text-white" style="border-radius:6px; font-size:11px;">View</a>@endif
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">{{ $sc['val'] }}</h3>
                    <p class="text-{{ $sc['color'] }} fw-bold fs-11 mb-0">{{ $sc['label'] }}</p>
                    <p class="text-muted fs-11 mb-0">{{ $sc['sub'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- REAL-TIME LIVE USERS & API TRAFFIC MONITOR --}}
    <div class="row g-3 mb-4">
        {{-- LIVE USERS ONLINE (NOW) --}}
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-2 px-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="pulse-dot-admin bg-success"></span>
                        <h5 class="card-title fw-bold mb-0 fs-14 text-dark">Live Users Online</h5>
                        <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0 fs-11 fw-bold" id="liveUsersBadge">{{ $liveUsersCount }} Active Now</span>
                    </div>
                    <span class="badge bg-light text-muted border fs-10" id="liveUserSyncStatus"><i data-feather="activity" class="icon-xxs me-1 text-success"></i>Auto-sync</span>
                </div>
                <div class="card-body p-0">
                    <div class="p-2 border-bottom bg-light-subtle d-flex justify-content-between align-items-center fs-11 text-muted px-3">
                        <span><i data-feather="smartphone" class="icon-xxs me-1 text-primary"></i>Mobile App & Web Telemetry</span>
                        <span>Active within 5 mins</span>
                    </div>
                    <div class="list-group list-group-flush" id="liveUsersList" style="max-height: 380px; overflow-y: auto;">
                        @forelse($liveUsers as $lu)
                        <div class="list-group-item px-3 py-2 border-0 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center min-width-0">
                                <div class="position-relative me-2 flex-shrink-0">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-12" style="width:34px;height:34px;">
                                        {{ substr($lu->name ?? 'U', 0, 1) }}
                                    </div>
                                    <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" style="width:9px;height:9px;"></span>
                                </div>
                                <div class="min-width-0">
                                    <div class="d-flex align-items-center gap-1">
                                        <p class="mb-0 fw-bold text-dark fs-12 text-truncate">{{ $lu->name }}</p>
                                        <span class="badge badge-subtle-primary fs-9 px-1">{{ ucfirst($lu->role ?? 'User') }}</span>
                                    </div>
                                    <p class="mb-0 text-muted fs-11 text-truncate">
                                        {{ $lu->details->society->name ?? ($lu->Society->name ?? 'Standalone') }}
                                        @if(!empty($lu->details->flat->flat_no)) • Flat {{ $lu->details->flat->flat_no }} @endif
                                    </p>
                                </div>
                            </div>
                            <div class="text-end ms-2 flex-shrink-0">
                                <span class="badge bg-success-subtle text-success fs-10 fw-semibold d-block">● Online</span>
                                <span class="text-muted fs-10">{{ $lu->last_seen_at ? $lu->last_seen_at->diffForHumans() : 'Just now' }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="p-4 text-center text-muted fs-12" id="noLiveUsersPlaceholder">
                            <i data-feather="users" class="icon-md mb-2 text-muted"></i>
                            <p class="mb-0">No active mobile or web users in the last 5 minutes.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- REAL-TIME API TRAFFIC STREAM --}}
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-2 px-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary-subtle text-primary rounded p-1"><i data-feather="zap" class="icon-xs"></i></div>
                        <h5 class="card-title fw-bold mb-0 fs-14 text-dark">Live API Traffic Stream</h5>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0 fs-11 fw-bold">0% App Changes</span>
                    </div>
                    <span class="text-muted fs-11"><i data-feather="clock" class="icon-xxs me-1"></i>Real-Time Log</span>
                </div>
                <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                    <table class="table table-hover table-centered table-nowrap mb-0" id="apiActivityTable">
                        <thead class="bg-light fs-11 fw-bold text-muted text-uppercase sticky-top">
                            <tr>
                                <th class="ps-3">Method & Route</th>
                                <th>User / Caller</th>
                                <th>Device</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Latency & Time</th>
                            </tr>
                        </thead>
                        <tbody id="apiActivityTbody">
                            @forelse($recentApiActivities as $act)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        @php
                                            $methodColors = [
                                                'GET' => 'success',
                                                'POST' => 'primary',
                                                'PUT' => 'warning',
                                                'PATCH' => 'info',
                                                'DELETE' => 'danger'
                                            ];
                                            $mColor = $methodColors[$act->method] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $mColor }}-subtle text-{{ $mColor }} fw-bold fs-10 px-2 py-1">{{ $act->method }}</span>
                                        <code class="text-dark fs-11">{{ \Illuminate\Support\Str::limit($act->endpoint, 28) }}</code>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark fs-12 d-block">{{ $act->user->name ?? 'Mobile User #' . ($act->user_id ?? 'Guest') }}</span>
                                    <span class="text-muted fs-10">{{ $act->ip_address ?? '—' }}</span>
                                </td>
                                <td>
                                    @if(str_contains($act->device_type, 'Mobile'))
                                        <span class="badge bg-info-subtle text-info fs-10 px-2 py-1"><i data-feather="smartphone" class="icon-xxs me-1"></i>Mobile App</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary fs-10 px-2 py-1"><i data-feather="monitor" class="icon-xxs me-1"></i>Web</span>
                                    @endif
                                </td>
                                <td>
                                    @if($act->status_code >= 200 && $act->status_code < 300)
                                        <span class="badge bg-success-subtle text-success fs-10 fw-bold">{{ $act->status_code }} OK</span>
                                    @elseif($act->status_code >= 400 && $act->status_code < 500)
                                        <span class="badge bg-warning-subtle text-warning fs-10 fw-bold">{{ $act->status_code }}</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger fs-10 fw-bold">{{ $act->status_code }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <span class="text-dark fw-bold fs-11 d-block">{{ $act->response_time_ms ? round($act->response_time_ms) . ' ms' : '< 1 ms' }}</span>
                                    <span class="text-muted fs-10">{{ $act->created_at ? $act->created_at->diffForHumans() : 'Just now' }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr id="noApiActivityRow"><td colspan="5" class="text-center text-muted py-4 fs-12">No API requests captured yet. Waiting for live hits...</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- PERFORMANCE GAUGE CYCLES --}}
    <div class="row g-3 mb-4">
        @php $cycles = [
            ['id'=>'adm-occ','label'=>'Guard Coverage','val'=>round($occupancyRate),'color'=>'#4A00E0'],
            ['id'=>'adm-res','label'=>'Job Resolution','val'=>round($resolutionRate),'color'=>'#10b981'],
            ['id'=>'adm-ver','label'=>'KYC Compliance','val'=>round($verificationRate),'color'=>'#f59e0b'],
            ['id'=>'adm-up','label'=>'System Uptime','val'=>99,'color'=>'#3b82f6'],
        ]; @endphp
        @foreach($cycles as $c)
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100 stat-hover-card">
                <div class="card-body text-center p-2">
                    <div id="{{ $c['id'] }}" style="min-height:140px;"></div>
                    <p class="text-muted fw-bold mb-0 fs-11 text-uppercase">{{ $c['label'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- SECTION: AMENITIES & POLLS OVERVIEW --}}
    <div class="row g-3 mb-4">
        {{-- AMENITY BOOKINGS TRACKER --}}
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-info-subtle text-info rounded p-1"><i data-feather="layers" class="icon-xs"></i></div>
                        <h5 class="card-title fw-bold mb-0 fs-14">Facility & Amenity Reservations</h5>
                    </div>
                    @if(Route::has('admin.amenities.bookings'))
                        <a href="{{ route('admin.amenities.bookings') }}" class="btn btn-xs btn-light border fw-bold">Manage Bookings →</a>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered table-nowrap mb-0">
                        <thead class="bg-light fs-11 fw-bold text-muted text-uppercase">
                            <tr>
                                <th class="ps-3">Facility</th>
                                <th>Society / Resident</th>
                                <th>Schedule</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAmenityBookings as $booking)
                            <tr>
                                <td class="ps-3 fw-semibold text-dark">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-info-subtle text-info rounded px-2 py-1 fs-12 fw-bold">
                                            🏊
                                        </div>
                                        <div>
                                            <span class="d-block fw-bold fs-13">{{ $booking->amenity->name ?? 'Facility' }}</span>
                                            <span class="text-muted fs-11">{{ ucfirst($booking->amenity->booking_mode ?? 'Slot') }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark fs-12 d-block">{{ $booking->society->name ?? 'Society' }}</span>
                                    <span class="text-muted fs-11">{{ $booking->user->name ?? 'Resident' }}</span>
                                </td>
                                <td>
                                    <span class="fs-12 fw-semibold d-block">{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') : '—' }}</span>
                                    <span class="text-muted fs-11">{{ $booking->start_time ?? '' }} - {{ $booking->end_time ?? '' }}</span>
                                </td>
                                <td>
                                    @if($booking->status === 'confirmed' || $booking->status === 'approved')
                                        <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 fs-10 fw-bold">Approved</span>
                                    @elseif($booking->status === 'pending')
                                        <span class="badge bg-warning-subtle text-warning rounded-pill px-2 py-1 fs-10 fw-bold">Pending</span>
                                    @elseif($booking->status === 'rejected')
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1 fs-10 fw-bold">Rejected</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 fs-10 fw-bold">{{ ucfirst($booking->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    @if(Route::has('admin.amenities.bookings'))
                                        <a href="{{ route('admin.amenities.bookings') }}" class="btn btn-xs btn-outline-primary rounded px-2 py-1 fw-bold fs-11">View</a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4 fs-12">No recent amenity reservations recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ACTIVE COMMUNITY POLLS OVERVIEW --}}
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary-subtle text-primary rounded p-1"><i data-feather="check-square" class="icon-xs"></i></div>
                        <h5 class="card-title fw-bold mb-0 fs-14">Community Polls Intelligence</h5>
                    </div>
                    @if(Route::has('admin.polls.index'))
                        <a href="{{ route('admin.polls.index') }}" class="btn btn-xs btn-light border fw-bold">All Polls →</a>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered table-nowrap mb-0">
                        <thead class="bg-light fs-11 fw-bold text-muted text-uppercase">
                            <tr>
                                <th class="ps-3">Poll Title</th>
                                <th>Society</th>
                                <th>Votes</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Analytics</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPolls as $poll)
                            @php $isLive = $poll->isVotingOpen(); @endphp
                            <tr>
                                <td class="ps-3">
                                    <span class="fw-bold text-dark fs-13 d-block">{{ Str::limit($poll->title, 28) }}</span>
                                    <span class="text-muted fs-11">Ends: {{ $poll->end_date ? $poll->end_date->format('M d, Y') : '—' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light border text-dark fs-11">{{ $poll->society->name ?? 'All Societies' }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-primary fs-12">{{ $poll->votes_count }}</span>
                                    <span class="text-muted fs-11">votes</span>
                                </td>
                                <td>
                                    @if($isLive)
                                        <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 fs-10 fw-bold">
                                            <span class="pulse-dot-admin bg-success me-1"></span> Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 fs-10 fw-bold">Closed</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    @if(Route::has('admin.polls.results'))
                                        <a href="{{ route('admin.polls.results', $poll->id) }}" class="btn btn-xs btn-primary fw-bold px-2 py-1 text-white fs-11" style="border-radius:6px;">Results</a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4 fs-12">No community polls available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION: SYSTEM FEEDBACK & BUG REPORTS --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-danger-subtle text-danger rounded p-1"><i data-feather="alert-triangle" class="icon-xs"></i></div>
                        <h5 class="card-title fw-bold mb-0 fs-14">Reported Bugs & User Complaints</h5>
                    </div>
                    @if(Route::has('admin.reports.index'))
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-xs btn-light border fw-bold">Full Issue Registry →</a>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered table-nowrap mb-0">
                        <thead class="bg-light fs-11 fw-bold text-muted text-uppercase">
                            <tr>
                                <th class="ps-3">Report ID</th>
                                <th>Category / Type</th>
                                <th>Reporter Email</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Reported Time</th>
                                <th class="text-end pe-3">Inspect</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentReports as $rep)
                            <tr>
                                <td class="ps-3 fw-bold text-dark font-monospace">#REP-{{ $rep->id }}</td>
                                <td>
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1 fs-11 fw-bold text-uppercase">
                                        {{ str_replace('_', ' ', $rep->report_type ?? 'Bug') }}
                                    </span>
                                </td>
                                <td class="fw-semibold text-dark fs-12">{{ $rep->email ?? ($rep->user->email ?? 'N/A') }}</td>
                                <td class="text-muted fs-12 text-truncate" style="max-width: 250px;">{{ Str::limit($rep->description, 50) }}</td>
                                <td>
                                    @if($rep->status === 'pending')
                                        <span class="badge bg-warning-subtle text-warning rounded-pill px-2 py-1 fs-10 fw-bold">Pending Review</span>
                                    @elseif($rep->status === 'resolved')
                                        <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 fs-10 fw-bold">Resolved</span>
                                    @else
                                        <span class="badge bg-info-subtle text-info rounded-pill px-2 py-1 fs-10 fw-bold">{{ ucfirst($rep->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-muted fs-11">{{ $rep->created_at ? $rep->created_at->format('M d, Y h:i A') : 'N/A' }}</td>
                                <td class="text-end pe-3">
                                    @if(Route::has('admin.reports.show'))
                                        <a href="{{ route('admin.reports.show', $rep->id) }}" class="btn btn-xs btn-primary fw-bold px-3 py-1 text-white fs-11" style="border-radius:6px;">View</a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-muted py-4 fs-12">No bug reports or user issues reported.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ANALYTICS CHARTS ROW 1 --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0 fs-14">System Onboarding & Revenue Trends</h5>
                    <span class="badge bg-success-subtle text-success fw-bold">This Week</span>
                </div>
                <div class="card-body pt-0"><div id="adm-area-main" style="min-height:300px;"></div></div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0"><h5 class="card-title fw-bold mb-0 fs-14">Workforce Distribution</h5></div>
                <div class="card-body pt-0 text-center">
                    <div id="adm-role-donut" style="min-height:270px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ANALYTICS CHARTS ROW 2 --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0"><h5 class="card-title fw-bold mb-0 fs-14">Job Market Segments</h5></div>
                <div class="card-body pt-0"><div id="adm-job-polar" style="min-height:280px;"></div></div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0"><h5 class="card-title fw-bold mb-0 fs-14">Society Activity Index</h5></div>
                <div class="card-body pt-0"><div id="adm-soc-bar" style="min-height:280px;"></div></div>
            </div>
        </div>
    </div>

    {{-- DATA TABLES ROW --}}
    <div class="row g-3">
        <div class="col-xl-8">

            {{-- FINANCIAL LOG --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0 fs-14">Financial Flow Intelligence</h5>
                    @if(Route::has('admin.payments.index'))<a href="{{ route('admin.payments.index') }}" class="btn btn-xs btn-light border fw-bold">Full Log →</a>@endif
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered table-nowrap mb-0">
                        <thead class="bg-light fs-11 fw-bold text-muted text-uppercase">
                            <tr><th class="ps-4">Ref</th><th>Payer</th><th>Amount</th><th>Method</th><th>Date</th><th class="text-end pe-4">Action</th></tr>
                        </thead>
                        <tbody>
                            @forelse($recentPayments as $pay)
                            <tr>
                                <td class="ps-4 fw-bold text-dark">#TXN-{{ $pay->id }}</td>
                                <td class="fw-semibold">{{ $pay->payer->name ?? '—' }}</td>
                                <td class="fw-bold text-success">₹{{ number_format($pay->amount) }}</td>
                                <td><span class="badge bg-light border text-dark">{{ strtoupper($pay->payment_method ?? 'CASH') }}</span></td>
                                <td class="text-muted fs-12">{{ $pay->created_at->format('M d, h:i A') }}</td>
                                <td class="text-end pe-4">@if(Route::has('admin.payments.show'))<a href="{{ route('admin.payments.show', $pay->id) }}" class="btn btn-primary btn-sm fw-bold px-3" style="font-size:12px;">View</a>@endif</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3 fs-13">No transactions yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- JOB LOG --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0 fs-14">Marketplace Activity</h5>
                    @if(Route::has('jobs.index'))<a href="{{ route('jobs.index') }}" class="btn btn-xs btn-light border fw-bold">Full Registry →</a>@endif
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered table-nowrap mb-0">
                        <thead class="bg-light fs-11 fw-bold text-muted text-uppercase">
                            <tr><th class="ps-4">ID</th><th>Title</th><th>Status</th><th class="text-end pe-4">Controls</th></tr>
                        </thead>
                        <tbody>
                            @foreach($recentJobs as $job)
                            <tr>
                                <td class="ps-4 fw-bold text-primary">#{{ $job->id }}</td>
                                <td class="fw-semibold text-dark">{{ $job->job_title }}</td>
                                <td><span class="badge rounded-pill px-3 {{ $job->status=='active' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">{{ ucfirst($job->status) }}</span></td>
                                <td class="text-end pe-4">
                                    @if(Route::has('jobs.view'))<a href="{{ route('jobs.view', $job->id) }}" class="btn btn-primary btn-sm fw-bold px-3" style="font-size:12px; border-radius:6px;">View</a>@endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- RIGHT PANEL --}}
        <div class="col-xl-4">

            {{-- WORKFORCE HEALTH --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-bottom"><h5 class="card-title fw-bold mb-0 fs-14">Workforce Health Metrics</h5></div>
                <div class="card-body">
                    @php $metrics = [
                        ['label'=>'Guard Coverage','val'=>round($occupancyRate),'color'=>'primary'],
                        ['label'=>'KYC Verification','val'=>round($verificationRate),'color'=>'success'],
                        ['label'=>'Job Fulfillment','val'=>round($resolutionRate),'color'=>'warning'],
                        ['label'=>'System Uptime','val'=>99,'color'=>'info'],
                    ]; @endphp
                    @foreach($metrics as $m)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fs-12 fw-bold text-dark">{{ $m['label'] }}</span>
                            <span class="fs-12 fw-bold text-{{ $m['color'] }}">{{ $m['val'] }}%</span>
                        </div>
                        <div class="progress" style="height:6px;border-radius:10px;">
                            <div class="progress-bar bg-{{ $m['color'] }}" style="width:{{ $m['val'] }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- RESIDENT REGISTRY --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0 fs-14">Recent Residents</h5>
                    @if(Route::has('resident.assign.index'))<a href="{{ route('resident.assign.index') }}" class="fs-11 text-primary fw-bold">All →</a>@endif
                </div>
                <ul class="list-group list-group-flush">
                    @foreach($recentResidents as $res)
                    <li class="list-group-item px-3 py-2 border-0">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold fs-12" style="width:30px;height:30px;flex-shrink:0;">{{ substr($res->name,0,1) }}</div>
                            <div class="flex-grow-1 min-width-0">
                                <p class="mb-0 fw-bold text-dark fs-13 text-truncate">{{ $res->name }}</p>
                                <p class="mb-0 text-muted fs-11">{{ $res->Society->name ?? 'Standalone' }}</p>
                            </div>
                            <span class="badge {{ $res->is_active ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} ms-2 fw-bold fs-10">{{ $res->is_active ? 'OK' : 'Review' }}</span>
                            @if(Route::has('residents.view'))<a href="{{ route('residents.view', $res->id) }}" class="btn btn-primary btn-sm fw-bold px-3 ms-2" style="font-size:12px;">View</a>@endif
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- BROADCASTS --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0 fs-14">System Broadcasts</h5>
                    @if(Route::has('admin.notices.index'))<a href="{{ route('admin.notices.index') }}" class="fs-11 text-primary fw-bold">All →</a>@endif
                </div>
                <ul class="list-group list-group-flush">
                    @foreach($recentNotices as $notice)
                    <li class="list-group-item px-3 py-2 border-0">
                        <div class="d-flex align-items-start">
                            <div class="bg-primary-subtle text-primary rounded p-1 me-3 mt-1"><i data-feather="bell" class="icon-xs"></i></div>
                            <div>
                                <p class="mb-0 fw-bold text-dark fs-13">{{ $notice->title }}</p>
                                <span class="text-muted fs-11">{{ $notice->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @if(Route::has('admin.notices.create'))
                <div class="p-3 border-top"><a href="{{ route('admin.notices.create') }}" class="btn btn-outline-primary btn-sm w-100 fw-bold">+ New Broadcast</a></div>
                @endif
            </div>
        </div>
    </div>

</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof feather !== 'undefined') feather.replace();
    const el = document.getElementById('current-time');
    if(el) { const tick = () => el.innerText = new Date().toLocaleString(); tick(); setInterval(tick,1000); }

    const c = ['#4A00E0','#10b981','#f59e0b','#3b82f6','#ef4444','#6c757d'];
    const R = (id, opts) => { const e = document.getElementById(id); if(e){ try{ new ApexCharts(e,opts).render(); }catch(ex){ console.warn(id,ex); } } };

    // Cycles
    @foreach($cycles as $cy)
    R("{{ $cy['id'] }}", { series:[{{ $cy['val'] }}], chart:{height:150,type:'radialBar'}, plotOptions:{radialBar:{hollow:{size:'62%'}, dataLabels:{name:{show:false},value:{show:true,fontSize:'16px',fontWeight:800,color:'#1e293b',offsetY:6}}}}, colors:['{{ $cy['color'] }}'] });
    @endforeach

    // Area chart
    R("adm-area-main",{ series:[{name:'Onboarding',data:[31,40,28,51,42,109,100]},{name:'Revenue',data:[11,32,45,32,34,52,41]}], chart:{height:300,type:'area',toolbar:{show:false}}, stroke:{curve:'smooth',width:3}, colors:[c[0],c[1]], fill:{type:'gradient',gradient:{opacityFrom:0.4,opacityTo:0.05}}, xaxis:{categories:['Mon','Tue','Wed','Thu','Fri','Sat','Sun']}, grid:{borderColor:'#f1f5f9',strokeDashArray:4} });

    // Role donut
    R("adm-role-donut",{ series:[{{ $roleDistribution->pluck('total')->implode(',') }}], labels:[@foreach($roleDistribution as $r)'{{ ucfirst($r->role) }}',@endforeach], chart:{type:'donut',height:270}, colors:c, legend:{position:'bottom',fontSize:'11px',fontWeight:600}, plotOptions:{pie:{donut:{size:'78%',labels:{show:true,total:{show:true,label:'Users',fontSize:'11px'}}}}} });

    // Job polar
    @if($jobTypeDistribution->count())
    R("adm-job-polar",{ series:[{{ $jobTypeDistribution->pluck('total')->implode(',') }}], labels:[@foreach($jobTypeDistribution as $jt)'{{ str_replace('_',' ',$jt->job_type) }}',@endforeach], chart:{type:'polarArea',height:280}, colors:c, legend:{show:false}, stroke:{colors:['#fff']}, fill:{opacity:0.8} });
    @endif

    // Society bar
    @if($societyDistribution->count())
    R("adm-soc-bar",{ series:[{name:'Entities',data:[{{ $societyDistribution->pluck('total')->implode(',') }}]}], xaxis:{categories:[@foreach($societyDistribution as $s)'{{ $s->name }}',@endforeach]}, chart:{type:'bar',height:280,toolbar:{show:false}}, plotOptions:{bar:{borderRadius:7,distributed:true}}, colors:c, dataLabels:{enabled:false}, grid:{borderColor:'#f1f5f9',strokeDashArray:4} });
    @endif

    // Revenue radial
    R("adm-rev-radial",{ series:[{{ round($revenuePercent ?? 0) }}], chart:{height:220,type:'radialBar'}, plotOptions:{radialBar:{hollow:{size:'70%'}, dataLabels:{name:{show:true,fontSize:'11px',color:'#64748b',offsetY:-8},value:{show:true,fontSize:'24px',fontWeight:800,color:'#1e293b',offsetY:4,formatter:v=>v+'%'}}}}, colors:[c[0]], labels:['Target'] });

    // REAL-TIME AUTO SYNC (Live Users & API Traffic)
    const liveFeedUrl = "{{ route('admin.live-feed') }}";
    function refreshLiveFeed() {
        fetch(liveFeedUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(data => {
                // Update live count badge
                const countBadge = document.getElementById('liveUsersBadge');
                if (countBadge) countBadge.innerText = data.count + ' Active Now';

                // Update users list
                const usersList = document.getElementById('liveUsersList');
                if (usersList && data.users) {
                    if (data.users.length === 0) {
                        usersList.innerHTML = `<div class="p-4 text-center text-muted fs-12"><p class="mb-0">No active mobile or web users in the last 5 minutes.</p></div>`;
                    } else {
                        let html = '';
                        data.users.forEach(u => {
                            html += `
                            <div class="list-group-item px-3 py-2 border-0 border-bottom d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center min-width-0">
                                    <div class="position-relative me-2 flex-shrink-0">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-12" style="width:34px;height:34px;">
                                            ${(u.name || 'U').charAt(0)}
                                        </div>
                                        <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" style="width:9px;height:9px;"></span>
                                    </div>
                                    <div class="min-width-0">
                                        <div class="d-flex align-items-center gap-1">
                                            <p class="mb-0 fw-bold text-dark fs-12 text-truncate">${u.name}</p>
                                            <span class="badge badge-subtle-primary fs-9 px-1">${u.role}</span>
                                        </div>
                                        <p class="mb-0 text-muted fs-11 text-truncate">${u.society} ${u.flat ? '• Flat ' + u.flat : ''}</p>
                                    </div>
                                </div>
                                <div class="text-end ms-2 flex-shrink-0">
                                    <span class="badge bg-success-subtle text-success fs-10 fw-semibold d-block">● Online</span>
                                    <span class="text-muted fs-10">${u.last_seen}</span>
                                </div>
                            </div>`;
                        });
                        usersList.innerHTML = html;
                    }
                }

                // Update activity stream table
                const actTbody = document.getElementById('apiActivityTbody');
                if (actTbody && data.activities) {
                    if (data.activities.length === 0) {
                        actTbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4 fs-12">No API requests captured yet. Waiting for live hits...</td></tr>`;
                    } else {
                        let rows = '';
                        data.activities.forEach(a => {
                            const methodColors = { 'GET':'success', 'POST':'primary', 'PUT':'warning', 'PATCH':'info', 'DELETE':'danger' };
                            const mColor = methodColors[a.method] || 'secondary';
                            const devBadge = (a.device_type && a.device_type.includes('Mobile'))
                                ? `<span class="badge bg-info-subtle text-info fs-10 px-2 py-1"><i data-feather="smartphone" class="icon-xxs me-1"></i>Mobile App</span>`
                                : `<span class="badge bg-secondary-subtle text-secondary fs-10 px-2 py-1"><i data-feather="monitor" class="icon-xxs me-1"></i>Web</span>`;
                            const statusBadge = a.status_code >= 200 && a.status_code < 300
                                ? `<span class="badge bg-success-subtle text-success fs-10 fw-bold">${a.status_code} OK</span>`
                                : `<span class="badge bg-warning-subtle text-warning fs-10 fw-bold">${a.status_code}</span>`;

                            const epShort = a.endpoint.length > 28 ? a.endpoint.substring(0, 28) + '...' : a.endpoint;

                            rows += `
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-${mColor}-subtle text-${mColor} fw-bold fs-10 px-2 py-1">${a.method}</span>
                                        <code class="text-dark fs-11">${epShort}</code>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark fs-12 d-block">${a.user_name}</span>
                                </td>
                                <td>${devBadge}</td>
                                <td>${statusBadge}</td>
                                <td class="text-end pe-3">
                                    <span class="text-dark fw-bold fs-11 d-block">${a.response_time_ms ? Math.round(a.response_time_ms) + ' ms' : '< 1 ms'}</span>
                                    <span class="text-muted fs-10">${a.time_ago}</span>
                                </td>
                            </tr>`;
                        });
                        actTbody.innerHTML = rows;
                    }
                }
                if (typeof feather !== 'undefined') feather.replace();
            })
            .catch(err => console.debug('Live feed sync error:', err));
    }

    // Refresh every 8 seconds
    setInterval(refreshLiveFeed, 8000);
});
</script>
@endpush
@endsection
