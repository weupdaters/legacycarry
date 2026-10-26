@extends('member.layouts.app')

@section('title', 'Society Dashboard')

@section('content')
  @php
    if (!isset($society)) {
        $assignment = \App\Models\ResidentAssignment::where('user_id', auth()->id())->first();
        $society = $assignment ? \App\Models\Society::find($assignment->society_id) : null;
    }
    $hasAnyPermission = has_permission('resident_index') || has_permission('guard_index') || has_permission('pass_history_index') || has_permission('notice_index') || has_permission('amenity_index') || has_permission('poll_index') || has_permission('ticket_index');
  @endphp
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap');

    body {
      font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
      background-color: #f8fafc;
    }

    .welcome-card {
      background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #3b82f6 100%);
      border-radius: 22px;
      padding: 38px 40px;
      color: white;
      position: relative;
      overflow: hidden;
      box-shadow: 0 12px 30px rgba(37, 99, 235, 0.22);
    }

    .welcome-card::after {
      content: '';
      position: absolute;
      top: -60px;
      right: -60px;
      width: 240px;
      height: 240px;
      background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
      border-radius: 50%;
      pointer-events: none;
    }

    .welcome-card::before {
      content: '';
      position: absolute;
      bottom: -40px;
      left: 30%;
      width: 180px;
      height: 180px;
      background: rgba(255, 255, 255, 0.06);
      border-radius: 50%;
      pointer-events: none;
    }

    .stat-card {
      border: 1px solid rgba(226, 232, 240, 0.85);
      border-radius: 20px;
      padding: 22px;
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
      background: #ffffff;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      height: 100%;
    }

    .stat-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 18px 32px rgba(15, 23, 42, 0.09);
      border-color: #cbd5e1;
    }

    .stat-card .card-top-bar {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: transparent;
      transition: all 0.3s ease;
    }

    .stat-card:hover .card-top-bar {
      opacity: 1;
    }

    .stat-icon {
      width: 52px;
      height: 52px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      transition: transform 0.3s ease;
    }

    .stat-card:hover .stat-icon {
      transform: scale(1.08) rotate(2deg);
    }

    .bg-soft-primary { background: linear-gradient(135deg, #e0e7ff 0%, #ede9fe 100%); color: #4338ca; }
    .bg-soft-success { background: linear-gradient(135deg, #dcfce7 0%, #d1fae5 100%); color: #047857; }
    .bg-soft-info { background: linear-gradient(135deg, #e0f2fe 0%, #e0e7ff 100%); color: #0284c7; }
    .bg-soft-warning { background: linear-gradient(135deg, #fef3c7 0%, #ffedd5 100%); color: #b45309; }
    .bg-soft-purple { background: linear-gradient(135deg, #f3e8ff 0%, #fae8ff 100%); color: #7e22ce; }
    .bg-soft-danger { background: linear-gradient(135deg, #fee2e2 0%, #ffe4e6 100%); color: #b91c1c; }

    .pulse-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      display: inline-block;
      animation: pulseAnim 2s infinite;
    }

    @keyframes pulseAnim {
      0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
      70% { transform: scale(1.1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
      100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    .pulse-dot-amber {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      display: inline-block;
      animation: pulseAmber 2s infinite;
    }

    @keyframes pulseAmber {
      0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); }
      70% { transform: scale(1.1); box-shadow: 0 0 0 6px rgba(245, 158, 11, 0); }
      100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
    }

    .glass-table-card {
      background: white;
      border-radius: 20px;
      border: 1px solid rgba(226, 232, 240, 0.85);
      box-shadow: 0 4px 18px rgba(0,0,0,0.03);
      overflow: hidden;
      margin-bottom: 24px;
    }

    .table thead th {
      background-color: #f8fafc;
      text-transform: uppercase;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.05em;
      color: #64748b;
      border-bottom: 1px solid #f1f5f9;
      padding: 14px 20px;
    }

    .table tbody td {
      padding: 16px 20px;
      vertical-align: middle;
      font-size: 13.5px;
      color: #334155;
      border-bottom: 1px solid #f1f5f9;
    }

    .society-badge {
      background: rgba(255, 255, 255, 0.18);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255, 255, 255, 0.25);
      padding: 7px 16px;
      border-radius: 50px;
      font-weight: 600;
      font-size: 13px;
      color: #ffffff;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .avatar-ring {
      padding: 2.5px;
      background: linear-gradient(135deg, #3b82f6, #10b981);
      border-radius: 50%;
      display: inline-block;
    }

    .quick-link {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      padding: 14px 10px;
      border-radius: 16px;
      text-align: center;
      color: #475569;
      text-decoration: none;
      transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
      display: block;
      font-weight: 600;
      font-size: 12.5px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }

    .quick-link:hover {
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      color: white !important;
      border-color: #2563eb;
      transform: translateY(-3px);
      box-shadow: 0 8px 18px rgba(37, 99, 235, 0.25);
    }

    .quick-link:hover i {
      color: white !important;
      transform: scale(1.1);
    }

    .quick-link i {
      transition: transform 0.25s ease;
    }

    .interactive-card {
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      background: #ffffff;
      transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .interactive-card:hover {
      border-color: #93c5fd;
      transform: translateY(-3px);
      box-shadow: 0 10px 20px rgba(37, 99, 235, 0.08);
    }

    .badge-subtle-primary { background: #e0e7ff; color: #4338ca; }
    .badge-subtle-success { background: #dcfce7; color: #15803d; }
    .badge-subtle-warning { background: #fef3c7; color: #92400e; }
    .badge-subtle-danger { background: #fee2e2; color: #b91c1c; }
    .badge-subtle-info { background: #e0f2fe; color: #0369a1; }
    .badge-subtle-purple { background: #f3e8ff; color: #7e22ce; }

    .nav-tabs-clean {
      border-bottom: 2px solid #f1f5f9;
      gap: 8px;
    }
    .nav-tabs-clean .nav-link {
      border: none;
      font-weight: 600;
      font-size: 13px;
      color: #64748b;
      padding: 10px 18px;
      border-radius: 10px;
      transition: all 0.2s ease;
    }
    .nav-tabs-clean .nav-link.active {
      color: #2563eb;
      background: #eff6ff;
    }
  </style>

  <div class="container-fluid py-4">

    {{-- WELCOME BANNER --}}
    <div class="row mb-4">
      <div class="col-12">
        <div class="welcome-card">
          <div class="row align-items-center">
            <div class="col-md-8">
              <span class="badge bg-white bg-opacity-25 text-white px-3 py-1 rounded-pill mb-2 fw-semibold fs-12">
                <i class="mdi mdi-shield-crown-outline me-1"></i> Society Administration Portal
              </span>
              <h1 class="fw-bold mb-2">Welcome back, {{ Auth::user()->name }}! 👋</h1>
              <p class="fs-15 text-white-50 mb-3">Live operational pulse for <strong>{{ $society->name ?? 'Your Society' }}</strong>. Manage amenities, active polls, complaints, and residents in real time.</p>
              <div class="d-flex flex-wrap gap-2">
                <span class="society-badge">
                  <i class="mdi mdi-office-building"></i> {{ $society->name ?? 'Standalone Society' }}
                </span>
                @if($society && $society->address)
                <span class="society-badge">
                  <i class="mdi mdi-map-marker"></i> {{ Str::limit($society->address, 35) }}
                </span>
                @endif
                <span class="society-badge">
                  <span class="pulse-dot bg-success me-1"></span> Live Operations
                </span>
              </div>
            </div>
            <div class="col-md-4 d-none d-md-block text-end">
              @if($society && $society->profile_image)
                <img src="{{ asset('storage/' . $society->profile_image) }}" class="rounded-4 shadow-lg" style="height: 120px; width: 120px; object-fit: cover; border: 4px solid rgba(255,255,255,0.3);">
              @else
                <div class="d-inline-flex align-items-center justify-content-center bg-white bg-opacity-10 rounded-4 p-4 border border-white border-opacity-25" style="width: 120px; height: 120px;">
                  <i class="mdi mdi-home-city-outline text-white" style="font-size: 54px;"></i>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>

    @if($hasAnyPermission)
    {{-- STATS ROW --}}
    <div class="row g-3 mb-4">
      {{-- 1. RESIDENTS --}}
      <div class="col-xl-3 col-md-6 col-sm-6">
        <div class="stat-card">
          <div class="card-top-bar" style="background: #3b82f6;"></div>
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="stat-icon bg-soft-primary">
              <i class="mdi mdi-account-group"></i>
            </div>
            @if(has_permission('resident_index'))
              <a href="{{ route('member.residents.index') }}" class="btn btn-xs btn-outline-primary rounded-pill px-3 fw-bold" style="font-size: 11px;">View All →</a>
            @endif
          </div>
          <div>
            <h3 class="fw-bold mb-1 text-slate-900">{{ number_format($totalEmployees) }}</h3>
            <p class="text-muted mb-0 font-13 fw-semibold">Total Residents</p>
            <span class="text-success font-11 fw-bold"><i class="mdi mdi-check-circle-outline"></i> Active Community Members</span>
          </div>
        </div>
      </div>

      {{-- 2. AMENITIES & BOOKINGS --}}
      <div class="col-xl-3 col-md-6 col-sm-6">
        <div class="stat-card">
          <div class="card-top-bar" style="background: #06b6d4;"></div>
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="stat-icon bg-soft-info">
              <i class="mdi mdi-pool"></i>
            </div>
            <div class="dropdown">
              <a href="{{ route('member.amenities.index') }}" class="btn btn-xs btn-outline-info rounded-pill px-3 fw-bold" style="font-size: 11px;">Facilities →</a>
            </div>
          </div>
          <div>
            <div class="d-flex align-items-baseline gap-2">
              <h3 class="fw-bold mb-1 text-slate-900">{{ number_format($totalAmenities ?? 0) }}</h3>
              @if(($pendingAmenityBookings ?? 0) > 0)
                <span class="badge badge-subtle-warning rounded-pill px-2 py-1 font-11 fw-bold">
                  <span class="pulse-dot-amber me-1"></span> {{ $pendingAmenityBookings }} Pending
                </span>
              @endif
            </div>
            <p class="text-muted mb-0 font-13 fw-semibold">Society Amenities</p>
            <span class="text-muted font-11"><i class="mdi mdi-calendar-check me-1"></i> {{ $totalAmenityBookings ?? 0 }} Total Bookings</span>
          </div>
        </div>
      </div>

      {{-- 3. POLLS & COMMUNITY VOTING --}}
      <div class="col-xl-3 col-md-6 col-sm-6">
        <div class="stat-card">
          <div class="card-top-bar" style="background: #8b5cf6;"></div>
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="stat-icon bg-soft-purple">
              <i class="mdi mdi-vote-outline"></i>
            </div>
            <a href="{{ route('member.polls.index') }}" class="btn btn-xs btn-outline-purple rounded-pill px-3 fw-bold" style="font-size: 11px; color:#7e22ce; border-color:#d8b4fe;">Polls Hub →</a>
          </div>
          <div>
            <div class="d-flex align-items-baseline gap-2">
              <h3 class="fw-bold mb-1 text-slate-900">{{ number_format($activePollsCount ?? 0) }}</h3>
              <span class="badge badge-subtle-success rounded-pill px-2 py-1 font-11 fw-bold">
                <span class="pulse-dot me-1"></span> Active
              </span>
            </div>
            <p class="text-muted mb-0 font-13 fw-semibold">Community Polls</p>
            <span class="text-purple font-11 fw-bold" style="color: #7e22ce;"><i class="mdi mdi-chart-box-outline me-1"></i> {{ number_format($totalPollVotes ?? 0) }} Votes Submitted</span>
          </div>
        </div>
      </div>

      {{-- 4. TICKETS / COMPLAINTS / REPORTS --}}
      <div class="col-xl-3 col-md-6 col-sm-6">
        <div class="stat-card">
          <div class="card-top-bar" style="background: #f43f5e;"></div>
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="stat-icon bg-soft-danger">
              <i class="mdi mdi-alert-octagon-outline"></i>
            </div>
            <a href="{{ route('member.tickets.index') }}" class="btn btn-xs btn-outline-danger rounded-pill px-3 fw-bold" style="font-size: 11px;">Tickets →</a>
          </div>
          <div>
            <div class="d-flex align-items-baseline gap-2">
              <h3 class="fw-bold mb-1 text-slate-900">{{ number_format($pendingTickets ?? 0) }}</h3>
              <span class="badge badge-subtle-danger rounded-pill px-2 py-1 font-11 fw-bold">Open</span>
            </div>
            <p class="text-muted mb-0 font-13 fw-semibold">Complaints & Tickets</p>
            <span class="text-success font-11 fw-bold"><i class="mdi mdi-check-all me-1"></i> {{ number_format($resolvedTickets ?? 0) }} Resolved</span>
          </div>
        </div>
      </div>
    </div>
    @endif

    {{-- REAL-TIME LIVE RESIDENTS & APP ACTIVITY --}}
    <div class="row g-4 mb-4">
      <div class="col-lg-5">
        <div class="glass-table-card h-100 mb-0">
          <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white">
            <div>
              <h5 class="fw-bold m-0 text-slate-800 d-flex align-items-center gap-2">
                <span class="pulse-dot bg-success"></span> Live Residents Online
              </h5>
              <p class="text-muted font-12 mb-0 mt-1">Residents active in the mobile app / portal now</p>
            </div>
            <span class="badge badge-subtle-success rounded-pill px-3 py-1 font-12 fw-bold" id="residentLiveBadge">
              {{ $liveResidentsCount ?? 0 }} Online Now
            </span>
          </div>
          <div class="p-0">
            <div class="list-group list-group-flush" id="residentLiveList" style="max-height: 360px; overflow-y: auto;">
              @forelse($liveResidents ?? [] as $lr)
              <div class="list-group-item px-4 py-3 border-0 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center min-width-0">
                  <div class="position-relative me-3 flex-shrink-0">
                    <div class="rounded-circle bg-soft-primary text-primary d-flex align-items-center justify-content-center fw-bold font-14" style="width:40px;height:40px;">
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
              <div class="p-4 text-center text-muted font-13" id="noLiveResidentsPlaceholder">
                <i class="mdi mdi-account-clock text-muted" style="font-size: 36px;"></i>
                <p class="mb-0 mt-2">No residents currently active in the last 5 minutes.</p>
              </div>
              @endforelse
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-7">
        <div class="glass-table-card h-100 mb-0">
          <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white">
            <div>
              <h5 class="fw-bold m-0 text-slate-800 d-flex align-items-center gap-2">
                <i class="mdi mdi-broadcast text-primary"></i> Society Live App Activity Stream
              </h5>
              <p class="text-muted font-12 mb-0 mt-1">Real-time API requests from residents using the mobile application</p>
            </div>
            <span class="badge bg-soft-info text-info rounded-pill px-3 py-1 font-11 fw-bold">Live Synced</span>
          </div>
          <div class="table-responsive" style="max-height: 360px; overflow-y: auto;">
            <table class="table table-hover table-centered table-nowrap mb-0">
              <thead>
                <tr>
                  <th class="ps-4">Request / Action</th>
                  <th>Resident</th>
                  <th>Platform</th>
                  <th class="text-end pe-4">Timestamp</th>
                </tr>
              </thead>
              <tbody id="residentActivityTbody">
                @forelse($recentApiActivities ?? [] as $act)
                <tr>
                  <td class="ps-4">
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge bg-soft-primary text-primary fw-bold font-10 px-2 py-1">{{ $act->method }}</span>
                      <span class="fw-semibold text-dark font-12">{{ \Illuminate\Support\Str::limit($act->endpoint, 25) }}</span>
                    </div>
                  </td>
                  <td>
                    <span class="fw-bold text-dark font-12 d-block">{{ $act->user->name ?? 'Resident #' . $act->user_id }}</span>
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
                <tr><td colspan="4" class="text-center text-muted py-4 font-13">No resident app activity recorded yet. Waiting for live events...</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    {{-- INTERACTIVE WIDGETS SECTION: POLLS & AMENITIES --}}
    <div class="row g-4 mb-4">
      
      {{-- ACTIVE POLLS SHOWCASE --}}
      <div class="col-lg-6">
        <div class="glass-table-card h-100 mb-0">
          <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white">
            <div>
              <h5 class="fw-bold m-0 text-slate-800 d-flex align-items-center gap-2">
                <i class="mdi mdi-vote text-primary"></i> Active Community Polls
              </h5>
              <p class="text-muted font-12 mb-0 mt-1">Live polls currently open for resident decision-making</p>
            </div>
            <div class="d-flex gap-2">
              <a href="{{ route('member.polls.create') }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold">
                <i class="mdi mdi-plus"></i> New Poll
              </a>
              <a href="{{ route('member.polls.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                All Polls
              </a>
            </div>
          </div>

          <div class="p-3">
            @if(isset($activePolls) && $activePolls->count() > 0)
              <div class="row g-3">
                @foreach($activePolls as $poll)
                  @php
                    $isVotingOpen = $poll->isVotingOpen();
                    $hasVoted = $poll->hasUserVoted(Auth::id());
                  @endphp
                  <div class="col-12">
                    <div class="interactive-card p-3">
                      <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="me-2">
                          <h6 class="fw-bold text-dark mb-1">
                            <a href="{{ route('member.polls.show', $poll->id) }}" class="text-dark text-decoration-none hover-primary">
                              {{ $poll->title }}
                            </a>
                          </h6>
                          <p class="text-muted font-12 mb-0 line-clamp-2">{{ Str::limit($poll->description, 80) }}</p>
                        </div>
                        <div>
                          @if($isVotingOpen)
                            <span class="badge badge-subtle-success rounded-pill px-2 py-1 font-11 fw-bold">
                              <span class="pulse-dot me-1"></span> Open
                            </span>
                          @else
                            <span class="badge badge-subtle-secondary rounded-pill px-2 py-1 font-11 fw-bold">Closed</span>
                          @endif
                        </div>
                      </div>

                      <div class="d-flex align-items-center justify-content-between mt-3 pt-2 border-top">
                        <div class="d-flex align-items-center gap-3 font-12 text-muted">
                          <span><i class="mdi mdi-ballot-outline text-primary me-1"></i> {{ $poll->options->count() }} Choices</span>
                          <span><i class="mdi mdi-account-check-outline text-success me-1"></i> {{ $poll->votes_count }} Votes</span>
                          <span><i class="mdi mdi-clock-outline me-1"></i> Ends {{ $poll->end_date ? $poll->end_date->format('M d') : 'N/A' }}</span>
                        </div>
                        <div class="d-flex gap-2">
                          <a href="{{ route('member.polls.results', $poll->id) }}" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1 font-11 fw-semibold">
                            <i class="mdi mdi-chart-pie"></i> Results
                          </a>
                          @if($isVotingOpen)
                            <a href="{{ route('member.polls.show', $poll->id) }}" class="btn btn-xs {{ $hasVoted ? 'btn-soft-primary' : 'btn-primary text-white' }} rounded-pill px-3 py-1 font-11 fw-bold">
                              <i class="mdi mdi-vote me-1"></i> {{ $hasVoted ? 'Vote Again' : 'Vote Now' }}
                            </a>
                          @endif
                        </div>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            @else
              <div class="text-center py-5">
                <div class="bg-soft-purple rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                  <i class="mdi mdi-ballot-recount-outline mdi-24px text-purple" style="color: #7e22ce;"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">No Active Polls</h6>
                <p class="text-muted font-13 mb-3">Engage society residents by creating a new decision poll today.</p>
                <a href="{{ route('member.polls.create') }}" class="btn btn-sm btn-primary rounded-pill px-4">
                  <i class="mdi mdi-plus-circle me-1"></i> Create First Poll
                </a>
              </div>
            @endif
          </div>
        </div>
      </div>

      {{-- AMENITY BOOKINGS TRACKER --}}
      <div class="col-lg-6">
        <div class="glass-table-card h-100 mb-0">
          <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white">
            <div>
              <h5 class="fw-bold m-0 text-slate-800 d-flex align-items-center gap-2">
                <i class="mdi mdi-calendar-clock text-info"></i> Amenity Reservations
              </h5>
              <p class="text-muted font-12 mb-0 mt-1">Clubhouse, swimming pool, sports court and halls schedule</p>
            </div>
            <div class="d-flex gap-2">
              <a href="{{ route('member.amenities.index') }}" class="btn btn-sm btn-info text-white rounded-pill px-3 fw-semibold">
                <i class="mdi mdi-plus"></i> Book Amenity
              </a>
              <a href="{{ route('member.amenities.bookings.manage') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                Manage
              </a>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Amenity / Facility</th>
                  <th>Resident</th>
                  <th>Schedule</th>
                  <th>Status</th>
                  <th class="text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($recentAmenityBookings as $booking)
                  <tr>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="bg-soft-info rounded-3 p-2 me-2 text-info">
                          <i class="mdi mdi-pool font-18"></i>
                        </div>
                        <div>
                          <span class="fw-bold text-dark d-block font-13">{{ $booking->amenity->name ?? 'Facility' }}</span>
                          <span class="text-muted font-11">{{ ucfirst($booking->amenity->booking_mode ?? 'Slot') }}</span>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="fw-semibold text-slate-700 font-13 d-block">{{ $booking->user->name ?? 'Resident' }}</span>
                      <span class="text-muted font-11">{{ $booking->user->details->flat->flat_number ?? 'Flat —' }}</span>
                    </td>
                    <td>
                      <span class="font-12 fw-semibold d-block">{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') : '—' }}</span>
                      <span class="text-muted font-11">{{ $booking->start_time ?? '' }} - {{ $booking->end_time ?? '' }}</span>
                    </td>
                    <td>
                      @if($booking->status === 'confirmed' || $booking->status === 'approved')
                        <span class="badge badge-subtle-success rounded-pill px-2 py-1 font-11 fw-bold">Confirmed</span>
                      @elseif($booking->status === 'pending')
                        <span class="badge badge-subtle-warning rounded-pill px-2 py-1 font-11 fw-bold">Pending</span>
                      @elseif($booking->status === 'rejected')
                        <span class="badge badge-subtle-danger rounded-pill px-2 py-1 font-11 fw-bold">Rejected</span>
                      @else
                        <span class="badge badge-subtle-secondary rounded-pill px-2 py-1 font-11 fw-bold">{{ ucfirst($booking->status) }}</span>
                      @endif
                    </td>
                    <td class="text-end">
                      <a href="{{ route('member.amenities.bookings.manage') }}" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-1 font-11 fw-semibold">
                        View
                      </a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                      <div class="bg-soft-info rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 54px; height: 54px;">
                        <i class="mdi mdi-calendar-blank-outline font-22 text-info"></i>
                      </div>
                      <p class="font-13 mb-2">No recent amenity reservations recorded.</p>
                      <a href="{{ route('member.amenities.index') }}" class="btn btn-sm btn-outline-info rounded-pill px-3">
                        Browse Amenities
                      </a>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>

    {{-- SECTION: COMPLAINTS & SOCIETY REPORTS --}}
    <div class="row mb-4">
      <div class="col-12">
        <div class="glass-table-card mb-0">
          <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white flex-wrap gap-2">
            <div>
              <h5 class="fw-bold m-0 text-slate-800 d-flex align-items-center gap-2">
                <i class="mdi mdi-ticket-confirmation-outline text-danger"></i> Society Complaints & Support Reports
              </h5>
              <p class="text-muted font-12 mb-0 mt-1">Real-time resident grievances, maintenance requests, and issue resolution tracking</p>
            </div>
            <div class="d-flex gap-2">
              <a href="{{ route('member.tickets.create') }}" class="btn btn-sm btn-danger rounded-pill px-3 fw-semibold">
                <i class="mdi mdi-plus"></i> Raise New Ticket
              </a>
              <a href="{{ route('member.tickets.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                All Tickets
              </a>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Ticket #</th>
                  <th>Resident</th>
                  <th>Category & Subject</th>
                  <th>Priority</th>
                  <th>Status</th>
                  <th>Reported Date</th>
                  <th class="text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($recentTickets as $ticket)
                  <tr>
                    <td>
                      <span class="badge bg-light border text-dark font-12 fw-bold font-monospace">
                        #{{ $ticket->ticket_number ?? $ticket->id }}
                      </span>
                    </td>
                    <td>
                      <span class="fw-semibold text-slate-800 font-13 d-block">{{ $ticket->user->name ?? 'Resident' }}</span>
                      <span class="text-muted font-11">{{ $ticket->user->email ?? '—' }}</span>
                    </td>
                    <td>
                      <span class="fw-bold text-dark font-13 d-block">{{ $ticket->title }}</span>
                      <span class="badge badge-subtle-info rounded-pill px-2 py-0 font-10 text-uppercase fw-bold">{{ str_replace('_', ' ', $ticket->category ?? 'General') }}</span>
                    </td>
                    <td>
                      @if($ticket->priority === 'urgent' || $ticket->priority === 'high')
                        <span class="badge badge-subtle-danger rounded-pill px-2 py-1 font-11 fw-bold">
                          <i class="mdi mdi-arrow-up-bold me-1"></i> {{ ucfirst($ticket->priority) }}
                        </span>
                      @elseif($ticket->priority === 'medium')
                        <span class="badge badge-subtle-warning rounded-pill px-2 py-1 font-11 fw-bold">Medium</span>
                      @else
                        <span class="badge badge-subtle-info rounded-pill px-2 py-1 font-11 fw-bold">Low</span>
                      @endif
                    </td>
                    <td>
                      @if($ticket->status === 'open')
                        <span class="badge badge-subtle-danger rounded-pill px-2 py-1 font-11 fw-bold">
                          <span class="pulse-dot bg-danger me-1"></span> Open
                        </span>
                      @elseif($ticket->status === 'in_progress')
                        <span class="badge badge-subtle-warning rounded-pill px-2 py-1 font-11 fw-bold">In Progress</span>
                      @elseif($ticket->status === 'resolved')
                        <span class="badge badge-subtle-success rounded-pill px-2 py-1 font-11 fw-bold">Resolved</span>
                      @else
                        <span class="badge badge-subtle-secondary rounded-pill px-2 py-1 font-11 fw-bold">{{ ucfirst($ticket->status) }}</span>
                      @endif
                    </td>
                    <td>
                      <span class="text-muted font-12">{{ $ticket->created_at ? $ticket->created_at->format('M d, Y h:i A') : 'N/A' }}</span>
                    </td>
                    <td class="text-end">
                      <a href="{{ route('member.tickets.show', $ticket->id) }}" class="btn btn-xs btn-primary rounded-pill px-3 py-1 font-11 fw-bold">
                        Inspect →
                      </a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                      <div class="bg-soft-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 54px; height: 54px;">
                        <i class="mdi mdi-check-circle-outline font-24 text-success"></i>
                      </div>
                      <p class="font-14 mb-0 fw-semibold text-dark">No Pending Grievances or Complaints</p>
                      <p class="text-muted font-12 mb-3">All issues reported by society residents have been resolved!</p>
                      <a href="{{ route('member.tickets.create') }}" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                        <i class="mdi mdi-plus"></i> Submit a Ticket
                      </a>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    {{-- SECTION: RESIDENTS TABLE & QUICK LAUNCH ACTIONS --}}
    <div class="row">
      @if($hasAnyPermission)
      {{-- RESIDENTS TABLE --}}
      <div class="col-lg-8">
        <div class="glass-table-card mb-4">
          <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white">
            <div>
              <h5 class="fw-bold m-0 text-slate-800 d-flex align-items-center gap-2">
                <i class="mdi mdi-account-group-outline text-primary"></i> Society Residents Directory
              </h5>
              <p class="text-muted font-12 mb-0 mt-1">Recently onboarded and verified residents in your society</p>
            </div>
            @if(has_permission('resident_index'))
              <a href="{{ route('member.residents.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">View All</a>
            @endif
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Resident Profile</th>
                  <th>Contact Information</th>
                  <th>Flat / Assignment</th>
                  <th>Joined Date</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($employees as $emp)
                  @php
                    $u = $emp instanceof \App\Models\User ? $emp : ($emp->user ?? $emp);
                    $flatNumber = $u->details?->flat?->flat_number ?? $u->residentAssignment?->flat?->flat_number ?? null;
                  @endphp
                  <tr>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="avatar-ring me-3">
                          <img src="{{ $u->avatar_url ?? asset('assets/images/users/default.png') }}"
                               class="rounded-circle" width="42" height="42" style="object-fit: cover; border: 2px solid white;">
                        </div>
                        <div>
                          <h6 class="mb-0 fw-bold text-dark font-13">{{ $u->name }}</h6>
                          <span class="badge {{ $u->is_active ? 'badge-subtle-success' : 'badge-subtle-warning' }} font-10 px-2 py-0 rounded-pill">
                            {{ $u->is_active ? 'Active' : 'Unverified' }}
                          </span>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex flex-column">
                        <span class="font-12 fw-semibold text-slate-700"><i class="mdi mdi-email-outline me-1 text-muted"></i> {{ $u->email }}</span>
                        <span class="font-12 text-muted"><i class="mdi mdi-phone-outline me-1 text-muted"></i> {{ $u->phone ?? 'No Phone' }}</span>
                      </div>
                    </td>
                    <td>
                      @if($flatNumber)
                        <span class="badge bg-light border text-dark fw-bold font-12 px-2 py-1 rounded">
                          <i class="mdi mdi-home-outline me-1"></i> {{ $flatNumber }}
                        </span>
                      @else
                        <span class="text-muted font-12">—</span>
                      @endif
                    </td>
                    <td>
                      <span class="text-slate-500 font-12">{{ $u->created_at?->format('M d, Y') ?? 'N/A' }}</span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="text-center py-5 text-muted">
                      <img src="{{ asset('assets/images/no-data.svg') }}" height="90" class="mb-3 d-block mx-auto">
                      No residents found in your society.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          @if($employees->hasPages())
            <div class="p-3 border-top bg-white">
              {{ $employees->links('pagination::bootstrap-5') }}
            </div>
          @endif
        </div>
      </div>
      @endif

      {{-- SIDEBAR / QUICK ACTIONS --}}
      <div class="col-lg-4">
        <div class="glass-table-card p-4 mb-4">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold m-0 text-slate-800">Quick Actions</h5>
            <span class="badge badge-subtle-primary font-11 px-2 py-1 rounded-pill">Shortcuts</span>
          </div>
          <p class="text-muted font-12 mb-3">Instant one-click shortcuts to administrative modules</p>

          @if ($hasAnyPermission)
          <div class="row g-2">
            {{-- AMENITIES SHORTCUT --}}
            <div class="col-6">
              <a href="{{ route('member.amenities.index') }}" class="quick-link">
                <i class="mdi mdi-pool text-info mdi-24px d-block mb-1"></i>
                Amenities
              </a>
            </div>

            {{-- AMENITIES BOOKINGS --}}
            <div class="col-6">
              <a href="{{ route('member.amenities.bookings.manage') }}" class="quick-link">
                <i class="mdi mdi-calendar-check text-success mdi-24px d-block mb-1"></i>
                Bookings
              </a>
            </div>

            {{-- POLLS SHORTCUT --}}
            <div class="col-6">
              <a href="{{ route('member.polls.index') }}" class="quick-link">
                <i class="mdi mdi-vote text-purple mdi-24px d-block mb-1" style="color: #7e22ce;"></i>
                Polls Hub
              </a>
            </div>

            {{-- CREATE POLL --}}
            <div class="col-6">
              <a href="{{ route('member.polls.create') }}" class="quick-link">
                <i class="mdi mdi-plus-box-outline text-primary mdi-24px d-block mb-1"></i>
                New Poll
              </a>
            </div>

            {{-- COMPLAINTS / TICKETS --}}
            <div class="col-6">
              <a href="{{ route('member.tickets.index') }}" class="quick-link">
                <i class="mdi mdi-ticket-confirmation text-danger mdi-24px d-block mb-1"></i>
                Complaints
              </a>
            </div>

            {{-- NEW TICKET --}}
            <div class="col-6">
              <a href="{{ route('member.tickets.create') }}" class="quick-link">
                <i class="mdi mdi-alert-plus-outline text-warning mdi-24px d-block mb-1"></i>
                Raise Ticket
              </a>
            </div>

            @if (has_permission('resident_index'))
            <div class="col-6">
              <a href="{{ route('member.flats.manage') }}" class="quick-link">
                <i class="mdi mdi-office-building-plus text-primary mdi-24px d-block mb-1"></i>
                Buildings
              </a>
            </div>
            @endif

            @if (has_permission('resident_index'))
            <div class="col-6">
              <a href="{{ route('member.residents.index') }}" class="quick-link">
                <i class="mdi mdi-account-plus text-indigo mdi-24px d-block mb-1"></i>
                Residents
              </a>
            </div>
            @endif

            @if (has_permission('guard_index'))
            <div class="col-6">
              <a href="{{ route('member.guards.index') }}" class="quick-link">
                <i class="mdi mdi-shield-check text-success mdi-24px d-block mb-1"></i>
                Security
              </a>
            </div>
            @endif

            @if (has_permission('pass_history_index'))
            <div class="col-6">
              <a href="{{ route('member.guards.all-pass-history') }}" class="quick-link">
                <i class="mdi mdi-history text-secondary mdi-24px d-block mb-1"></i>
                Pass History
              </a>
            </div>
            @endif

            @if (has_permission('notice_index'))
            <div class="col-6">
              <a href="{{ route('member.notices.index') }}" class="quick-link">
                <i class="mdi mdi-bell-outline text-warning mdi-24px d-block mb-1"></i>
                Notices
              </a>
            </div>
            @endif
          </div>
          @else
          <div class="text-center py-4 px-2">
            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
              <i class="mdi mdi-lock-outline mdi-24px text-muted"></i>
            </div>
            <h6 class="fw-bold text-dark mb-1">Access Restricted</h6>
            <p class="text-muted small mb-0">Hi {{ Auth::user()->name }}, you do not have permission to access these tools.</p>
          </div>
          @endif
        </div>

        {{-- SOCIETY SUMMARY MINI-CARD --}}
        <div class="glass-table-card p-4">
          <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
            <i class="mdi mdi-information-outline text-primary"></i> Society Overview
          </h6>
          <div class="d-flex flex-column gap-2 font-13">
            <div class="d-flex justify-content-between py-1 border-bottom">
              <span class="text-muted">Society Name:</span>
              <span class="fw-bold text-dark">{{ $society->name ?? 'N/A' }}</span>
            </div>
            <div class="d-flex justify-content-between py-1 border-bottom">
              <span class="text-muted">Registered Residents:</span>
              <span class="fw-bold text-dark">{{ number_format($totalEmployees) }}</span>
            </div>
            <div class="d-flex justify-content-between py-1 border-bottom">
              <span class="text-muted">Facilities Configured:</span>
              <span class="fw-bold text-dark">{{ number_format($totalAmenities ?? 0) }}</span>
            </div>
            <div class="d-flex justify-content-between py-1 border-bottom">
              <span class="text-muted">Active Community Polls:</span>
              <span class="fw-bold text-success">{{ number_format($activePollsCount ?? 0) }}</span>
            </div>
            <div class="d-flex justify-content-between py-1">
              <span class="text-muted">Open Complaints:</span>
              <span class="fw-bold text-danger">{{ number_format($pendingTickets ?? 0) }}</span>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const liveFeedUrl = "{{ route('member.live-feed') }}";
    function refreshMemberLiveFeed() {
        fetch(liveFeedUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(data => {
                // Update live badge
                const liveBadge = document.getElementById('residentLiveBadge');
                if (liveBadge) liveBadge.innerText = data.count + ' Online Now';

                // Update resident list
                const residentList = document.getElementById('residentLiveList');
                if (residentList && data.users) {
                    if (data.users.length === 0) {
                        residentList.innerHTML = `<div class="p-4 text-center text-muted font-13"><i class="mdi mdi-account-clock text-muted" style="font-size: 36px;"></i><p class="mb-0 mt-2">No residents currently active in the last 5 minutes.</p></div>`;
                    } else {
                        let html = '';
                        data.users.forEach(u => {
                            html += `
                            <div class="list-group-item px-4 py-3 border-0 border-bottom d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center min-width-0">
                                    <div class="position-relative me-3 flex-shrink-0">
                                        <div class="rounded-circle bg-soft-primary text-primary d-flex align-items-center justify-content-center fw-bold font-14" style="width:40px;height:40px;">
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
                const actTbody = document.getElementById('residentActivityTbody');
                if (actTbody && data.activities) {
                    if (data.activities.length === 0) {
                        actTbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4 font-13">No resident app activity recorded yet. Waiting for live events...</td></tr>`;
                    } else {
                        let rows = '';
                        data.activities.forEach(a => {
                            const epShort = a.endpoint.length > 25 ? a.endpoint.substring(0, 25) + '...' : a.endpoint;
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
            })
            .catch(err => console.debug('Live sync error:', err));
    }

    // Refresh every 8 seconds
    setInterval(refreshMemberLiveFeed, 8000);
});
</script>
@endpush
@endsection
