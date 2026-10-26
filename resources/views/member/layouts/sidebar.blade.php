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
          $sociality = assingedSocietyName(Auth::user()->id);

        @endphp

        <h5 class="mb-0 fw-semibold d-none d-lg-block">
          {{ $greeting }}, {{ Auth::user()->name }}, Welcome to {{ $sociality ?? 'the Society' }}
        </h5>
      </div>

      <!-- Right: Actions -->
      <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">

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
            <div class="dropdown-header text-center pb-2 border-bottom">
              <h6 class="m-0">Welcome!</h6>
            </div>



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

      <!-- Logo -->
      <div class="logo-box text-center py-3">

        <a href="{{ route('member.dashboard') }}" class="logo logo-light">
          <span class="logo-lg">
            <img src="{{ asset('assets/images/logo-light.png') }}" height="28">
          </span>
        </a>
        <span class="badge rounded-pill px-3 py-2 ms-2"
          style="
                background: rgba(16, 141, 255, 0.1);
                border: 1px solid rgba(16, 141, 255, 0.2);
                color: #108dff;
                font-size: 11px;
                letter-spacing: 0.5px;
                text-transform: uppercase;
                font-weight: 700;
            ">Resident Area</span>
      </div>


      <ul id="side-menu" class="mt-2">

        <!-- Dashboard -->
        <li class="menu-title">Dashboard</li>

        @if (has_permission('resident_show'))
          <li>
            <a href="{{ route('member.dashboard') }}" class="tp-link {{ Request::routeIs('member.dashboard') ? 'active' : '' }}">
              <i data-feather="home"></i>
              <span> Overview </span>
            </a>
          </li>
          @php
            $memberSidebarLiveCount = count(\Illuminate\Support\Facades\Cache::get('active_online_user_ids', []));
            if ($memberSidebarLiveCount === 0) {
                $memberSidebarLiveCount = \App\Models\User::where('last_seen_at', '>=', now()->subMinutes(5))->count();
            }
          @endphp
          <li>
            <a href="{{ route('member.dashboard') }}#live-tracking-section" class="tp-link d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-2">
                <i data-feather="activity" class="text-success"></i>
                <span> Live Tracking </span>
              </div>
              <span class="badge bg-success text-white rounded-pill px-2 py-0 fs-10 fw-bold member-sidebar-live-count">
                {{ $memberSidebarLiveCount }} Live
              </span>
            </a>
          </li>
        @endif


        <li class="menu-title mt-2">User Management</li>

        {{-- Service Providers --}}
        @if (has_permission('service_provider_index'))
          <li>
            <a href="{{ route('member.employees.index') }}" class="tp-link {{ Request::routeIs('member.employees.*') ? 'active' : '' }}">
              <i data-feather="user-check"></i>
              <span> Service Providers </span>
            </a>
          </li>
        @endif


        {{-- Residents --}}
        @if (has_permission('resident_index'))
          <li>
            <a href="{{ route('member.residents.index') }}" class="tp-link {{ Request::routeIs('member.residents.*') ? 'active' : '' }}">
              <i data-feather="users"></i>
              <span> Residents </span>
            </a>
          </li>

          <li>
            <a href="{{ route('member.flats.manage') }}" class="tp-link {{ Request::routeIs('member.flats.*') ? 'active' : '' }}">
              <i data-feather="layers"></i>
              <span> Manage Buildings </span>
            </a>
          </li>
        @endif


        {{-- Guards --}}
        @if (has_permission('guard_index'))
          <li>
            <a href="{{ route('member.guards.index') }}" class="tp-link {{ Request::routeIs('member.guards.index') || Request::routeIs('member.guards.view') || Request::routeIs('member.guards.edit') ? 'active' : '' }}">
              <i data-feather="shield"></i>
              <span> Guards </span>
            </a>
          </li>
        @endif


        {{-- Guard Shifts --}}
        @if (has_permission('guard_shift_index'))
          <li>
            <a href="{{ route('member.shift.guards.index') }}" class="tp-link {{ Request::routeIs('member.shift.guards.*') ? 'active' : '' }}">
              <i data-feather="clock"></i>
              <span> Guard Shifts </span>
            </a>
          </li>
        @endif


        {{-- Pass History --}}
        @if (has_permission('pass_history_index'))
          <li>
            <a href="{{ route('member.guards.all-pass-history') }}" class="tp-link {{ Request::routeIs('member.guards.all-pass-history') ? 'active' : '' }}">
              <i data-feather="file-text"></i>
              <span> Pass History </span>
            </a>
          </li>
        @endif


        {{-- Utility Bills --}}

        <li>
          <a href="javascript:void(0);" class="tp-link" style="opacity: 0.6; cursor: not-allowed;">
            <i data-feather="credit-card"></i>
            <span> Utility Bills <small class="badge bg-soft-warning font-10">Soon</small> </span>
          </a>
        </li>



        {{-- Facility Management --}}

        <li>
          <a href="javascript:void(0);" class="tp-link" style="opacity: 0.6; cursor: not-allowed;">
            <i data-feather="settings"></i>
            <span> Facility Management <small class="badge bg-soft-warning font-10">Soon</small> </span>
          </a>
        </li>



        {{-- Public Notice --}}
        @if (has_permission('notice_index'))
          <li>
            <a href="{{ route('member.notices.index') }}" class="tp-link {{ Request::routeIs('member.notices.*') ? 'active' : '' }}">
              <i data-feather="bell"></i>
              <span> Notices </span>
            </a>
          </li>
        @endif

        <li class="menu-title mt-2">Society Services</li>

        <li>
          <a href="{{ route('member.tickets.index') }}" class="tp-link {{ Request::routeIs('member.tickets.*') ? 'active' : '' }}">
            <i data-feather="message-square"></i>
            <span> Complaints & Helpdesk </span>
          </a>
        </li>

        <li>
          <a href="{{ route('member.amenities.index') }}" class="tp-link {{ Request::routeIs('member.amenities.*') ? 'active' : '' }}">
            <i data-feather="calendar"></i>
            <span> Amenity Booking </span>
          </a>
        </li>

        <li>
          <a href="{{ route('member.polls.index') }}" class="tp-link {{ Request::routeIs('member.polls.*') ? 'active' : '' }}">
            <i data-feather="pie-chart"></i>
            <span> Polls & Surveys </span>
          </a>
        </li>


      </ul>

    </div>

    <div class="clearfix"></div>
  </div>
</div>
<!-- Left Sidebar End -->
