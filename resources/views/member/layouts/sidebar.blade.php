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
          <a class="nav-link dropdown-toggle nav-user" data-bs-toggle="dropdown">
            <img src="{{ asset('assets/images/users/user-13.jpg') }}" class="rounded-circle" height="32">
            <span class="ms-1">{{ Auth::user()->name }} <i class="mdi mdi-chevron-down"></i></span>
          </a>

          <div class="dropdown-menu dropdown-menu-end profile-dropdown">
            <div class="dropdown-header text-center pb-2 border-bottom">
              <h6 class="m-0">Welcome!</h6>
            </div>

            <a href="{{ url('pages-profile') }}" class="dropdown-item">
              <i class="mdi mdi-account-circle-outline me-1"></i> My Account
            </a>

            <a href="{{ url('auth-lock-screen') }}" class="dropdown-item">
              <i class="mdi mdi-lock-outline me-1"></i> Lock Screen
            </a>

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

        <a href="{{ route('admin.dashboard') }}" class="logo logo-light">
          <span class="logo-lg">
            <img src="{{ asset('assets/images/logo-light.png') }}" height="28">
          </span>
        </a>
        <span class="btn-sm btn ml-10"
          style="
                background: transparent;
                border: 1px dashed #108dff;
                color: #108dff;
            ">Resident
          area</span>
      </div>


      <ul id="side-menu" class="mt-2">

        <!-- Dashboard -->
        <li class="menu-title">Dashboard</li>
        <li>
          <a href="{{ route('member.dashboard') }}" class="tp-link">
            <i data-feather="home"></i>
            <span> Overview </span>
          </a>
        </li>





        <!-- USER & ROLE MANAGEMENT -->
        <li class="menu-title mt-2">User Management</li>
        <li>
          <a href="{{ route('member.employees.index') }}" class="tp-link">
            <i data-feather="user-check"></i>
            <span> Service Providers </span>
          </a>
        </li>

        <li>
          <a href="{{ route('member.residents.index') }}" class="tp-link">
            <i data-feather="user-check"></i>
            <span> Residents </span>
          </a>
        </li>

        <li>
          <a href="{{ route('member.guards.index') }}" class="tp-link">
            <i data-feather="user-check"></i>
            <span> Guards </span>
          </a>
        </li>


        <li>
          <a href="{{ route('member.shift.guards.index') }}" class="tp-link">
            <i data-feather="clock"></i>
            <span> Guard Shifts </span>
          </a>
        </li>

        <li>
          <a href="{{ route('member.guards.all-pass-history') }}" class="tp-link">
            <i data-feather="clock"></i>
            <span> Pass history </span>
          </a>
        </li>


        <li>
          <a href="#" class="tp-link">
            <i data-feather="clock"></i>
            <span> Utility Bills </span></span>
          </a>
        </li>

        <li>
          <a href="#" class="tp-link">
            <i data-feather="clock"></i>
            <span> Facility Management </span>
          </a>
        </li>
        <li>
          <a href="#" class="tp-link">
            <i data-feather="clock"></i>
            <span> Public Notice </span>
          </a>
        </li>


      </ul>

    </div>

    <div class="clearfix"></div>
  </div>
</div>
<!-- Left Sidebar End -->
