@extends('admin.layouts.app')

@section('content')
  <style>
    .status-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 12px;
    }

    .status-applied {
      background: #e1efff;
      color: #0d6efd;
    }

    .status-hired {
      background: #e7fbe9;
      color: #2e9e3f;
    }

    .status-completed {
      background: #fff3cd;
      color: #b8860b;
    }

    .status-cancel {
      background: #ffe3e3;
      color: #d9534f;
    }
  </style>

  <div class="container-fluid">

    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
      <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0">
          <i class="bi bi-people-fill me-1"></i>
          Applications for: {{ $job->job_title }}
        </h4>
      </div>

      <div class="text-end">
        <a href="{{ route('jobs.index') }}" class="btn btn-secondary btn-sm">
          <i class="bi bi-arrow-left-circle me-1"></i> Back to Jobs
        </a>
        {{-- <a href="{{ route('admin.jobs.applications.export', $job->id) }}" class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Applications
            </a> --}}
      </div>
    </div>


    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">

            @if ($applications->count() == 0)
              <p class="text-muted text-center py-4">
                <i class="bi bi-inbox text-secondary" style="font-size:32px;"></i><br>
                No Applications yet.
              </p>
            @else
              <!-- FILTERS -->
              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label class="filter-label">Search Applicant</label>
                  <input type="text" id="search" class="form-control" placeholder="Search name, phone, email...">
                </div>

                <div class="col-md-3">
                  <label class="filter-label">Status</label>
                  <select id="status" class="form-select">
                    <option value="">All</option>
                    <option value="applied">Applied</option>
                    <option value="hired">Hired</option>
                    <option value="completed">Completed</option>
                    <option value="cancel">Cancelled</option>
                  </select>
                </div>
              </div>

              <!-- TABLE WRAPPER -->
              <div id="applicationsTable">
                @yield('applicationsTable')
              </div>
            @endif

          </div>
        </div>
      </div>
    </div>

  </div>
@endsection


<!-- TABLE SECTION (for AJAX rendering) -->
@section('applicationsTable')
  <table class="table table-bordered align-middle">
    <thead>
      <tr>
        <th>#</th>
        <th>Applicant</th>
        <th>Contact</th>
        <th>Expected Cost</th>
        <th>Status</th>
        <th>Shortlisted</th>
        <th>Message</th>
        <th>Certificate</th>
        <th>Applied On</th>
        <th>Chat</th>
      </tr>
    </thead>

    <tbody>
      @foreach ($applications as $app)
        <tr>
          <td>{{ $app->id }}</td>

          <td>
            <strong>{{ $app->user->name ?? '-' }}</strong><br>
            <small class="text-muted">User ID: {{ $app->user_id }}</small>
          </td>

          <td>
            <i class="bi bi-telephone-outbound me-1"></i>
            {{ $app->user->phone ?? '-' }} <br>
            <i class="bi bi-envelope me-1"></i>
            {{ $app->user->email ?? '-' }}
          </td>

          <td>₹{{ number_format($app->expected_cost ?? 0, 2) }}</td>

          <td>
            @php
              $statusClass =
                  [
                      'applied' => 'status-applied',
                      'hired' => 'status-hired',
                      'completed' => 'status-completed',
                      'cancel' => 'status-cancel',
                  ][$app->status] ?? 'status-applied';
            @endphp

            <span class="status-badge {{ $statusClass }}">
              {{ ucfirst($app->status) }}
            </span>
          </td>

          <td>
            @if ($app->shortlisted)
              <span class="badge bg-success">Yes</span>
            @else
              <span class="badge bg-secondary">No</span>
            @endif
          </td>

          <td style="max-width:250px;">
            {{ $app->message_to_employer ?? '-' }}
          </td>

          <td>
            @if ($app->certificate_photo)
              <a href="{{ asset('uploads/certificates/' . $app->certificate_photo) }}" target="_blank"
                class="btn btn-sm btn-outline-primary">
                View
              </a>
          <td>
            <a href="{{ route('applications.chat', $app->id) }}" class="btn btn-sm btn-outline-primary">
              <i class="bi bi-chat-dots"></i> Chat
            </a>
          </td>
        @else
          <span class="text-muted">None</span>
      @endif
      </td>

      <td>
        <a href="{{ route('applications.chat', $app->id) }}" class="btn btn-sm btn-outline-primary">
          <i class="bi bi-chat-dots"></i> Chat
        </a>
      </td>

      <td>{{ $app->created_at->format('d M Y, h:i A') }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="d-flex justify-content-between align-items-center mt-3 px-2">
    <div>
      Showing <strong>{{ $applications->firstItem() }}</strong>
      to <strong>{{ $applications->lastItem() }}</strong> of
      <strong>{{ $applications->total() }}</strong> entries
    </div>

    <div>
      {!! $applications->onEachSide(2)->links('vendor.pagination.bootstrap-5') !!}
    </div>
  </div>
@endsection
<!-- END TABLE SECTION -->


@push('scripts')
  <script>
    function loadApplications(url = "{{ route('jobs.applications', $job->id) }}") {
      $.ajax({
        url: url,
        type: "GET",
        data: {
          search: $('#search').val(),
          status: $('#status').val()
        },
        success: function(response) {
          $('#applicationsTable').html(response);
        }
      });
    }

    $(document).ready(function() {
      loadApplications(); // load table on page load

      $('#search').on('keyup', function() {
        loadApplications();
      });

      $('#status').on('change', function() {
        loadApplications();
      });

      $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        loadApplications($(this).attr('href'));
      });

    });
  </script>
@endpush
