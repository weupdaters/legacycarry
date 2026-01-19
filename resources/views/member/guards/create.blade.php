@extends('member.layouts.app')

@section('content')
  <div class="container-fluid">

    <div class="py-3 d-flex justify-content-between align-items-center">
      <h4 class="fw-semibold">
        <i class="bi bi-person-plus-fill me-1"></i> Add Security Guard
      </h4>

      <a href="{{ route('guards.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>

    <div class="card shadow-sm">
      <div class="card-body">

        <form action="{{ route('member.guards.store') }}" method="POST">
          @csrf

          <div class="row">

            {{-- BASIC DETAILS --}}
            <div class="col-md-6 mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="name" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Full Name</label>
              <input type="text" name="full_name" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Phone</label>
              <input type="text" name="phone" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>




            {{-- PERSONAL DETAILS --}}
            <div class="col-md-6 mb-3">
              <label class="form-label">Gender</label>
              <select name="gender" class="form-select">
                <option value="">Select</option>
                <option>Male</option>
                <option>Female</option>
                <option>Other</option>
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Date of Birth</label>
              <input type="date" name="dob" class="form-control">
            </div>

            <div class="col-md-12 mb-3">
              <label class="form-label">Address</label>
              <textarea name="address" rows="2" class="form-control"></textarea>
            </div>

            {{-- EMERGENCY --}}
            <div class="col-md-6 mb-3">
              <label class="form-label">Emergency Contact Name</label>
              <input type="text" name="emergency_contact_name" class="form-control">
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Emergency Contact Phone</label>
              <input type="text" name="emergency_contact_phone" class="form-control">
            </div>

          </div>

          <div class="text-end">
            <button class="btn btn-success px-4">
              <i class="bi bi-check-circle"></i> Create Guard
            </button>
          </div>

        </form>

      </div>
    </div>

  </div>
@endsection
