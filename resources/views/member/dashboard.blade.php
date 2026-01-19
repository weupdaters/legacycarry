@extends('member.layouts.app')

@section('title', 'Resident Dashboard')

@section('content')

  <div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="py-3 d-flex align-items-center">
      <h4 class="fs-18 fw-semibold m-0">My Society Dashboard</h4>
    </div>

    {{-- QUICK STATS --}}
    <div class="row">
      <div class="col-md-3">
        <div class="card shadow-sm">
          <div class="card-body text-center">
            <h3>{{ $totalEmployees }}</h3>
            <p class="text-muted">Total Residents in Society</p>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card shadow-sm">
          <div class="card-body text-center">
            <h3>{{ $activeJobs }}</h3>
            <p class="text-muted">Active Residents</p>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card shadow-sm">
          <div class="card-body text-center">
            <h3>{{ $closedJobs }}</h3>
            <p class="text-muted">Closed Residents</p>
          </div>
        </div>
      </div>

      {{-- <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h3>{{ $onHoldJobs }}</h3>
                    <p class="text-muted">Residents On Hold</p>
                </div>
            </div>
        </div> --}}
    </div>

    {{-- Residents LIST --}}
    <div class="row mt-4">
      <div class="col-12">

        <div class="card shadow-sm">
          <div class="card-header">
            <h5 class="card-title mb-0">Residents in Your Society</h5>
          </div>

          <div class="table-responsive">
            <table class="table mb-0">
              <thead>
                <tr>
                  <th>Residents</th>
                  <th>Email</th>
                </tr>
              </thead>

              <tbody>
                @foreach ($employees as $emp)
                  <tr>
                    <td>
                      <img src="{{ asset($emp->avatar ?? 'assets/images/users/default.png') }}"
                        class="avatar avatar-sm rounded-circle me-2">
                      {{ $emp->user->name }}
                    </td>

                    <td>{{ $emp->user->email }}</td>



                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="px-3 py-2">
            {{ $employees->links('pagination::bootstrap-5') }}
          </div>
        </div>
      </div>
    </div>

  </div>

@endsection
