@extends('admin.layouts.app')

@section('content')
  <div class="container-fluid">

    {{-- HEADER --}}
    <div class="py-3 d-flex justify-content-between align-items-center">
      <h4 class="fw-semibold">
        <i class="bi bi-shield-lock-fill me-1"></i> Guards Management
      </h4>

      <div class="text-end">
        <a href="{{ route('guards.create') }}" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-circle"></i> Add Guard
        </a>

        <a href="{{ route('guards.export', ['type' => 'excel']) }}" class="btn btn-success btn-sm me-2">Export Excel</a>

      </div>
    </div>

    {{-- FILTERS --}}
    <div class="card p-3 mb-3">
      <div class="row g-3">
        <div class="col-md-4">
          <input type="text" id="search" class="form-control" placeholder="Search name, email, phone">
        </div>

        <div class="col-md-4">
          <select id="society_id" class="form-select">
            <option value="">All Societies</option>
            @foreach ($societies as $society)
              <option value="{{ $society->id }}">{{ $society->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3">
          <select id="status" class="form-select">
            <option value="">All Status</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>
      </div>
    </div>

    {{-- TABLE --}}
    <div id="searchTable">
      @include('admin.guards.partials.table')
    </div>

  </div>
@endsection

@push('scripts')
  <script>
    function loadGuards(url = "{{ route('guards.index') }}") {
      $.get(url, {
        search: $('#search').val(),
        status: $('#status').val(),
        society_id: $('#society_id').val()
      }, function(res) {
        $('#searchTable').html(res);
      });
    }

    $('#search, #status, #society_id').on('keyup change', function() {
      loadGuards();
    });

    $(document).on('click', '.pagination a', function(e) {
      e.preventDefault();
      loadGuards($(this).attr('href'));
    });

    function toggleStatus(id) {
      if (!confirm('Change status?')) return;

      $.post("{{ route('guards.toggleStatus') }}", {
        _token: "{{ csrf_token() }}",
        id: id
      }, function() {
        loadGuards();
      });
    }

    function deleteGuard(id) {
      if (!confirm('Delete guard permanently?')) return;

      $.ajax({
        url: "{{ url('admin/guards') }}/" + id,
        type: "DELETE",
        data: {
          _token: "{{ csrf_token() }}"
        },
        success: function() {
          loadGuards();
        }
      });
    }
  </script>
@endpush
