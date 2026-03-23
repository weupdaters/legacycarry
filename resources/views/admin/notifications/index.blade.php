@extends('admin.layouts.app')

@section('content')
  <div class="container-fluid">

    {{-- HEADER --}}
    <div class="py-3">
      <h4 class="fs-18 fw-semibold m-0">Notifications Management</h4>
    </div>

    <div class="card p-3 mb-3">
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <input type="text" id="search" class="form-control" placeholder="Search title / description / user id">
        </div>
      </div>

      <div class="table-responsive" id="searchTable">
        @include('admin.notifications.table')
      </div>
    </div>

  </div>
@endsection

@push('scripts')
  <script>
    function loadData(url = "{{ route('admin.notifications.index') }}") {
      $.ajax({
        url: url,
        type: "GET",
        data: {
          search: $('#search').val(),
        },
        success: function(res) {
          $('#searchTable').html(res);
        }
      });
    }

    $('#search').on('keyup', function() {
      loadData();
    });

    $(document).on('click', '.pagination a', function(e) {
      e.preventDefault();
      loadData($(this).attr('href'));
    });
  </script>
@endpush
