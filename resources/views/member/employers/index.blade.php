@extends('member.layouts.app')

@section('content')

  <style>
    .employee-badge {
      background: #eef5ff;
      padding: 5px 10px;
      border-radius: 12px;
      color: #0b74d8;
      font-weight: 600;
      font-size: 12px;
    }
  </style>

  <div class="container-fluid">

    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
      <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0">
          <i class="bi bi-building-fill me-1"></i> Service Providers Management List
        </h4>
      </div>

      {{-- <div class="text-end">
        <a href="{{ route('employees.export', ['type' => 'excel']) }}" class="btn btn-success btn-sm">Export Excel</a>
      </div> --}}
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-body">

        <div class="row g-3 mb-4">
          <!-- GLOBAL SEARCH -->
          <div class="col-md-8">
            <label class="filter-label">Search</label>
            <input type="text" id="search" class="form-control" placeholder="Search name, phone, email...">
          </div>

          <!-- VERIFICATION STATUS -->
          <div class="col-md-4">
            <label class="filter-label">Verification</label>
            <select id="status" class="form-select">
              <option value="">All</option>
              <option value="unverified">Not Verified</option>
              <option value="pending">Pending</option>
              <option value="verified">Verified</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>
        </div>

        <!-- TABLE -->
        <div class="table-responsive" id="employeeTable">

        @section('table')
          <table class="table table-striped">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Contact</th>
                <th>Verification</th>
                <th>Status</th>
                <th>Experience</th>
                <th>Services</th>
                <th>Availability</th>
                <th>Hourly Rate</th>
                <th class="text-center">Jobs Pending</th>
                <th class="text-center">Jobs Active</th>
                <th class="text-center">Jobs Completed</th>
                <th width="180">Action</th>
              </tr>
            </thead>

            <tbody>
              @foreach ($employers as $emp)
                <tr>
                  <td>{{ $emp->id }}</td>

                  <td>
                    <strong>{{ $emp->name }}</strong><br>
                    <small class="text-muted">User ID: {{ $emp->id }}</small>
                  </td>

                  <td>
                    <i class="bi bi-telephone"></i> {{ $emp->phone ?? '-' }} <br>
                    <i class="bi bi-envelope"></i> {{ $emp->email }}
                  </td>

                  <td>
                    @if ($emp->verification_status == 'verified')
                      <span class="badge bg-success">Verified</span>
                    @elseif($emp->verification_status == 'pending')
                      <span class="badge bg-warning text-dark">Pending</span>
                    @elseif($emp->verification_status == 'rejected')
                      <span class="badge bg-danger">Rejected</span>
                    @else
                      <span class="badge bg-secondary">Not Verified</span>
                    @endif
                  </td>

                  <td>
                    @if ($emp->is_active)
                      <span class="badge bg-success">Active</span>
                    @else
                      <span class="badge bg-danger">Banned</span>
                    @endif
                  </td>

                  <td>{{ $emp->employee?->experience_years ? $emp->employee->experience_years . ' Years' : '-' }}</td>

                  <td>
                    @if ($emp->employee)
                      @php
                        $services = $emp->employee->services;
                        $services = is_string($services) ? json_decode($services, true) : $services;
                      @endphp
                      {{ !empty($services) ? implode(', ', $services) : '-' }}
                    @else
                      -
                    @endif
                  </td>

                  <td>{{ $emp->employee->availability ?? '-' }}</td>

                  <td>
                    @if ($emp->employee)
                      ₹{{ number_format($emp->employee->hourly_rate, 2) }}
                    @else
                      -
                    @endif
                  </td>

                  <td class="text-center">
                    <span class="badge bg-warning text-dark">{{ $emp->pending_jobs_count }}</span>
                  </td>

                  <td class="text-center">
                    <span class="badge bg-primary">{{ $emp->active_jobs_count }}</span>
                  </td>

                  <td class="text-center">
                    <span class="badge bg-success">{{ $emp->completed_jobs_count }}</span>
                  </td>

                  <td>
                    {{-- Ban / Unban --}}
                    <form action="{{ route('member.toggle-ban', $emp->id) }}" method="POST"
                      style="display:inline-block;">
                      @csrf
                      @method('PATCH')
                      <button type="submit" class="btn btn-sm {{ $emp->is_active ? 'btn-warning' : 'btn-success' }}">

                        {{ $emp->is_active ? 'Ban' : 'Unban' }}
                      </button>
                    </form>


                    <a href="{{ route('member.employees.view', $emp->id) }}" class="btn btn-info btn-sm">View</a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>


          <div class="d-flex justify-content-between align-items-center mt-3 px-2">

            {{ $employers->links() }}

          </div>
        @endsection

        @yield('table')

      </div>

    </div>
  </div>
</div>

@endsection




@push('scripts')
<script>
  function loadData(url = "{{ route('member.employees.index') }}") {

    $.ajax({
      url: url,
      type: "GET",
      data: {
        search: $('#search').val(),
        status: $('#status').val(),
        availability: $('#availability').val(),
        experience: $('#experience').val(),
        min_rate: $('#min_rate').val(),
        max_rate: $('#max_rate').val()
      },
      success: function(response) {
        $('#employeeTable').html(response);
      }
    });
  }

  $(document).ready(function() {

    // Search box
    $('#search').on('keyup', function() {
      loadData();
    });

    // Filter dropdowns
    $('#status, #availability, #experience, #min_rate, #max_rate')
      .on('change keyup', function() {
        loadData();
      });

    // Pagination
    $(document).on('click', '.pagination a', function(e) {
      e.preventDefault();
      loadData($(this).attr('href'));
    });

  });
</script>
@endpush
