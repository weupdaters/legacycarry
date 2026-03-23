<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ setting('site_name') }} | @yield('title', 'Dashboard')</title>

  <!-- Favicon -->
  @php
    $favicon = setting('site_favicon');
    $faviconUrl = $favicon ? asset('storage/' . $favicon) : asset('assets/images/favicon.ico');
  @endphp

  <link rel="shortcut icon" href="{{ $faviconUrl }}" type="image/png">
  <link rel="icon" href="{{ $faviconUrl }}" type="image/png">

  <!-- App css -->
  <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-style" />

  <!-- Icons -->
  <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />

  <!-- Head JS -->
  <script src="{{ asset('assets/js/head.js') }}"></script>
</head>

<body>


  <!-- Main Content -->
  <div class="content">
    @yield('content')
  </div>

  <!-- Vendor JS -->
  <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
  <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
  <script src="{{ asset('assets/libs/waypoints/lib/jquery.waypoints.min.js') }}"></script>
  <script src="{{ asset('assets/libs/jquery.counterup/jquery.counterup.min.js') }}"></script>
  <script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>

  <!-- App js-->
  <script src="{{ asset('assets/js/app.js') }}"></script>
</body>

</html>
