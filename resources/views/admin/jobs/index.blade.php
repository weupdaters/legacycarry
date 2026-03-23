@extends('admin.layouts.app')

@section('content')

  <div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
      <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0">Job Posts Management</h4>
      </div>
      <div class="text-end">
        <a href="{{ route('jobs.export', ['type' => 'excel']) }}" class="btn btn-success btn-sm">Export Excel</a>
      </div>
    </div>

    <div class="row">
      <div class="col-12">

        <div class="card p-3 mb-3">
          <div class="row g-3 mb-4">

            <div class="col-md-3">
              <input type="text" id="search" class="form-control" placeholder="Search ID / Title / Location">
            </div>

            <div class="col-md-2">
              <select id="job_type" class="form-select">
                <option value="">Job Type</option>
                <option value="full_day">Full Day</option>
                <option value="part_time">Part Time</option>
                <option value="hourly">Hourly</option>
              </select>
            </div>

            <div class="col-md-2">
              <select id="pay_type" class="form-select">
                <option value="">Pay Type</option>
                <option value="per_hour">Per Hour</option>
                <option value="per_day">Per Day</option>
                <option value="fixed">Fixed</option>
              </select>
            </div>

            <div class="col-md-2">
              <select id="status" class="form-select">
                <option value="">Status</option>
                <option value="active">Active</option>
                <option value="closed">Closed</option>
                <option value="pending">Pending</option>
                <option value="complete">Complete</option>
                <option value="draft">Draft</option>
              </select>
            </div>

            {{-- <div class="col-md-1">
                        <input type="number" id="apps_min" class="form-control" placeholder="Min">
                    </div>

                    <div class="col-md-1">
                        <input type="number" id="apps_max" class="form-control" placeholder="Max">
                    </div> --}}

          </div>

          <!-- TABLE -->
          <div class="table-responsive" id="searchTable">

          @section('table')
            {{-- JOB TABLE --}}
            <div class="table table-striped">
              <table class="table">

                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Job Title</th>
                    <th>Location</th>
                    <th>Job Type</th>
                    <th>Pay</th>
                    <th>Status</th>
                    <th>Job Applications</th>
                    <th width="180">Action</th>
                  </tr>
                </thead>

                <tbody>
                  @forelse($jobs as $job)
                    <tr>
                      <td>{{ $job->id }}</td>

                      {{-- TITLE --}}
                      <td>{{ $job->job_title }}</td>

                      {{-- LOCATION --}}
                      <td>{{ $job->location ?? 'Unknown' }}</td>

                      {{-- JOB TYPE --}}
                      <td>{{ ucfirst($job->job_type) }}</td>

                      {{-- PAY INFO --}}
                      <td>
                        {{ ucfirst($job->pay_type) }} :
                        <strong>{{ number_format($job->pay_amount, 2) }}</strong>
                      </td>

                      {{-- STATUS --}}
                      <td>
                        @if ($job->status === 'active')
                          <span class="badge bg-success">Active</span>
                        @else
                          <span class="badge bg-warning">{{ ucfirst($job->status) }}</span>
                        @endif
                      </td>

                      {{-- JOB APPLICATIONS COUNT --}}
                      <td>
                        @php
                          $count = $job->applications->count();
                        @endphp

                        <a href="{{ route('jobs.applications', $job->id) }}" class="text-decoration-none">

                          {{-- IF APPLICATIONS EXIST --}}
                          @if ($count > 0)
                            <span class="badge rounded-pill px-3 py-2"
                              style="background:linear-gradient(135deg,#4fb0ff,#007bff); font-size:13px;">
                              <i class="bi bi-people-fill me-1"></i>
                              {{ $count }} Applications
                            </span>

                            {{-- IF ZERO APPLICATIONS --}}
                          @else
                            <span class="badge rounded-pill px-3 py-2"
                              style="background:#dcdcdc; color:#555; font-size:13px;">
                              <i class="bi bi-person-x me-1"></i>
                              No Applications
                            </span>
                          @endif

                        </a>
                      </td>



                      {{-- ACTION BUTTONS --}}
                      <td>

                        <a href="{{ route('jobs.view', $job->id) }}" class="btn btn-info btn-sm">
                          View
                        </a>

                        {{-- <a href="{{ route('jobs.edit', $job->id) }}" 
                                           class="btn btn-primary btn-sm">
                                            Edit
                                        </a> --}}

                        {{-- <form action="{{ route('jobs.destroy', $job->id) }}" 
                                              method="POST" 
                                              style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm"
                                                onclick="return confirm('Delete this job?');">
                                                Delete
                                            </button>
                                        </form> --}}
                      </td>
                    </tr>

                  @empty
                    <tr>
                      <td colspan="7" class="text-center text-muted">
                        No job posts found.
                      </td>
                    </tr>
                  @endforelse
                </tbody>

              </table>
            </div>
            {{-- PAGINATION --}}
            <div class="d-flex justify-content-between align-items-center mt-3 px-2">
              <div>
                Showing <strong>{{ $jobs->firstItem() }}</strong>
                to <strong>{{ $jobs->lastItem() }}</strong> of
                <strong>{{ $jobs->total() }}</strong> entries
              </div>

              <div>
                {!! $jobs->onEachSide(2)->links('vendor.pagination.bootstrap-5') !!}
              </div>
            </div>
          @endsection

          @yield('table')
        </div>

      </div>
    </div>

  </div>
</div>

</div>

@endsection

@push('scripts')
<script>
  function loadData(url = "{{ route('jobs.index') }}") {

    $.ajax({
      url: url,
      type: "GET",
      data: {
        search: $('#search').val(),
        job_type: $('#job_type').val(),
        pay_type: $('#pay_type').val(),
        status: $('#status').val(),
        apps_min: $('#apps_min').val(),
        apps_max: $('#apps_max').val(),
      },
      success: function(res) {
        $('#searchTable').html(res);
      }
    });
  }

  // Filters
  $('#search, #job_type, #pay_type, #status, #apps_min, #apps_max')
    .on('keyup change', function() {
      loadData();
    });

  // Pagination via AJAX
  $(document).on('click', '.pagination a', function(e) {
    e.preventDefault();
    loadData($(this).attr('href'));
  });
</script>
@endpush
