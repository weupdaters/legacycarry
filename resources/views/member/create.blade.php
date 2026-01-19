@extends('layouts.app')

@section('content')

<!-- Start Content-->
<div class="container-fluid">

    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">General Elements</h4>
        </div>

        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Forms</a></li>
                <li class="breadcrumb-item active">General Elements</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-header">
                    <h5 class="card-title mb-0">Add User</h5>
                </div><!-- end card header -->

                <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="aadhaar_number" class="form-label">Aadhaar Number</label>
                                <input type="text" name="aadhaar_number" id="aadhaar_number" class="form-control" value="{{ old('aadhaar_number') }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="avatar" class="form-label">Avatar</label>
                                <input type="file" name="avatar" id="avatar" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="is_active" class="form-label">Active</label>
                                <select name="is_active" id="is_active" class="form-select" required>
                                    <option value="1" {{ old('is_active')=='1' ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ old('is_active')=='0' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select name="role" id="role" class="form-select" required>
                                    @foreach(['labor','customer','guard','super_admin'] as $role)
                                    <option value="{{ $role }}" {{ old('role')==$role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="verification_status" class="form-label">Verification Status</label>
                                <select name="verification_status" id="verification_status" class="form-select" required>
                                    @foreach(['unverified','pending','verified','rejected'] as $status)
                                    <option value="{{ $status }}" {{ old('verification_status')==$status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div><!-- end row -->
                    </div><!-- end card-body -->

                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-success">Submit User</button>
                    </div>
                </form>

            </div><!-- end card -->
        </div><!-- end col-12 -->
    </div><!-- end row -->
</div> <!-- container-fluid -->

@endsection
