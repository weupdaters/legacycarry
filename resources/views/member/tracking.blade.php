@extends('member.layouts.app')
@section('title', 'Society Live Device & Resident Tracking')

@section('content')
<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-soft-success text-success font-12 fw-bold px-3 py-1 rounded-pill">
                    <span class="pulse-dot bg-success me-1"></span> LIVE TELEMETRY
                </span>
                <h3 class="fw-bold m-0 text-slate-900">Society Resident & App Activity</h3>
            </div>
            <p class="text-muted font-13 mb-0">Live monitoring of residents using the mobile application and portal for <strong>{{ $society->name ?? 'Society' }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('member.dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
                <i class="mdi mdi-arrow-left me-1"></i> Dashboard
            </a>
            <button class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" onclick="refreshMemberTrackingNow()">
                <i class="mdi mdi-refresh me-1" id="mRefreshSpin"></i> Sync
            </button>
        </div>
    </div>

    {{-- LIVE STATS ROW --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-sm-6">
            <div class="stat-card">
                <div class="card-top-bar" style="background: #10b981;"></div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon bg-soft-success">
                        <i class="mdi mdi-cellphone-link"></i>
                    </div>
                    <span class="badge badge-subtle-success rounded-pill px-2 py-1 font-11 fw-bold">ONLINE</span>
                </div>
                <h3 class="fw-bold mb-1 text-slate-900" id="mPageLiveCount">{{ $liveResidentsCount }}</h3>
                <p class="text-muted mb-0 font-13 fw-semibold">Residents Active Right Now</p>
                <span class="text-success font-11 fw-bold"><i class="mdi mdi-check-circle"></i> Connected in last 5 mins</span>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="stat-card">
                <div class="card-top-bar" style="background: #3b82f6;"></div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon bg-soft-primary">
                        <i class="mdi mdi-server-network"></i>
                    </div>
                    <span class="badge bg-soft-primary text-primary rounded-pill px-2 py-1 font-11 fw-bold">ACTIVITY</span>
                </div>
                <h3 class="fw-bold mb-1 text-slate-900">{{ count($recentApiActivities) }}</h3>
                <p class="text-muted mb-0 font-13 fw-semibold">Recent Requests Captured</p>
                <span class="text-muted font-11">Real-time mobile API traffic</span>
            </div>
        </div>

        <div class="col-md-4 col-sm-12">
            <div class="stat-card">
                <div class="card-top-bar" style="background: #0284c7;"></div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon bg-soft-info">
                        <i class="mdi mdi-shield-check"></i>
                    </div>
                    <span class="badge bg-soft-info text-info rounded-pill px-2 py-1 font-11 fw-bold">TELEMETRY</span>
                </div>
                <h3 class="fw-bold mb-1 text-slate-900">0% <span class="font-14 fw-normal text-muted">App Setup</span></h3>
                <p class="text-muted mb-0 font-13 fw-semibold">Backend Smart Interception</p>
                <span class="text-info font-11 fw-bold">Zero mobile updates needed</span>
            </div>
        </div>
    </div>

    {{-- SPLIT PANELS --}}
    <div class="row g-4 mb-4">
        {{-- CONNECTED RESIDENTS --}}
        <div class="col-lg-5">
            <div class="glass-table-card h-100 mb-0">
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white">
                    <div>
                        <h5 class="fw-bold m-0 text-slate-800 d-flex align-items-center gap-2">
                            <span class="pulse-dot bg-success"></span> Connected Residents
                        </h5>
                        <p class="text-muted font-12 mb-0 mt-1">Live in society mobile application</p>
                    </div>
                    <span class="badge badge-subtle-success rounded-pill px-3 py-1 font-12 fw-bold" id="mResidentBadge">
                        {{ $liveResidentsCount }} Online Now
                    </span>
                </div>
                <div class="p-0">
                    <div class="list-group list-group-flush" id="mResidentLiveList" style="max-height: 540px; overflow-y: auto;">
                        @forelse($liveResidents as $lr)
                        <div class="list-group-item px-4 py-3 border-0 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center min-width-0">
                                <div class="position-relative me-3 flex-shrink-0">
                                    <div class="rounded-circle bg-soft-primary text-primary d-flex align-items-center justify-content-center fw-bold font-14" style="width:42px;height:42px;">
                                        {{ substr($lr->name ?? 'R', 0, 1) }}
                                    </div>
                                    <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" style="width:10px;height:10px;"></span>
                                </div>
                                <div class="min-width-0">
                                    <h6 class="mb-0 fw-bold text-dark font-13 text-truncate">{{ $lr->name }}</h6>
                                    <span class="text-muted font-11">
                                        @if(!empty($lr->details->flat->flat_no)) Flat {{ $lr->details->flat->flat_no }} • @endif
                                        {{ $lr->phone ? Str::mask($lr->phone, '*', 3, 4) : 'Resident' }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-end ms-2 flex-shrink-0">
                                <span class="badge badge-subtle-success font-10 fw-bold px-2 py-1 d-block mb-1">● Active</span>
                                <span class="text-muted font-10">{{ $lr->last_seen_at ? $lr->last_seen_at->diffForHumans() : 'Just now' }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="p-5 text-center text-muted font-13">
                            <i class="mdi mdi-account-clock text-muted" style="font-size: 42px;"></i>
                            <p class="mb-0 mt-2 fw-semibold">No residents active in the last 5 minutes.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- ACTIVITY STREAM --}}
        <div class="col-lg-7">
            <div class="glass-table-card h-100 mb-0">
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white">
                    <div>
                        <h5 class="fw-bold m-0 text-slate-800 d-flex align-items-center gap-2">
                            <i class="mdi mdi-broadcast text-primary"></i> Society Live API Activity Stream
                        </h5>
                        <p class="text-muted font-12 mb-0 mt-1">Real-time actions taken by residents</p>
                    </div>
                    <span class="badge bg-soft-info text-info rounded-pill px-3 py-1 font-11 fw-bold">Live Synced</span>
                </div>
                <div class="table-responsive" style="max-height: 540px; overflow-y: auto;">
                    <table class="table table-hover table-centered table-nowrap mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Request</th>
                                <th>Resident</th>
                                <th>Device</th>
                                <th class="text-end pe-4">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody id="mResidentActivityTbody">
                            @forelse($recentApiActivities as $act)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-soft-primary text-primary fw-bold font-10 px-2 py-1">{{ $act->method }}</span>
                                        <span class="fw-semibold text-dark font-12">{{ \Illuminate\Support\Str::limit($act->endpoint, 28) }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark font-12 d-block">{{ $act->user->name ?? ($act->user_name ?: ('Resident #' . $act->user_id)) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-soft-success text-success font-11 px-2 py-1">
                                        <i class="mdi mdi-cellphone me-1"></i> {{ $act->device_type ?? 'Mobile App' }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="text-muted font-11">{{ $act->created_at ? $act->created_at->diffForHumans() : 'Just now' }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-5 font-13">No resident app activity recorded yet.</td></tr>
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
    const liveFeedUrl = "{{ route('member.live-feed') }}";

    window.refreshMemberTrackingNow = function () {
        const icon = document.getElementById('mRefreshSpin');
        if (icon) icon.classList.add('mdi-spin');

        fetch(liveFeedUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(data => {
                const countBadge = document.getElementById('mPageLiveCount');
                if (countBadge) countBadge.innerText = data.count;

                const rBadge = document.getElementById('mResidentBadge');
                if (rBadge) rBadge.innerText = data.count + ' Online Now';

                // Update resident list
                const residentList = document.getElementById('mResidentLiveList');
                if (residentList && data.users) {
                    if (data.users.length === 0) {
                        residentList.innerHTML = `<div class="p-5 text-center text-muted font-13"><i class="mdi mdi-account-clock text-muted" style="font-size: 42px;"></i><p class="mb-0 mt-2 fw-semibold">No residents active in the last 5 minutes.</p></div>`;
                    } else {
                        let html = '';
                        data.users.forEach(u => {
                            html += `
                            <div class="list-group-item px-4 py-3 border-0 border-bottom d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center min-width-0">
                                    <div class="position-relative me-3 flex-shrink-0">
                                        <div class="rounded-circle bg-soft-primary text-primary d-flex align-items-center justify-content-center fw-bold font-14" style="width:42px;height:42px;">
                                            ${(u.name || 'R').charAt(0)}
                                        </div>
                                        <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" style="width:10px;height:10px;"></span>
                                    </div>
                                    <div class="min-width-0">
                                        <h6 class="mb-0 fw-bold text-dark font-13 text-truncate">${u.name}</h6>
                                        <span class="text-muted font-11">${u.flat ? 'Flat ' + u.flat + ' • ' : ''}Resident</span>
                                    </div>
                                </div>
                                <div class="text-end ms-2 flex-shrink-0">
                                    <span class="badge badge-subtle-success font-10 fw-bold px-2 py-1 d-block mb-1">● Active</span>
                                    <span class="text-muted font-10">${u.last_seen}</span>
                                </div>
                            </div>`;
                        });
                        residentList.innerHTML = html;
                    }
                }

                // Update activity stream
                const actTbody = document.getElementById('mResidentActivityTbody');
                if (actTbody && data.activities) {
                    if (data.activities.length === 0) {
                        actTbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-5 font-13">No resident app activity recorded yet.</td></tr>`;
                    } else {
                        let rows = '';
                        data.activities.forEach(a => {
                            const epShort = a.endpoint.length > 28 ? a.endpoint.substring(0, 28) + '...' : a.endpoint;
                            rows += `
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-soft-primary text-primary fw-bold font-10 px-2 py-1">${a.method}</span>
                                        <span class="fw-semibold text-dark font-12">${epShort}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark font-12 d-block">${a.user_name}</span>
                                </td>
                                <td>
                                    <span class="badge bg-soft-success text-success font-11 px-2 py-1">
                                        <i class="mdi mdi-cellphone me-1"></i> ${a.device_type || 'Mobile App'}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="text-muted font-11">${a.time_ago}</span>
                                </td>
                            </tr>`;
                        });
                        actTbody.innerHTML = rows;
                    }
                }
                if (icon) icon.classList.remove('mdi-spin');
            })
            .catch(err => {
                console.debug('Live sync error:', err);
                if (icon) icon.classList.remove('mdi-spin');
            });
    };

    // Auto-refresh every 5 seconds
    setInterval(window.refreshMemberTrackingNow, 5000);
});
</script>
@endpush
@endsection
