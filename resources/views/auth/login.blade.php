@extends('auth.layouts.main')
@section('title', 'Login')
@section('content')

  <style>
    .gold-logo {
      font-size: 22px;
      font-weight: 800;
      color: black;
      /* Strong visibility */
    }
  </style>
  <div class="account-page">
    <div class="container-fluid p-0">
      <div class="row align-items-center g-0 px-3 py-3 vh-100">

        <div class="col-xl-12">
          <div class="row">
            <div class="col-md-4 mx-auto">
              <div class="card">
                <div class="card-body">
                  <div class="mb-0 p-0 p-lg-3">
                    <div class="mb-0 border-0 p-md-4 p-lg-0">
                      <div class="mb-4 p-0 text-lg-start text-center">
                        <div class="auth-brand">
                          <a href="#" class="logo logo-light">
                            <span class="logo-lg">
                              @php
                                $logo = setting('site_logo');
                                $logoHeight = setting('site_logo_height') . 'px' ?: '120px';
                                $logoUrl = $logo ? asset('storage/' . $logo) : asset('images/default-logo.png');
                              @endphp
                              <img src="{{ $logoUrl }}" alt="" height="{{ $logoHeight }}">
                              <h2 class="gold-logo">{{ setting('site_name') }}</h2>
                            </span>
                          </a>
                          <a href="#" class="logo logo-dark">
                            <span class="logo-lg">
                              @php
                                $logoDark = setting('site_logo_dark');
                                $logoDarkHeight = setting('site_logo_height') . 'px' ?: '120px';
                                $logoDarkUrl = $logoDark
                                    ? asset('storage/' . $logoDark)
                                    : asset('images/default-logo-dark.png');
                              @endphp
                              <img src="{{ $logoDarkUrl }}" alt="" height="{{ $logoDarkHeight }}">
                              <h2 class="gold-logo">{{ setting('site_name') }}</h2>
                            </span>
                          </a>
                        </div>
                      </div>

                      <!-- Laravel Plain HTML Form -->
                      @if (session('status'))
                        <div class="alert alert-success">
                          {{ session('status') }}
                        </div>
                      @endif

                      @if ($errors->any())
                        <div class="alert alert-danger">
                          <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                            @endforeach
                          </ul>
                        </div>
                      @endif

                      <div class="pt-0">
                        <form method="POST" action="{{ route('login.submit') }}" class="my-4">
                          @csrf

                          <!-- Email -->
                          <div class="form-group mb-3">
                            <label for="emailaddress" class="form-label">Email address</label>
                            <input class="form-control @error('email') is-invalid @enderror" type="email" name="email"
                              id="emailaddress" value="{{ old('email') }}" required autofocus
                              placeholder="Enter your email">
                            @error('email')
                              <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                          </div>

                          <!-- Password -->
                          <div class="form-group mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input class="form-control @error('password') is-invalid @enderror" type="password"
                              name="password" id="password" required placeholder="Enter your password">
                            @error('password')
                              <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                          </div>

                          <!-- Remember + Forgot -->
                          <div class="form-group d-flex mb-3">
                            <div class="col-sm-6">
                              <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember"
                                  {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">Remember me</label>
                              </div>
                            </div>
                            <div class="col-sm-6 text-end">
                              @if (Route::has('password.request'))
                                <a class="text-muted fs-14" href="{{ route('password.request') }}">Forgot password?</a>
                              @endif
                            </div>
                          </div>

                          <!-- Submit -->
                          <div class="form-group mb-0 row">
                            <div class="col-12">
                              <div class="d-grid">
                                <button class="btn btn-primary fw-semibold" type="submit"> Log In </button>
                              </div>
                            </div>
                          </div>
                        </form>
                        {{-- <div class="text-center text-muted">
                                               <p class="mb-0">Don't have an account ?<a class='text-primary ms-2 fw-medium' href='auth-register.html'>Sing up</a></p>
                                           </div> --}}

                      </div>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>



      </div>
    </div>
  </div>

@endsection
