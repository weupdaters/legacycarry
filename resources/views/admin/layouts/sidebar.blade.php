<!-- Topbar Start -->
<div class="topbar-custom shadow-sm">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">

      <!-- Left: Greeting + Menu Toggle -->
      <div class="d-flex align-items-center gap-3">
        <button class="button-toggle-menu nav-link">
          <i data-feather="menu" class="noti-icon"></i>
        </button>

        @php
          $hour = now()->format('H');
          $greeting = $hour < 12 ? 'Good Morning' : ($hour < 18 ? 'Good Afternoon' : 'Good Evening');
        @endphp

        <h5 class="mb-0 fw-semibold d-none d-lg-block">
          {{ $greeting }}, {{ Auth::user()->name }}
        </h5>
      </div>

      <!-- Right: Actions -->
      <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">

        <li class="d-none d-sm-flex me-2">
          <span class="nav-link" id="current-date-time">{{ now()->format('F d, Y H:i:s') }}</span>
        </li>

        <!-- Fullscreen -->
        <li class="d-none d-sm-flex me-2">
          <button type="button" class="btn nav-link" data-toggle="fullscreen">
            <i data-feather="maximize" class="noti-icon"></i>
          </button>
        </li>

        <!-- Light / Dark Mode -->
        <li class="d-none d-sm-flex me-2">
          <button type="button" class="btn nav-link" id="light-dark-mode">
            <i data-feather="moon" class="dark-mode"></i>
            <i data-feather="sun" class="light-mode"></i>
          </button>
        </li>

        <!-- User Menu -->
        <li class="dropdown topbar-dropdown">
          <a class="nav-link dropdown-toggle nav-user d-flex align-items-center" data-bs-toggle="dropdown">
            <div class="position-relative">
              <img src="{{ Auth::user()->avatar_url ?? asset('assets/images/users/user-13.jpg') }}" 
                   class="rounded-circle border border-2 border-white shadow-sm" 
                   height="34" width="34" style="object-fit: cover;">
              <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" 
                    style="width: 10px; height: 10px; transform: translate(25%, 25%);"></span>
            </div>
            <span class="ms-2 fw-medium d-none d-sm-inline-block text-dark">{{ Auth::user()->name }} <i class="mdi mdi-chevron-down"></i></span>
          </a>

          <div class="dropdown-menu dropdown-menu-end profile-dropdown">

            <div class="dropdown-divider"></div>
            <a href="{{ route('logout') }}" class="dropdown-item text-danger">
              <i class="mdi mdi-location-exit me-1"></i> Logout
            </a>
          </div>
        </li>

      </ul>
    </div>
  </div>
</div>
<!-- end Topbar -->

<!-- Left Sidebar Start -->
<div class="app-sidebar-menu">
  <div class="h-100" data-simplebar>

    <div id="sidebar-menu">

      @php
        $logo = setting('site_logo');
        $logoHeight = setting('site_logo_height') . 'px' ?: '60px';
        $logoUrl = $logo ? asset('storage/' . $logo) : asset('images/default-logo.png');
      @endphp

      <div class="logo-box" style="padding: 12px 16px; border-bottom: 1px solid rgba(0,0,0,0.06);">
        <a href="{{ route('admin.dashboard') }}" class="logo logo-dark d-flex align-items-center gap-2">
          <span class="logo-lg">
            <img src="{{ $logoUrl }}" style="height: {{ $logoHeight }}; width: auto; max-height: 44px;" alt="Website Logo">
          </span>
          <span class="badge rounded-pill px-2 py-1"
                style="background: rgba(255, 107, 107, 0.1); border: 1px solid rgba(255, 107, 107, 0.2); color: #ff6b6b; font-size: 10px; font-weight: 700; text-transform: uppercase;">
                Admin Portal
          </span>
        </a>
      </div>

      <style>
        /* Section title alignment */
        #side-menu .menu-title {
          padding: 14px 20px 6px 20px;
          font-size: 10px;
          font-weight: 700;
          letter-spacing: 0.08em;
          text-transform: uppercase;
          color: #94a3b8;
        }
        /* Menu item row alignment */
        #side-menu li > a.tp-link {
          display: flex;
          align-items: center;
          gap: 10px;
          padding: 8px 20px;
          font-size: 13.5px;
          font-weight: 500;
          color: #374151;
          border-radius: 0;
          transition: all 0.2s;
        }
        #side-menu li > a.tp-link:hover,
        #side-menu li > a.tp-link.active {
          color: #4A00E0;
          background: rgba(74, 0, 224, 0.06);
          border-left: 3px solid #4A00E0;
          padding-left: 17px;
        }
        /* Icon sizing */
        #side-menu li > a.tp-link svg {
          width: 16px;
          height: 16px;
          flex-shrink: 0;
          stroke-width: 2;
        }
        /* Remove extra spacing inside span */
        #side-menu li > a.tp-link span {
          line-height: 1.4;
        }
      </style>

      <ul id="side-menu" class="mt-2">

        {{-- ─── DASHBOARD ─── --}}
        <li class="menu-title">Dashboard</li>
        <li>
          <a href="{{ route('admin.dashboard') }}" class="tp-link">
            <i data-feather="monitor"></i>
            <span>Overview</span>
          </a>
        </li>

        {{-- ─── SOCIETY MANAGEMENT ─── --}}
        <li class="menu-title mt-2">Society Management</li>
        <li>
          <a href="{{ route('societies.index') }}" class="tp-link">
            <i data-feather="home"></i>
            <span>Societies</span>
          </a>
        </li>
        <li>
          <a href="{{ route('admin.notices.index') }}" class="tp-link">
            <i data-feather="bell"></i>
            <span>Notices</span>
          </a>
        </li>

        {{-- ─── JOB SYSTEM ─── --}}
        <li class="menu-title mt-2">Job System</li>
        <li>
          <a href="{{ route('jobs.index') }}" class="tp-link">
            <i data-feather="briefcase"></i>
            <span>All Jobs</span>
          </a>
        </li>

        {{-- ─── USER MANAGEMENT ─── --}}
        <li class="menu-title mt-2">User Management</li>
        <li>
          <a href="{{ route('services.index') }}" class="tp-link">
            <i data-feather="grid"></i>
            <span>Services</span>
          </a>
        </li>
        <li>
          <a href="{{ route('employees.index') }}" class="tp-link">
            <i data-feather="users"></i>
            <span>Service Providers</span>
          </a>
        </li>
        <li>
          <a href="{{ route('residents.index') }}" class="tp-link">
            <i data-feather="user-check"></i>
            <span>Residents</span>
          </a>
        </li>
        <li>
          <a href="{{ route('resident.assign.index') }}" class="tp-link">
            <i data-feather="user-plus"></i>
            <span>Assign Residents</span>
          </a>
        </li>

        {{-- ─── SECURITY ─── --}}
        <li class="menu-title mt-2">Security</li>
        <li>
          <a href="{{ route('guards.index') }}" class="tp-link">
            <i data-feather="shield"></i>
            <span>Guards</span>
          </a>
        </li>
        <li>
          <a href="{{ route('shift.guards.index') }}" class="tp-link">
            <i data-feather="clock"></i>
            <span>Guard Shifts</span>
          </a>
        </li>
        <li>
          <a href="{{ route('guards.all-pass-history') }}" class="tp-link">
            <i data-feather="key"></i>
            <span>Pass History</span>
          </a>
        </li>

        {{-- ─── PAYMENT ─── --}}
        <li class="menu-title mt-2">Payment</li>
        <li>
          <a href="{{ route('admin.payments.index') }}" class="tp-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
            <i data-feather="credit-card"></i>
            <span>Payment Logs</span>
          </a>
        </li>
        <li>
          @php
            $pendingWithdrawalsCount = \App\Models\Withdrawal::where('status', 'pending')->count();
          @endphp
          <a href="{{ route('admin.withdrawals.index') }}" class="tp-link {{ request()->routeIs('admin.withdrawals.*') ? 'active' : '' }} d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
              <i data-feather="dollar-sign"></i>
              <span>Withdrawals</span>
            </div>
            @if($pendingWithdrawalsCount > 0)
              <span class="badge bg-warning text-dark rounded-pill px-2" style="font-size: 11px;">{{ $pendingWithdrawalsCount }}</span>
            @endif
          </a>
        </li>

        {{-- ─── SUPPORT & REPORTS ─── --}}
        <li class="menu-title mt-2">Support & Reports</li>
        <li>
          <a href="{{ route('admin.reports.index') }}" class="tp-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
            <i data-feather="alert-circle"></i>
            <span>Reported Bugs</span>
          </a>
        </li>

        {{-- ─── SYSTEM SETTINGS ─── --}}
        <li class="menu-title mt-2">System Settings</li>
        <li>
          <a href="{{ route('settings') }}" class="tp-link">
            <i data-feather="settings"></i>
            <span>Settings</span>
          </a>
        </li>
        <li>
          <a href="{{ route('admin.notifications.index') }}" class="tp-link">
            <i data-feather="bell"></i>
            <span>Notifications</span>
          </a>
        </li>

      </ul>

    </div>

    <div class="clearfix"></div>
  </div>
</div>
<!-- Left Sidebar End -->
