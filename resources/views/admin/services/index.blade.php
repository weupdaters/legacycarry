@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">

    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Services</h4>
        </div>

        <div class="text-end">
            <a href="{{ route('services.create') }}" class="btn btn-success btn-sm">Add New Service</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <input type="text" id="search" class="form-control" placeholder="Search name, code or description">
                </div>
                <div class="col-md-3">
                    <input type="text" id="category" class="form-control" placeholder="Category">
                </div>
                <div class="col-md-2">
                    <select id="is_active" class="form-control">
                        <option value="">All status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button id="resetFilters" class="btn btn-secondary w-100">Reset</button>
                </div>
            </div>

            <div id="searchTable">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Active</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($services as $service)
                            <tr>
                                <td>{{ $service->id }}</td>
                                <td>{{ $service->name }}</td>
                                <td>{{ $service->category }}</td>
                                <td>{{ $service->is_active ? 'Yes' : 'No' }}</td>
                                <td class="">
                                    <a href="{{ route('services.edit', $service->id) }}"
                                       class="btn btn-warning btn-sm">Edit</a>

                                    <form action="{{ route('services.destroy', $service->id) }}"
                                          method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')

                                        <button onclick="return confirm('Delete this service?')"
                                                class="btn btn-danger btn-sm">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center">No services found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-between align-items-center mt-3 px-2">
                        <div>
                            Showing <strong>{{ $services->firstItem() }}</strong>
                            to <strong>{{ $services->lastItem() }}</strong> of
                            <strong>{{ $services->total() }}</strong> entries
                        </div>
                        <div>
                            {!! $services->onEachSide(2)->links('vendor.pagination.bootstrap-5') !!}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function() {
    // load data via AJAX (server returns full HTML -> we extract #searchTable)
    function loadData(url = "{{ route('services.index') }}") {
        $.ajax({
            url: url,
            type: 'GET',
            data: {
                search: $('#search').val(),
                category: $('#category').val(),
                is_active: $('#is_active').val()
            },
            success: function(res) {
                // if controller returns JSON with html
                if (res.html) {
                    $('#searchTable').html($(res.html).find('#searchTable').html());
                } else {
                    // fallback: response is HTML
                    $('#searchTable').html($(res).find('#searchTable').html());
                }
            }
        });
    }

    // debounce helper
    let debounceTimer;
    $('#search, #category, #is_active').on('keyup change', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => loadData(), 300);
    });

    $('#resetFilters').click(function() {
        $('#search, #category, #is_active').val('');
        loadData();
    });

    // pagination links
    $(document).on('click', '.pagination a', function(e){
        e.preventDefault();
        loadData($(this).attr('href'));
    });

    

   

});
</script>
@endpush
