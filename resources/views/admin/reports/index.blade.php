@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">

  {{-- PAGE TITLE & ACTIONS --}}
  <div class="py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h4 class="fw-semibold m-0">
        <i class="bi bi-bug-fill text-danger me-1"></i> Reported Bugs & Support
      </h4>
      <span class="text-muted small">Manage user submitted bug reports, payment issues, and feedback</span>
    </div>

    <div class="d-flex gap-2">
      <a href="javascript:void(0)" id="exportBtn" class="btn btn-success btn-sm d-flex align-items-center">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
      </a>
    </div>
  </div>

  {{-- SUMMARY CARDS --}}
  <div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
      <div class="card shadow-sm border-0 h-100 cursor-pointer quick-filter-card" data-filter-type="" data-filter-status="" style="cursor: pointer;">
        <div class="card-body d-flex align-items-center">
          <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
            <i class="bi bi-folder2-open fs-4"></i>
          </div>
          <div>
            <h6 class="text-muted mb-1 text-uppercase" style="font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">Total Reports</h6>
            <h4 class="fw-bold mb-0">{{ $totalReports }}</h4>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3 col-sm-6">
      <div class="card shadow-sm border-0 h-100 cursor-pointer quick-filter-card" data-filter-type="Bug" data-filter-status="" style="cursor: pointer;">
        <div class="card-body d-flex align-items-center">
          <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
            <i class="bi bi-bug-fill fs-4"></i>
          </div>
          <div>
            <h6 class="text-muted mb-1 text-uppercase" style="font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">Bugs Reported</h6>
            <h4 class="fw-bold mb-0 text-danger">{{ $totalBugs }}</h4>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3 col-sm-6">
      <div class="card shadow-sm border-0 h-100 cursor-pointer quick-filter-card" data-filter-type="" data-filter-status="open" style="cursor: pointer;">
        <div class="card-body d-flex align-items-center">
          <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
            <i class="bi bi-clock-history fs-4"></i>
          </div>
          <div>
            <h6 class="text-muted mb-1 text-uppercase" style="font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">Open / Pending</h6>
            <h4 class="fw-bold mb-0 text-warning">{{ $openReports }}</h4>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3 col-sm-6">
      <div class="card shadow-sm border-0 h-100 cursor-pointer quick-filter-card" data-filter-type="" data-filter-status="resolved" style="cursor: pointer;">
        <div class="card-body d-flex align-items-center">
          <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
            <i class="bi bi-check2-circle fs-4"></i>
          </div>
          <div>
            <h6 class="text-muted mb-1 text-uppercase" style="font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">Resolved / Closed</h6>
            <h4 class="fw-bold mb-0 text-success">{{ $resolvedReports }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- FILTERS & TABLE CARD --}}
  <div class="card shadow-sm border-0 mb-4">
    
    {{-- FILTER HEADER --}}
    <div class="card-header bg-light py-3 border-bottom">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-bold text-dark d-flex align-items-center">
          <i class="bi bi-funnel-fill text-primary me-2"></i> Filter & Search Reports
        </span>
        <button type="button" id="reset_filters" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-arrow-counterclockwise me-1"></i> Reset All Filters
        </button>
      </div>
    </div>

    <div class="card-body p-3">

      {{-- FILTER FORM FIELDS --}}
      <div class="row g-2 mb-3">
        
        {{-- Search Input --}}
        <div class="col-lg-3 col-md-6">
          <label class="form-label small fw-semibold text-muted mb-1">Search</label>
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
            <input type="text" id="report_search" class="form-control border-start-0" placeholder="Ref, issue, email, user...">
          </div>
        </div>

        {{-- Report Type Filter --}}
        <div class="col-lg-2 col-md-3 col-sm-6">
          <label class="form-label small fw-semibold text-muted mb-1">Report Type</label>
          <select id="report_type_filter" class="form-select form-select-sm">
            <option value="">All Types</option>
            <option value="Bug">🐛 Bug</option>
            <option value="Payment">💳 Payment</option>
            <option value="Feedback">💬 Feedback</option>
            <option value="Account">👤 Account</option>
            <option value="Other">📌 Other</option>
          </select>
        </div>

        {{-- Status Filter --}}
        <div class="col-lg-2 col-md-3 col-sm-6">
          <label class="form-label small fw-semibold text-muted mb-1">Status</label>
          <select id="status_filter" class="form-select form-select-sm">
            <option value="">All Statuses</option>
            <option value="open">⏳ Open</option>
            <option value="in_progress">⚡ In Progress</option>
            <option value="resolved">✅ Resolved</option>
            <option value="closed">🔒 Closed</option>
          </select>
        </div>

        {{-- User Role Filter --}}
        <div class="col-lg-2 col-md-3 col-sm-6">
          <label class="form-label small fw-semibold text-muted mb-1">User Role</label>
          <select id="user_role_filter" class="form-select form-select-sm">
            <option value="">All Roles</option>
            <option value="customer">Resident / Customer</option>
            <option value="employee">Service Provider</option>
            <option value="super_admin">Administrator</option>
          </select>
        </div>

        {{-- Sort By Filter --}}
        <div class="col-lg-3 col-md-3 col-sm-6">
          <label class="form-label small fw-semibold text-muted mb-1">Sort By</label>
          <select id="sort_by_filter" class="form-select form-select-sm">
            <option value="latest">Newest First</option>
            <option value="oldest">Oldest First</option>
            <option value="type_asc">Report Type (A-Z)</option>
            <option value="type_desc">Report Type (Z-A)</option>
            <option value="status_asc">Status</option>
          </select>
        </div>

      </div>

      {{-- DATE RANGE & PER PAGE ROW --}}
      <div class="row g-2 mb-3 pt-2 border-top">
        <div class="col-lg-3 col-md-4 col-sm-6">
          <label class="form-label small fw-semibold text-muted mb-1">From Date</label>
          <input type="date" id="date_from" class="form-control form-control-sm">
        </div>

        <div class="col-lg-3 col-md-4 col-sm-6">
          <label class="form-label small fw-semibold text-muted mb-1">To Date</label>
          <input type="date" id="date_to" class="form-control form-control-sm">
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6">
          <label class="form-label small fw-semibold text-muted mb-1">Per Page</label>
          <select id="per_page_filter" class="form-select form-select-sm">
            <option value="10">10 entries</option>
            <option value="15" selected>15 entries</option>
            <option value="25">25 entries</option>
            <option value="50">50 entries</option>
            <option value="100">100 entries</option>
          </select>
        </div>
      </div>

      {{-- TABLE CONTAINER --}}
      <div id="reportsTableContainer">
        @include('admin.reports.partials.table', ['reports' => $reports])
      </div>

    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
  $(document).ready(function() {
    function getFilterData() {
      return {
        search: $('#report_search').val(),
        report_type: $('#report_type_filter').val(),
        status: $('#status_filter').val(),
        user_role: $('#user_role_filter').val(),
        date_from: $('#date_from').val(),
        date_to: $('#date_to').val(),
        sort_by: $('#sort_by_filter').val(),
        per_page: $('#per_page_filter').val()
      };
    }

    function fetchReports(url = "{{ route('admin.reports.index') }}") {
      const data = getFilterData();

      $.ajax({
        url: url,
        type: 'GET',
        data: data,
        beforeSend: function() {
          $('#reportsTableContainer').css('opacity', '0.5');
        },
        success: function(response) {
          $('#reportsTableContainer').html(response).css('opacity', '1');
        },
        error: function() {
          $('#reportsTableContainer').css('opacity', '1');
        }
      });
    }

    // Export with active filters
    $('#exportBtn').on('click', function(e) {
      e.preventDefault();
      const params = $.param(getFilterData());
      window.location.href = "{{ route('admin.reports.export') }}?" + params;
    });

    // Quick filter cards
    $('.quick-filter-card').on('click', function() {
      const type = $(this).data('filter-type');
      const status = $(this).data('filter-status');

      $('#report_type_filter').val(type);
      $('#status_filter').val(status);
      fetchReports();
    });

    // Search with debounce
    let searchTimer;
    $('#report_search').on('keyup', function() {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(function() {
        fetchReports();
      }, 400);
    });

    // Dropdown change handlers
    $('#report_type_filter, #status_filter, #user_role_filter, #date_from, #date_to, #sort_by_filter, #per_page_filter').on('change', function() {
      fetchReports();
    });

    // Reset filters
    $('#reset_filters').on('click', function() {
      $('#report_search').val('');
      $('#report_type_filter').val('');
      $('#status_filter').val('');
      $('#user_role_filter').val('');
      $('#date_from').val('');
      $('#date_to').val('');
      $('#sort_by_filter').val('latest');
      $('#per_page_filter').val('15');
      fetchReports();
    });

    // Pagination AJAX
    $(document).on('click', '#reportsTableContainer .pagination a', function(e) {
      e.preventDefault();
      const url = $(this).attr('href');
      fetchReports(url);
    });

    // Quick inline status change
    $(document).on('click', '.quick-status-btn', function(e) {
      e.preventDefault();
      const reportId = $(this).data('id');
      const newStatus = $(this).data('status');

      $.ajax({
        url: "{{ url('admin/reports') }}/" + reportId + "/status",
        type: 'POST',
        data: {
          _token: "{{ csrf_token() }}",
          _method: 'PATCH',
          status: newStatus
        },
        success: function(res) {
          fetchReports();
        },
        error: function(xhr) {
          alert('Failed to update status. Please try again.');
        }
      });
    });
  });
</script>
@endpush
