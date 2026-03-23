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
          <a class="nav-link dropdown-toggle nav-user" data-bs-toggle="dropdown">
            <img
              src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('images/default-avatar.png') }}"
              class="rounded-circle" height="32" alt="Avatar">

            <span class="ms-1">{{ Auth::user()->name }} <i class="mdi mdi-chevron-down"></i></span>
          </a>

          <div class="dropdown-menu dropdown-menu-end profile-dropdown">
            {{-- <div class="dropdown-header text-center pb-2 border-bottom">
                            <h6 class="m-0">Welcome!</h6>
                        </div> --}}

            <a href="{{ route('settings') }}?tab=admin" class="dropdown-item">
              <i class="mdi mdi-account-circle-outline me-1"></i> My Account
            </a>

            {{-- <a href="{{ url('auth-lock-screen') }}" class="dropdown-item">
                            <i class="mdi mdi-lock-outline me-1"></i> Lock Screen
                        </a> --}}

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

      <div class="logo-box text-center">
        <a href="{{ route('admin.dashboard') }}" class="logo logo-dark">

          <span class="logo-lg">
            <img src="{{ $logoUrl }}" style="height: {{ $logoHeight }}; width: auto;" alt="Website Logo">
          </span>

        </a>
      </div>


      <ul id="side-menu" class="mt-2">

        <!-- Dashboard -->
        <li class="menu-title">Dashboard</li>
        <li>
          <a href="{{ route('admin.dashboard') }}" class="tp-link">
            <i data-feather="home"></i>
            <span> Overview </span>
          </a>
        </li>

        <!-- SOCIETY MANAGEMENT -->
        <li class="menu-title mt-2">Society Management</li>
        <li>
          <a href="{{ route('societies.index') }}" class="tp-link">
            <i data-feather="aperture"></i>
            <span> Societies </span>
          </a>
        </li>

        <!-- JOB SYSTEM -->
        <li class="menu-title mt-2">Job System</li>
        <li>
          <a href="{{ route('jobs.index') }}" class="tp-link">
            <i data-feather="briefcase"></i>
            <span> All Jobs </span>
          </a>
        </li>

        <!-- USER & ROLE MANAGEMENT -->
        <li class="menu-title mt-2">User Management</li>

        <li>
          <a href="{{ route('services.index') }}" class="tp-link">
            <i data-feather="grid"></i>
            <span> Services </span>
          </a>
        </li>


        <li>
          <a href="{{ route('employees.index') }}" class="tp-link">
            <i data-feather="users"></i>
            <span> Service Providers Management </span>
          </a>
        </li>

        <li>
          <a href="{{ route('residents.index') }}" class="tp-link">
            <i data-feather="user-check"></i>
            <span> Residents </span>
          </a>
        </li>

        <li>
          <a href="{{ route('guards.index') }}" class="tp-link">
            <i data-feather="shield"></i>
            <span> Guards </span>
          </a>
        </li>

        <li>
          <a href="{{ route('guards.all-pass-history') }}" class="tp-link">
            <i data-feather="clock"></i>
            <span> Pass History </span>
          </a>
        </li>

        <li>
          <a href="{{ route('shift.guards.index') }}" class="tp-link">
            <i data-feather="clock"></i>
            <span> Guard Shifts </span>
          </a>
        </li>

        <li>
          <a href="{{ route('resident.assign.index') }}" class="tp-link">
            <i data-feather="user-plus"></i>
            <span> Assign Residents </span>
          </a>
        </li>

        <!-- Settings -->
        <li class="menu-title mt-2">Payment</li>
        <li>
          <a href="{{ route('admin.payments.index') }}" class="tp-link">
            <i data-feather="file-text"></i>
            <span> Payment </span>
          </a>
        </li>


        <!-- Settings -->
        <li class="menu-title mt-2">System Settings</li>
        <li>
          <a href="{{ route('settings') }}" class="tp-link">
            <i data-feather="file-text"></i>
            <span> Settings </span>
          </a>
        </li>
        <li>
          <a href="{{ route('admin.notifications.index') }}" class="tp-link">
            <i data-feather="file-text"></i>
            <span> Notifications </span>
          </a>
        </li>
        {{-- <li>
                    <a href="{{ route('settings.edit', 'faqs') }}" class="tp-link">
                        <i data-feather="help-circle"></i>
                        <span> FAQs </span>
                    </a>
                </li> --}}

      </ul>

    </div>

    <div class="clearfix"></div>
  </div>
</div>
<!-- Left Sidebar End -->
