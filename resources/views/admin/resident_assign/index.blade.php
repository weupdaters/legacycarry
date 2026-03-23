@extends('admin.layouts.app')

@section('content')
  <!-- Start Content-->
  <div class="container-fluid">

    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
      <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0"> Assigned Residents Management
        </h4>

        <div class="text-end">
          <a href="{{ route('resident.assign.create') }}" class="btn btn-success btn-sm">Assign New Resident</a>
          <a href="{{ route('resident.assign.export', ['type' => 'excel']) }}" class="btn btn-success btn-sm">Export
            Excel</a>
        </div>
      </div>
    </div>


    <div class="row">
      <div class="col-12">
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
                <label class="filter-label">Status</label>
                <select id="status" class="form-select">
                  <option value="">All</option>
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
            </div>

            <!-- TABLE -->
            <div class="table-responsive" id="searchTable">

            @section('table')
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Society</th>
                    <th>Status</th>
                    <th width="240">Action</th>
                  </tr>
                </thead>

                <tbody>
                  @foreach ($assignments as $assignment)
                    @php
                      $user = $assignment->user;
                      $society = $assignment->society;
                    @endphp

                    <tr>
                      <td>{{ $user->id }}</td>

                      <td>
                        <strong>{{ $user->name }}</strong><br>
                        <small class="text-muted">Assign ID: {{ $assignment->id }}</small>
                      </td>

                      <td>{{ $user->phone ?? '-' }}</td>
                      <td>{{ $user->email }}</td>

                      <td>{{ $society->name }}</td>

                      {{-- STATUS --}}
                      <td>
                        @if ($user->is_active)
                          <span class="badge bg-success">Active</span>
                        @else
                          <span class="badge bg-danger">Banned</span>
                        @endif
                      </td>

                      {{-- ACTION BUTTONS --}}
                      <td class="d-flex gap-2">

                        {{-- EDIT --}}
                        <a href="{{ route('resident.assign.edit', $assignment->id) }}" class="btn btn-primary btn-sm ">
                          Edit
                        </a>

                        {{-- BAN --}}
                        @if ($user->is_active)
                          <form action="{{ route('resident.assign.ban', $assignment->id) }}" method="POST">
                            @csrf @method('PUT')
                            <button type="submit" class="btn btn-warning btn-sm ">
                              Ban
                            </button>
                          </form>
                        @else
                          {{-- UNBAN --}}
                          <form action="{{ route('resident.assign.unban', $assignment->id) }}" method="POST">
                            @csrf @method('PUT')
                            <button type="submit" class="btn btn-success btn-sm ">
                              Unban
                            </button>
                          </form>
                        @endif

                        {{-- DELETE ASSIGNMENT --}}
                        <form action="{{ route('resident.assign.delete', $assignment->id) }}" method="POST">
                          @csrf @method('DELETE')
                          <button type="submit" class="btn btn-danger btn-sm d-flex align-items-center"
                            onclick="return confirm('Delete this assignment?')">
                            Delete
                          </button>
                        </form>

                        <a href="{{ route('admin.permissions.edit', $user->id) }}" class="btn btn-sm btn-primary">
                          Manage Permission
                        </a>
                      </td>

                    </tr>
                  @endforeach
                </tbody>
              </table>

              {{-- PAGINATION --}}
              <div class="d-flex justify-content-between align-items-center mt-3 px-2">
                <div>
                  Showing <strong>{{ $assignments->firstItem() }}</strong>
                  to <strong>{{ $assignments->lastItem() }}</strong> of
                  <strong>{{ $assignments->total() }}</strong> entries
                </div>

                <div>
                  {!! $assignments->onEachSide(2)->links('vendor.pagination.bootstrap-5') !!}
                </div>
              </div>
            @endsection

            @yield('table')

          </div>
        </div>
      </div>
    </div>

  </div>



@endsection


@push('scripts')
  <script>
    function loadData(url = "{{ route('resident.assign.index') }}") {

      $.ajax({
        url: url,
        type: "GET",
        data: {
          search: $('#search').val(),
          status: $('#status').val()
        },
        success: function(response) {

          // Replace only table content
          $('#searchTable').html(
            $(response).find('#searchTable').html()
          );

        }
      });
    }

    $(document).ready(function() {

      // Search box
      $('#search').on('keyup', function() {
        loadData();
      });

      // Status dropdown
      $('#status').on('change', function() {
        loadData();
      });

      // Pagination handling
      $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        loadData($(this).attr('href'));
      });

    });
  </script>
@endpush
