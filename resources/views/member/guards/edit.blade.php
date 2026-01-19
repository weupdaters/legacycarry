@extends('member.layouts.app')

@section('content')
  <div class="container-fluid">

    <div class="py-3 d-flex justify-content-between align-items-center">
      <h4 class="fw-semibold">
        <i class="bi bi-pencil-square me-1"></i> Edit Security Guard
      </h4>

      <a href="{{ route('member.guards.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>

    <div class="card shadow-sm">
      <div class="card-body">

        <form action="{{ route('member.guards.update', $user->id) }}" method="POST">
          @csrf
          @method('PATCH')

          <div class="row">

            {{-- BASIC DETAILS --}}
            <div class="col-md-6 mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="name" value="{{ $user->name }}" class="form-control">
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Full Name</label>
              <input type="text" name="full_name" value="{{ $user->detail->full_name ?? '' }}" class="form-control">
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" value="{{ $user->email }}" class="form-control">
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Phone</label>
              <input type="text" name="phone" value="{{ $user->phone }}" class="form-control">
            </div>




            <div class="col-md-6 mb-3">
              <label class="form-label">Status</label>
              <select name="is_active" class="form-select">
                <option value="1" {{ $user->is_active ? 'selected' : '' }}>Active</option>
                <option value="0" {{ !$user->is_active ? 'selected' : '' }}>Inactive</option>
              </select>
            </div>

            {{-- PERSONAL DETAILS --}}
            <div class="col-md-6 mb-3">
              <label class="form-label">Gender</label>
              <select name="gender" class="form-select">
                <option value="">Select</option>
                <option {{ ($user->detail->gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                <option {{ ($user->detail->gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                <option {{ ($user->detail->gender ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Date of Birth</label>
              <input type="date" name="dob" value="{{ $user->detail->date_of_birth ?? '' }}" class="form-control">
            </div>

            <div class="col-md-12 mb-3">
              <label class="form-label">Address</label>
              <textarea name="address" rows="2" class="form-control">{{ $user->detail->address ?? '' }}</textarea>
            </div>

            {{-- EMERGENCY --}}
            <div class="col-md-6 mb-3">
              <label class="form-label">Emergency Contact Name</label>
              <input type="text" name="emergency_contact_name"
                value="{{ $user->detail->emergency_contact_name ?? '' }}" class="form-control">
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Emergency Contact Phone</label>
              <input type="text" name="emergency_contact_phone"
                value="{{ $user->detail->emergency_contact_phone ?? '' }}" class="form-control">
            </div>

          </div>

          <div class="text-end">
            <button class="btn btn-primary px-4">
              <i class="bi bi-save"></i> Update Guard
            </button>
          </div>

        </form>

      </div>
    </div>

  </div>
@endsection
