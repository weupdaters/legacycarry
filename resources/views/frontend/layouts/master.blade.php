<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>@yield('title', 'Legacy Carry')</title>

  <meta name="description"
    content="@yield('meta_description', 'Legacy Carry platform')" />

  <link rel="icon" href="{{ asset('frontend/logo.png') }}">

  <link rel="stylesheet" href="{{ asset('frontend/style.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/custom.css') }}">

  <script src="{{ asset('frontend/main.js') }}"></script>
<link rel="canonical" href="{{ isset($canonical) ? $canonical : request()->url() }}" />
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  {{-- INLINE CSS --}}
 

  @stack('styles')

</head>

<body class="page-enter">

  {{-- HEADER --}}
  @include('frontend.layouts.header')

  {{-- MAIN CONTENT --}}
  <main>
    @yield('content')
  </main>

  {{-- FOOTER --}}
  @include('frontend.layouts.footer')

  @stack('scripts')

</body>

</html>