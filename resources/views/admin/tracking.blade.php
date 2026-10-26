@extends('admin.layouts.app')
@section('title', 'Live Device & User Tracking Monitor')

@section('content')
<style>
    .tracking-stat-card {
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        border: 1px solid rgba(226, 232, 240, 0.85) !important;
        border-radius: 14px;
    }
    .tracking-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08) !important;
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
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column justify-content-between mb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success-subtle text-success fs-11 px-2 py-1 fw-bold">
                    <span class="pulse-dot-admin bg-success me-1"></span> LIVE MONITOR
                </span>
                <h4 class="fs-18 fw-bold m-0 text-dark">Real-Time User & Device Tracking</h4>
            </div>
            <p class="text-muted mb-0 fs-13">
                Tracking live mobile app (Flutter / Android / iOS) and web hits via zero-config backend telemetry.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
            <span class="badge bg-light text-muted border px-3 py-2 fs-12">
                <i data-feather="clock" class="icon-xxs me-1 text-primary"></i> <span id="current-tracking-time"></span>
            </span>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-light border fw-bold px-3">
                <i data-feather="arrow-left" class="icon-xxs me-1"></i> Dashboard
            </a>
            <button type="button" class="btn btn-sm btn-primary fw-bold px-3" onclick="refreshTrackingPageNow()">
                <i data-feather="refresh-cw" class="icon-xxs me-1" id="refreshIconSpin"></i> Sync Now
            </button>
        </div>
    </div>

    {{-- 4 TELEMETRY METRIC CARDS --}}
    <div class="row g-3 mb-4">
        {{-- Card 1: Live Connected Right Now --}}
        <div class="col-xl-3 col-md-6 col-6">
            <div class="card border-0 shadow-sm h-100 tracking-stat-card border-success">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="bg-success-subtle rounded p-2 position-relative">
                            <i data-feather="activity" class="text-success icon-sm"></i>
                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-white rounded-circle pulse-dot-admin" style="width:8px;height:8px;"></span>
                        </div>
                        <span class="badge bg-success text-white rounded-pill px-2 py-1 fs-10 fw-bold">● ONLINE NOW</span>
                    </div>
                    <h2 class="fw-bold mb-0 text-dark" id="metricLiveCount">{{ $liveUsersCount }}</h2>
                    <p class="text-success fw-bold fs-11 mb-0">Active Users & Devices</p>
                    <p class="text-muted fs-11 mb-0">Active in the last 5 minutes</p>
                </div>
            </div>
        </div>

        {{-- Card 2: Mobile App Hits Today --}}
        <div class="col-xl-3 col-md-6 col-6">
            <div class="card border-0 shadow-sm h-100 tracking-stat-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="bg-primary-subtle rounded p-2">
                            <i data-feather="smartphone" class="text-primary icon-sm"></i>
                        </div>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1 fs-10 fw-bold">MOBILE APP</span>
                    </div>
                    <h2 class="fw-bold mb-0 text-dark" id="metricMobileHits">{{ number_format($mobileAppHitsToday) }}</h2>
                    <p class="text-primary fw-bold fs-11 mb-0">App API Requests Today</p>
                    <p class="text-muted fs-11 mb-0">Android & iOS mobile client traffic</p>
                </div>
            </div>
        </div>

        {{-- Card 3: Web Portal Hits Today --}}
        <div class="col-xl-3 col-md-6 col-6">
            <div class="card border-0 shadow-sm h-100 tracking-stat-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="bg-info-subtle rounded p-2">
                            <i data-feather="monitor" class="text-info icon-sm"></i>
                        </div>
                        <span class="badge bg-info-subtle text-info rounded-pill px-2 py-1 fs-10 fw-bold">WEB PORTAL</span>
                    </div>
                    <h2 class="fw-bold mb-0 text-dark" id="metricWebHits">{{ number_format($webHitsToday) }}</h2>
                    <p class="text-info fw-bold fs-11 mb-0">Web Operations Today</p>
                    <p class="text-muted fs-11 mb-0">Admin & resident web sessions</p>
                </div>
            </div>
        </div>

        {{-- Card 4: Avg Telemetry Latency --}}
        <div class="col-xl-3 col-md-6 col-6">
            <div class="card border-0 shadow-sm h-100 tracking-stat-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="bg-warning-subtle rounded p-2">
                            <i data-feather="zap" class="text-warning icon-sm"></i>
                        </div>
                        <span class="badge bg-warning-subtle text-warning rounded-pill px-2 py-1 fs-10 fw-bold">SPEED</span>
                    </div>
                    <h2 class="fw-bold mb-0 text-dark">{{ $avgLatency }} <span class="fs-14 fw-normal text-muted">ms</span></h2>
                    <p class="text-warning fw-bold fs-11 mb-0">Avg API Response Time</p>
                    <p class="text-muted fs-11 mb-0">High-performance processing</p>
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN TRACKING PANELS: CONNECTED USERS & LIVE API TRAFFIC --}}
    <div class="row g-3 mb-4">
        
        {{-- LEFT PANEL: CONNECTED USERS & DEVICES (5 COLS) --}}
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3 px-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="pulse-dot-admin bg-success"></span>
                        <h5 class="card-title fw-bold mb-0 fs-14 text-dark">Live Connected Devices</h5>
                        <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 fs-11 fw-bold" id="liveUsersPageBadge">
                            {{ $liveUsersCount }} Connected Now
                        </span>
                    </div>
                    <span class="badge bg-light text-muted border fs-10">
                        <i data-feather="activity" class="icon-xxs me-1 text-success"></i>Auto-sync (5s)
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="p-2 border-bottom bg-light-subtle d-flex justify-content-between align-items-center fs-11 text-muted px-3">
                        <span><i data-feather="smartphone" class="icon-xxs me-1 text-primary"></i>Active Mobile & Web Telemetry</span>
                        <span>Within 5 minutes</span>
                    </div>

                    <div class="list-group list-group-flush" id="trackingUsersList" style="max-height: 540px; overflow-y: auto;">
                        @forelse($liveUsers as $lu)
                        <div class="list-group-item px-3 py-3 border-0 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center min-width-0">
                                <div class="position-relative me-3 flex-shrink-0">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-13" style="width:38px;height:38px;">
                                        {{ substr($lu->name ?? 'U', 0, 1) }}
                                    </div>
                                    <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" style="width:10px;height:10px;"></span>
                                </div>
                                <div class="min-width-0">
                                    <div class="d-flex align-items-center gap-1 mb-1">
                                        <p class="mb-0 fw-bold text-dark fs-13 text-truncate">{{ $lu->name }}</p>
                                        <span class="badge badge-subtle-primary fs-9 px-1">{{ ucfirst($lu->role ?? 'User') }}</span>
                                    </div>
                                    <p class="mb-0 text-muted fs-11 text-truncate">
                                        {{ $lu->details->society->name ?? ($lu->Society->name ?? 'Standalone') }}
                                        @if(!empty($lu->details->flat->flat_no)) • Flat {{ $lu->details->flat->flat_no }} @endif
                                    </p>
                                    @if(!empty($lu->last_active_endpoint))
                                        <code class="text-primary fs-10">{{ \Illuminate\Support\Str::limit($lu->last_active_endpoint, 30) }}</code>
                                    @endif
                                </div>
                            </div>
                            <div class="text-end ms-2 flex-shrink-0">
                                <span class="badge bg-success-subtle text-success fs-10 fw-semibold d-block mb-1">● Online</span>
                                <span class="text-muted fs-10">{{ $lu->last_seen_at ? $lu->last_seen_at->diffForHumans() : 'Just now' }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="p-5 text-center text-muted fs-13" id="noUsersPagePlaceholder">
                            <i data-feather="users" class="icon-lg mb-2 text-muted"></i>
                            <p class="mb-0 fw-semibold text-dark">No Active Devices Detected</p>
                            <p class="fs-12 text-muted mb-0">Open your mobile app or load a screen to see your device live here.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT PANEL: LIVE API TRAFFIC STREAM (7 COLS) --}}
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3 px-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary-subtle text-primary rounded p-1"><i data-feather="zap" class="icon-xs"></i></div>
                        <h5 class="card-title fw-bold mb-0 fs-14 text-dark">Live API Request Stream</h5>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1 fs-11 fw-bold">Live Stream</span>
                    </div>
                    <span class="text-muted fs-11"><i data-feather="radio" class="icon-xxs me-1 text-danger"></i>Listening for Hits</span>
                </div>
                
                <div class="table-responsive" style="max-height: 580px; overflow-y: auto;">
                    <table class="table table-hover table-centered table-nowrap mb-0" id="trackingActivityTable">
                        <thead class="bg-light fs-11 fw-bold text-muted text-uppercase sticky-top">
                            <tr>
                                <th class="ps-3">Method & Route</th>
                                <th>User / Caller</th>
                                <th>Platform</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Speed & Time</th>
                            </tr>
                        </thead>
                        <tbody id="trackingActivityTbody">
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
                                        <code class="text-dark fs-11 fw-semibold">{{ \Illuminate\Support\Str::limit($act->endpoint, 28) }}</code>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark fs-12 d-block">{{ $act->user->name ?? ($act->user_name ?: ('Mobile User #' . ($act->user_id ?? 'Guest'))) }}</span>
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
                            <tr id="noTrackingActivityRow">
                                <td colspan="5" class="text-center text-muted py-5 fs-12">
                                    <i data-feather="activity" class="icon-md mb-2 text-muted"></i>
                                    <p class="mb-0">Waiting for live requests from the mobile app or web...</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof feather !== 'undefined') feather.replace();

    // Live clock
    const clockEl = document.getElementById('current-tracking-time');
    if (clockEl) {
        const updateClock = () => clockEl.innerText = new Date().toLocaleTimeString();
        updateClock();
        setInterval(updateClock, 1000);
    }

    const liveFeedUrl = "{{ route('admin.live-feed') }}";

    window.refreshTrackingPageNow = function () {
        const icon = document.getElementById('refreshIconSpin');
        if (icon) icon.classList.add('feather-spin');

        fetch(liveFeedUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(data => {
                // Update live count cards and badges
                const mLive = document.getElementById('metricLiveCount');
                if (mLive) mLive.innerText = data.count;

                const badge = document.getElementById('liveUsersPageBadge');
                if (badge) badge.innerText = data.count + ' Connected Now';

                // Update sidebar live counter
                document.querySelectorAll('.sidebar-live-count').forEach(el => {
                    el.innerHTML = '<span class="pulse-dot-admin bg-white me-1" style="width:5px;height:5px;"></span>' + data.count + ' Live';
                });

                // Update users list
                const usersList = document.getElementById('trackingUsersList');
                if (usersList && data.users) {
                    if (data.users.length === 0) {
                        usersList.innerHTML = `
                            <div class="p-5 text-center text-muted fs-13">
                                <i data-feather="users" class="icon-lg mb-2 text-muted"></i>
                                <p class="mb-0 fw-semibold text-dark">No Active Devices Detected</p>
                                <p class="fs-12 text-muted mb-0">Open your mobile app or load a screen to see your device live here.</p>
                            </div>`;
                    } else {
                        let html = '';
                        data.users.forEach(u => {
                            html += `
                            <div class="list-group-item px-3 py-3 border-0 border-bottom d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center min-width-0">
                                    <div class="position-relative me-3 flex-shrink-0">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-13" style="width:38px;height:38px;">
                                            ${(u.name || 'U').charAt(0)}
                                        </div>
                                        <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" style="width:10px;height:10px;"></span>
                                    </div>
                                    <div class="min-width-0">
                                        <div class="d-flex align-items-center gap-1 mb-1">
                                            <p class="mb-0 fw-bold text-dark fs-13 text-truncate">${u.name}</p>
                                            <span class="badge badge-subtle-primary fs-9 px-1">${u.role}</span>
                                        </div>
                                        <p class="mb-0 text-muted fs-11 text-truncate">${u.society} ${u.flat ? '• Flat ' + u.flat : ''}</p>
                                        ${u.last_endpoint ? `<code class="text-primary fs-10">${u.last_endpoint.length > 30 ? u.last_endpoint.substring(0, 30) + '...' : u.last_endpoint}</code>` : ''}
                                    </div>
                                </div>
                                <div class="text-end ms-2 flex-shrink-0">
                                    <span class="badge bg-success-subtle text-success fs-10 fw-semibold d-block mb-1">● Online</span>
                                    <span class="text-muted fs-10">${u.last_seen}</span>
                                </div>
                            </div>`;
                        });
                        usersList.innerHTML = html;
                    }
                }

                // Update activity stream table
                const actTbody = document.getElementById('trackingActivityTbody');
                if (actTbody && data.activities) {
                    if (data.activities.length === 0) {
                        actTbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-5 fs-12"><p class="mb-0">Waiting for live requests from the mobile app or web...</p></td></tr>`;
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
                                        <code class="text-dark fs-11 fw-semibold">${epShort}</code>
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
                if (icon) icon.classList.remove('feather-spin');
            })
            .catch(err => {
                console.debug('Tracking auto-sync error:', err);
                if (icon) icon.classList.remove('feather-spin');
            });
    };

    // Auto-refresh every 5 seconds for real-time monitoring
    setInterval(window.refreshTrackingPageNow, 5000);
});
</script>
@endpush
@endsection
