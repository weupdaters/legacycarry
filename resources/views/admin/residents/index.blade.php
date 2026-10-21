@extends('admin.layouts.app')

@section('content')
  <!-- Start Content-->
  <div class="container-fluid">

    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
      <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0">Residents Management </h4>
      </div>

      <div class="text-end">
        <a href="{{ route('residents.export', ['type' => 'excel']) }}" class="btn btn-success btn-sm">Export Excel</a>
      </div>
    </div>


    <div class="row">
      <div class="col-12">
        <div class="card">

          <div class="card-body">

            <!-- SEARCH + FILTER -->
            <div class="row g-3 mb-4">

              <!-- GLOBAL SEARCH -->
              <div class="col-md-5">
                <label class="filter-label">Search</label>
                <input type="text" id="search" class="form-control" placeholder="Search name, phone, email, society...">
              </div>

              <!-- SOCIETY FILTER -->
              <div class="col-md-4">
                <label class="filter-label">Society</label>
                <select id="society_id" class="form-select">
                  <option value="">All Societies</option>
                  @foreach ($societies ?? [] as $soc)
                    <option value="{{ $soc->id }}">{{ $soc->name }}</option>
                  @endforeach
                </select>
              </div>

              <!-- VERIFICATION STATUS -->
              <div class="col-md-3">
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
            <div class="table-responsive" id="residentsTable">

            @section('table')
              <div class="table table-striped">
                <table class="table">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Phone</th>
                      <th>Email</th>
                      <th>Society</th>
                      <th>Resident Type</th>
                      <th>Verification</th>
                      <th>Flat</th>
                      <th>Village/Town</th>
                      <th>Active</th>
                      <th>Action</th>
                    </tr>
                  </thead>

                  <tbody>
                    @foreach ($residents as $resident)
                      @php
                        $detail = $resident->details ?? null;
                        $societyName = $detail->society->name 
                            ?? $resident->society->name 
                            ?? $resident->residentAssignment->society->name 
                            ?? assingedSocietyName($resident->id) 
                            ?? null;
                      @endphp

                      <tr>
                        <td>{{ $resident->id }}</td>
                        <td>{{ $resident->name }}</td>
                        <td>{{ $resident->phone }}</td>
                        <td>{{ $resident->email }}</td>
                        <td>
                          @if ($societyName)
                            <span class="badge bg-soft-primary text-primary fw-semibold">{{ $societyName }}</span>
                          @else
                            <span class="text-muted">-</span>
                          @endif
                        </td>
                        <td>{{ $resident->user_residential_status }}</td>

                        <td>
                          @if ($resident->verification_status == 'verified')
                            <span class="badge bg-success">Verified</span>
                          @elseif($resident->verification_status == 'pending')
                            <span class="badge bg-warning text-dark">Pending</span>
                          @elseif($resident->verification_status == 'rejected')
                            <span class="badge bg-danger">Rejected</span>
                          @else
                            <span class="badge bg-secondary">Not Verified</span>
                          @endif
                        </td>

                        <td>{{ $detail->flat->building_no ?? '-' }}</td>
                        <td>{{ $detail->address ?? '-' }}</td>

                        <td>
                          @if ($resident->is_active == 1)
                            <span class="badge bg-success">Active</span>
                          @elseif ($resident->is_active == 2)
                            <span class="badge bg-warning text-dark">Suspended</span>
                          @else
                            <span class="badge bg-secondary">Inactive</span>
                          @endif
                        </td>

                        <td>
                          <a href="{{ route('residents.view', $resident->id) }}" class="btn btn-info btn-sm">View</a>

                          {{-- <a href="{{ route('residents.edit', $resident->id) }}" class="btn btn-primary btn-sm">Edit</a> --}}

                          <form action="{{ route('users.toggle-ban', $resident->id) }}" method="POST"
                            style="display:inline-block;">
                            @csrf
                            @method('PATCH')
                            <select name="status" onchange="this.form.submit()" class="form-select form-select-sm">
                              <option value="1" {{ $resident->is_active == 1 ? 'selected' : '' }}>Active</option>
                              <option value="0" {{ $resident->is_active == 0 ? 'selected' : '' }}>Inactive</option>
                              <option value="2" {{ $resident->is_active == 2 ? 'selected' : '' }}>Suspended</option>
                            </select>
                          </form>

                          {{-- <form action="{{ route('users.destroy', $resident->id) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Are you sure you want to delete this user?');">
                                                    Delete
                                                </button>
                                            </form> --}}
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>

                {{-- PAGINATION --}}
                <div class="d-flex justify-content-between align-items-center mt-3 px-2">

                  <div>
                    Showing<strong>{{ $residents->firstItem() }}</strong>
                    to<strong>{{ $residents->lastItem() }}</strong>of
                    <strong>{{ $residents->total() }}</strong>entries
                  </div>

                  <div>
                    {!! $residents->onEachSide(2)->links('vendor.pagination.bootstrap-5') !!}
                  </div>

                </div>
              </div>
            @endsection

            @yield('table')

          </div><!-- end table -->

        </div><!-- end card -->
      </div><!-- end col-12 -->
    </div><!-- end row -->
  </div><!-- end container-fluid -->
@endsection



@push('scripts')
  <script>
    function loadData(url = "{{ route('residents.index') }}") {

      $.ajax({
        url: url,
        type: "GET",
        data: {
          search: $('#search').val(),
          status: $('#status').val(),
          society_id: $('#society_id').val()
        },
        success: function(response) {

          // Replace only table content
          $('#residentsTable').html(
            $(response).find('#residentsTable').html()
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

      // Society dropdown
      $('#society_id').on('change', function() {
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
