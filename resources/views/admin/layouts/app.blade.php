<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="apple-mobile-web-app-capable" content="yes">

  <title>{{ setting('site_name') }} | @yield('title', 'Dashboard')</title>

  <!-- Favicon -->
  @php
    $favicon = setting('site_favicon');
    $faviconUrl = $favicon ? asset('storage/' . $favicon) : asset('assets/images/favicon.ico');
  @endphp

  <link rel="shortcut icon" href="{{ $faviconUrl }}" type="image/png">
  <link rel="icon" href="{{ $faviconUrl }}" type="image/png">


  <link href="{{ asset('assets/libs/simple-datatables/style.css') }}" rel="stylesheet" type="text/css" />

  <!-- App CSS -->
  <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-style" />
  <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Page-specific CSS -->
  @stack('styles')

  <style>
    /* Global pagination SVG & layout protections */
    nav[role="navigation"] svg,
    .pagination svg,
    nav svg {
      width: 1.25rem !important;
      height: 1.25rem !important;
      max-width: 1.25rem !important;
      max-height: 1.25rem !important;
      display: inline-block !important;
    }
  </style>

  <!-- Head JS -->
  <script src="{{ asset('assets/js/head.js') }}"></script>
</head>

<body data-menu-color="light" data-sidebar="default">

  <!-- Loader Start -->
  <div id="loading">
    <div id="loading-center"></div>
  </div>
  <!-- Loader End -->

  <!-- Begin page -->
  <div id="app-layout">
    <!-- Sidebar -->
    @include('admin.layouts.sidebar')

    <!-- Page Content -->
    <div class="content-page">
      <div class="content">
        @yield('content')
      </div>

      <!-- Footer Start -->
      <footer class="footer">
        <div class="container-fluid">
          <div class="row">
            <div class="col fs-13 text-muted text-center">
              &copy;
              <script>
                document.write(new Date().getFullYear())
              </script>
              <span class="fw-semibold text-dark">Legacy Carry</span>. All rights reserved.
            </div>
          </div>
        </div>
      </footer>
      <!-- Footer End -->

    </div>
    <!-- End Page Content -->

  </div>
  <!-- END Wrapper -->

  <!-- Modals -->
  @include('admin.layouts.model')

  <!-- Notifications -->
  @include('admin.layouts.notification')

  <!-- Vendor JS -->
  <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
  <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
  <script src="{{ asset('assets/libs/waypoints/lib/jquery.waypoints.min.js') }}"></script>
  <script src="{{ asset('assets/libs/jquery.counterup/jquery.counterup.min.js') }}"></script>
  <script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>

  <!-- ApexCharts -->
  <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
  <script src="https://apexcharts.com/samples/assets/stock-prices.js"></script>

  <!-- Page/Widgets JS -->
  <script src="{{ asset('assets/js/pages/hrm-dashboard.init.js') }}"></script>
  <script src="{{ asset('assets/js/app.js') }}"></script>

  <!-- Simple Datatables JS -->
  <script src="{{ asset('assets/libs/simple-datatables/umd/simple-datatables.js') }}"></script>

  <!-- Simple Datatables Init JS -->
  <script src="{{ asset('assets/js/pages/simple-datatables.init.js') }}"></script>

  <!-- Page-specific JS -->
  @stack('scripts')
</body>

</html>
