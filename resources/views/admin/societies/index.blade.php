@extends('admin.layouts.app')

@section('content')

<div class="container-fluid">

    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Society Management</h4>
        </div>

        <div class="text-end">
            <a href="{{ route('societies.create') }}" class="btn btn-success btn-sm">Add New Society</a>
            <a href="{{ route('societies.multiple.create') }}" class="btn btn-primary btn-sm">Add Multiple Societies</a>
            <a href="{{ route('societies.export', ['type' => 'excel']) }}" class="btn btn-success btn-sm">Export Excel</a>
        </div>
    </div>


    <div class="row">
        <div class="col-12">

            <div class="card">
                <div class="card-body">

                   

                    {{-- FILTERS --}}
                    <div class="row g-3 mb-4">

                        <div class="col-md-3">
                            <input type="text" id="search" class="form-control" placeholder="Search name or address...">
                        </div>

                        <div class="col-md-2">
                            <input type="text" id="city" class="form-control" placeholder="City">
                        </div>

                        <div class="col-md-2">
                            <input type="text" id="state" class="form-control" placeholder="State">
                        </div>

                        <div class="col-md-2">
                            <input type="text" id="pincode" class="form-control" placeholder="Pincode">
                        </div>

                        <div class="col-md-2">
                            <button class="btn btn-secondary w-100" id="resetFilters">Reset</button>
                        </div>

                    </div>

                    {{-- AJAX AREA --}}
                    

               
                <!-- TABLE -->
                <div class="table-responsive" id="searchTable">
    
                    @section('table')
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Flats</th>
                                    <th>City</th>
                                    <th>State</th>
                                    <th>Pincode</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>

                                <tbody>
                                @forelse($societies as $society)
                                    <tr>
                                        <td>{{ $society->id }}</td>
                                        <td>{{ $society->name }}</td>
                                        <td>{{ $society->address }}</td>
                                        <td>{{ $society->flats->pluck('building_no')->join(', ') }}</td>
                                        <td>{{ $society->city->name ?? '—' }}</td>
                                        <td>{{ $society->state->name ?? '—' }}</td>
                                        <td>{{ $society->pincode }}</td>

                                        <td>
                                            <a href="{{ route('flats.create', $society->id) }}" class="btn btn-sm btn-success">
                                                Manage Buildings
                                            </a>

                                            <a href="{{ route('societies.edit', $society->id) }}" class="btn btn-sm btn-warning">
                                                Edit
                                            </a>

                                            <form action="{{ route('societies.destroy', $society->id) }}" 
                                                  method="POST" style="display:inline-block;">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Delete this society?')">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No societies found.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>

                            {{-- Pagination --}}
                            <div class="d-flex justify-content-between align-items-center mt-3 px-2">
                                <div>
                                    Showing <strong>{{ $societies->firstItem() }}</strong>
                                    to <strong>{{ $societies->lastItem() }}</strong> of
                                    <strong>{{ $societies->total() }}</strong> entries
                                </div>

                                <div>
                                    {!! $societies->onEachSide(2)->links('vendor.pagination.bootstrap-5') !!}
                                </div>
                            </div>

                            @endsection

                            @yield('table')
                        </div>

                    
                    {{-- END AJAX AREA --}}

                </div>
            </div>

        </div>
    </div>

</div>

@endsection



@push('scripts')
<script>

function loadData(url = "{{ route('societies.index') }}") {

    $.ajax({
        url: url,
        type: "GET",
        data: {
            search: $('#search').val(),
            city: $('#city').val(),
            state: $('#state').val(),
            pincode: $('#pincode').val(),
        },
        success: function (response) {
            
            $('#searchTable').html(response);
          

        }
    });
}


$(document).ready(function () {

$('#search, #city, #state, #pincode').on('keyup change', function () {
    loadData();
});

$('#resetFilters').click(function () {
    $('#search, #city, #state, #pincode').val('');
    loadData();
});

// AJAX pagination
$(document).on('click', '.pagination a', function(e) {
    e.preventDefault();
    loadData($(this).attr('href'));
});

});

</script>
@endpush