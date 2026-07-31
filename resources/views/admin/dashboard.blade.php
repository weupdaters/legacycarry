@extends('admin.layouts.app')
@section('title', 'System Control Hub')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column mb-2">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-bold m-0">System Control Center</h4>
            <p class="text-muted mb-0 fs-13"><span id="live-dot" class="badge bg-success-subtle text-success me-1 fs-10">● LIVE</span>Operational Intelligence • <span id="current-time"></span></p>
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
                        <span class="text-muted fw-bold fs-10 text-uppercase me-1">⚡ Launchpad:</span>
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
            <div class="card border-0 shadow-sm h-100">
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
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-2">
                    <div id="{{ $c['id'] }}" style="min-height:140px;"></div>
                    <p class="text-muted fw-bold mb-0 fs-11 text-uppercase">{{ $c['label'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
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
                            <tr><td colspan="5" class="text-center text-muted py-3 fs-13">No transactions yet</td></tr>
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
});
</script>
@endpush
@endsection
