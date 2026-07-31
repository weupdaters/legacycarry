@extends('admin.layouts.app')

@section('content')

<div class="container-fluid">

    <h4 class="fw-semibold py-3">
        Manage Buildings for <span class="text-primary">{{ $society->name }}</span>
    </h4>

    <div class="card">

        <form action="{{ route('flats.store') }}" method="POST">
            @csrf

            <input type="hidden" name="society_id" value="{{ $society->id }}">

            <div class="card-body">

                <div id="flatRows">

                    {{-- EXISTING BUILDINGS (EDIT MODE) --}}
                    @foreach($flats as $index => $flat)
                    <div class="row border rounded p-3 mb-3 flat-row">

                        <div class="col-md-3">
                            <label>Building Number</label>
                            <input type="hidden" name="flats[{{ $index }}][id]" value="{{ $flat->id }}">
                            <input type="text"
                                   name="flats[{{ $index }}][building_no]"
                                   class="form-control"
                                   value="{{ $flat->building_no }}"
                                   required>
                        </div>

                        <div class="col-md-4">
                            <label>Owner Name</label>
                            <input type="text"
                                   name="flats[{{ $index }}][owner_name]"
                                   class="form-control"
                                   value="{{ $flat->owner_name }}">
                        </div>

                        <div class="col-md-3">
                            <label>Status</label>
                            <select name="flats[{{ $index }}][status]" class="form-control">
                                <option value="occupied" {{ $flat->status == 'occupied' ? 'selected' : '' }}>Occupied</option>
                                <option value="vacant" {{ $flat->status == 'vacant' ? 'selected' : '' }}>Vacant</option>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm removeFlat">X</button>
                        </div>

                    </div>
                    @endforeach

                    {{-- IF NO BUILDINGS YET (FIRST TIME ADD) --}}
                    @if($flats->count() == 0)
                    <div class="row border rounded p-3 mb-3 flat-row">

                        <div class="col-md-3">
                            <label>Building Number</label>
                            <input type="text" name="flats[0][building_no]" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label>Owner Name</label>
                            <input type="text" name="flats[0][owner_name]" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Status</label>
                            <select name="flats[0][status]" class="form-control">
                                <option value="occupied" selected>Occupied</option>
                                <option value="vacant">Vacant</option>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm removeFlat">X</button>
                        </div>

                    </div>
                    @endif

                </div>

                <button type="button" id="addFlat" class="btn btn-outline-primary btn-sm">
                    + Add More
                </button>

            </div>

            <div class="card-footer text-end">
                <button class="btn btn-success">Save Buildings</button>
            </div>

        </form>
    </div>

</div>

@endsection


@push('scripts')
<script>
$(document).ready(function() {

    let flatIndex = {{ $flats->count() > 0 ? $flats->count() : 1 }};

    $("#addFlat").click(function() {

        // Create a fresh empty row template
        let newRow = `
        <div class="row border rounded p-3 mb-3 flat-row">

            <div class="col-md-3">
                <label>Building Number</label>
                <input type="text" name="flats[${flatIndex}][building_no]" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label>Owner Name</label>
                <input type="text" name="flats[${flatIndex}][owner_name]" class="form-control">
            </div>

            <div class="col-md-3">
                <label>Status</label>
                <select name="flats[${flatIndex}][status]" class="form-control">
                    <option value="occupied" selected>Occupied</option>
                    <option value="vacant">Vacant</option>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm removeFlat">X</button>
            </div>

        </div>`;

        $("#flatRows").append(newRow);

        flatIndex++;
    });

    $(document).on("click", ".removeFlat", function() {
        if ($(".flat-row").length > 1) {
            $(this).closest(".flat-row").remove();
        }
    });

});
</script>
@endpush
