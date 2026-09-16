@extends('member.layouts.app')

@section('title', 'Society Dashboard')

@section('content')
  @php
    if (!isset($society)) {
        $assignment = \App\Models\ResidentAssignment::where('user_id', auth()->id())->first();
        $society = $assignment ? \App\Models\Society::find($assignment->society_id) : null;
    }
    $hasAnyPermission = has_permission('resident_index') || has_permission('guard_index') || has_permission('pass_history_index') || has_permission('notice_index');
  @endphp
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    body {
      font-family: 'Inter', sans-serif;
      background-color: #f8fafc;
    }

    .welcome-card {
      background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
      border-radius: 20px;
      padding: 40px;
      color: white;
      position: relative;
      overflow: hidden;
      box-shadow: 0 10px 25px rgba(59, 130, 246, 0.2);
    }

    .welcome-card::after {
      content: '';
      position: absolute;
      top: -50px;
      right: -50px;
      width: 200px;
      height: 200px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
    }

    .stat-card {
      border: none;
      border-radius: 18px;
      padding: 20px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      background: white;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
    }

    .stat-icon {
      width: 54px;
      height: 54px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      margin-bottom: 15px;
    }

    .bg-soft-primary { background-color: #e0e7ff; color: #4338ca; }
    .bg-soft-success { background-color: #dcfce7; color: #15803d; }
    .bg-soft-info { background-color: #e0f2fe; color: #0369a1; }
    .bg-soft-warning { background-color: #fef3c7; color: #92400e; }

    .glass-table-card {
      background: white;
      border-radius: 20px;
      border: 1px solid rgba(0,0,0,0.05);
      box-shadow: 0 4px 15px rgba(0,0,0,0.02);
      overflow: hidden;
    }

    .table thead th {
      background-color: #f8fafc;
      text-transform: uppercase;
      font-size: 11px;
      letter-spacing: 0.05em;
      color: #64748b;
      border-bottom: 1px solid #f1f5f9;
      padding: 15px 20px;
    }

    .table tbody td {
      padding: 18px 20px;
      vertical-align: middle;
      font-size: 14px;
      color: #334155;
      border-bottom: 1px solid #f1f5f9;
    }

    .society-badge {
      background: #f1f5f9;
      padding: 8px 16px;
      border-radius: 50px;
      font-weight: 600;
      font-size: 13px;
      color: #1e293b;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .avatar-ring {
      padding: 3px;
      background: linear-gradient(135deg, #3b82f6, #10b981);
      border-radius: 50%;
      display: inline-block;
    }

    .quick-link {
      background: #ffffff;
      border: 1px solid #f1f5f9;
      padding: 15px;
      border-radius: 15px;
      text-align: center;
      color: #475569;
      text-decoration: none;
      transition: all 0.2s;
      display: block;
    }

    .quick-link:hover {
      background: #3b82f6;
      color: white;
      border-color: #3b82f6;
    }
  </style>

  <div class="container-fluid py-4">

    {{-- WELCOME BANNER --}}
    <div class="row mb-4">
      <div class="col-12">
        <div class="welcome-card">
          <div class="row align-items-center">
            <div class="col-md-7">
              <h1 class="fw-bold mb-2">Welcome back, {{ Auth::user()->name }}! 👋</h1>
              <p class="fs-16 opacity-75 mb-0">Here's what's happening in <strong>{{ $society->name ?? 'Your Society' }}</strong> today.</p>
              <div class="mt-4">
                <span class="society-badge">
                  <i class="mdi mdi-office-building"></i> {{ $society->name ?? 'N/A' }}
                </span>
                <span class="society-badge ms-2">
                  <i class="mdi mdi-map-marker"></i> {{ $society->address ?? 'N/A' }}
                </span>
              </div>
            </div>
            <div class="col-md-5 d-none d-md-block text-end">
              @if($society && $society->profile_image)
                <img src="{{ asset('storage/' . $society->profile_image) }}" class="rounded-3 shadow-lg" style="height: 120px; width: 120px; object-fit: cover; border: 4px solid rgba(255,255,255,0.2);">
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>

    @if($hasAnyPermission)
    {{-- STATS ROW --}}
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="stat-card">
          <div class="stat-icon bg-soft-primary">
            <i class="mdi mdi-account-group"></i>
          </div>
          <h3 class="fw-bold mb-1">{{ number_format($totalEmployees) }}</h3>
          <p class="text-muted mb-0 font-14">Total Residents</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card">
          <div class="stat-icon bg-soft-success">
            <i class="mdi mdi-briefcase-check"></i>
          </div>
          <h3 class="fw-bold mb-1">{{ number_format($activeJobs) }}</h3>
          <p class="text-muted mb-0 font-14">Active Service Requests</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card">
          <div class="stat-icon bg-soft-info">
            <i class="mdi mdi-check-decagram"></i>
          </div>
          <h3 class="fw-bold mb-1">{{ number_format($closedJobs) }}</h3>
          <p class="text-muted mb-0 font-14">Completed Jobs</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card">
          <div class="stat-icon bg-soft-warning">
            <i class="mdi mdi-clock-alert"></i>
          </div>
          <h3 class="fw-bold mb-1">{{ number_format($onHoldJobs) }}</h3>
          <p class="text-muted mb-0 font-14">Pending Requests</p>
        </div>
      </div>
    </div>
    @endif

    <div class="row">
      @if($hasAnyPermission)
      {{-- RESIDENTS TABLE --}}
      <div class="col-lg-8">
        <div class="glass-table-card mb-4">
          <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold m-0 text-slate-800">Recent Society Residents</h5>
            <a href="{{ route('member.residents.index') }}" class="btn btn-sm btn-outline-primary rounded-pill">View All</a>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Resident Profile</th>
                  <th>Contact Information</th>
                  <th>Joined Date</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($employees as $emp)
                  @php
                    $u = $emp instanceof \App\Models\User ? $emp : ($emp->user ?? $emp);
                  @endphp
                  <tr>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="avatar-ring me-3">
                          <img src="{{ $u->avatar_url ?? asset('assets/images/users/default.png') }}"
                               class="rounded-circle" width="45" height="45" style="object-fit: cover; border: 2px solid white;">
                        </div>
                        <div>
                          <h6 class="mb-0 fw-bold">{{ $u->name }}</h6>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex flex-column">
                        <span class="font-13"><i class="mdi mdi-email-outline me-1"></i> {{ $u->email }}</span>
                        <span class="font-13 text-muted"><i class="mdi mdi-phone-outline me-1"></i> {{ $u->phone ?? 'No Phone' }}</span>
                      </div>
                    </td>
                    <td>
                      <span class="text-slate-500 font-13">{{ $u->created_at?->format('M d, Y') ?? 'N/A' }}</span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="3" class="text-center py-5 text-muted">
                      <img src="{{ asset('assets/images/no-data.svg') }}" height="100" class="mb-3 d-block mx-auto">
                      No residents found in your society.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          @if($employees->hasPages())
            <div class="p-4 border-top">
              {{ $employees->links('pagination::bootstrap-5') }}
            </div>
          @endif
        </div>
      </div>
      @endif

      {{-- SIDEBAR / QUICK ACTIONS --}}
      <div class="col-lg-4">
        <div class="glass-table-card p-4 mb-4">
          <h5 class="fw-bold mb-4">Quick Actions</h5>
          @if ($hasAnyPermission)
          <div class="row g-3">
            @if (has_permission('resident_index'))
            <div class="col-6">
              <a href="{{ route('member.flats.manage') }}" class="quick-link">
                <i class="mdi mdi-office-building-plus mdi-24px d-block mb-1"></i>
                Buildings
              </a>
            </div>
            @endif

            @if (has_permission('resident_index'))
            <div class="col-6">
              <a href="{{ route('member.residents.index') }}" class="quick-link">
                <i class="mdi mdi-account-plus mdi-24px d-block mb-1"></i>
                Residents
              </a>
            </div>
            @endif

            @if (has_permission('guard_index'))
            <div class="col-6">
              <a href="{{ route('member.guards.index') }}" class="quick-link">
                <i class="mdi mdi-shield-check mdi-24px d-block mb-1"></i>
                Security
              </a>
            </div>
            @endif

            @if (has_permission('pass_history_index'))
            <div class="col-6">
              <a href="{{ route('member.guards.all-pass-history') }}" class="quick-link">
                <i class="mdi mdi-history mdi-24px d-block mb-1"></i>
                Pass History
              </a>
            </div>
            @endif

            @if (has_permission('notice_index'))
            <div class="col-6">
              <a href="{{ route('member.notices.index') }}" class="quick-link">
                <i class="mdi mdi-bell-outline mdi-24px d-block mb-1"></i>
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


      </div>
    </div>

  </div>

@endsection
