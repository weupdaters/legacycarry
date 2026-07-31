@extends('member.layouts.app')

@section('content')

<div class="container-fluid">

    <h4 class="fw-semibold py-3">
        Manage Buildings for <span class="text-primary">{{ $society->name }}</span>
    </h4>

    <div class="card shadow-sm border-0" style="border-radius: 15px;">

        <form action="{{ route('member.flats.store') }}" method="POST">
            @csrf

            <input type="hidden" name="society_id" value="{{ $society->id }}">

            <div class="card-body p-4">

                <div id="flatRows">

                    {{-- EXISTING BUILDINGS (EDIT MODE) --}}
                    @foreach($flats as $index => $flat)
                    <div class="row border rounded p-3 mb-3 flat-row bg-light align-items-center">

                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="form-label fw-bold small">Building Number</label>
                            <input type="text"
                                   name="flats[{{ $index }}][building_no]"
                                   class="form-control"
                                   value="{{ $flat->building_no }}"
                                   placeholder="e.g. 101 tower A"
                                   required>
                        </div>

                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="form-label fw-bold small">Owner Name</label>
                            <input type="text"
                                   name="flats[{{ $index }}][owner_name]"
                                   class="form-control"
                                   placeholder="Enter owner name"
                                   value="{{ $flat->owner_name }}">
                        </div>

                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="form-label fw-bold small">Status</label>
                            <select name="flats[{{ $index }}][status]" class="form-select">
                                <option value="occupied" {{ $flat->status == 'occupied' ? 'selected' : '' }}>Occupied</option>
                                <option value="vacant" {{ $flat->status == 'vacant' ? 'selected' : '' }}>Vacant</option>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end justify-content-md-end">
                            <button type="button" class="btn btn-danger btn-sm removeFlat px-3">X</button>
                        </div>

                    </div>
                    @endforeach

                    {{-- IF NO BUILDINGS YET (FIRST TIME ADD) --}}
                    @if($flats->count() == 0)
                    <div class="row border rounded p-3 mb-3 flat-row bg-light align-items-center">

                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="form-label fw-bold small">Building Number</label>
                            <input type="text" name="flats[0][building_no]" class="form-control" placeholder="e.g. 101 tower A" required>
                        </div>

                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="form-label fw-bold small">Owner Name</label>
                            <input type="text" name="flats[0][owner_name]" class="form-control" placeholder="Enter owner name">
                        </div>

                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="form-label fw-bold small">Status</label>
                            <select name="flats[0][status]" class="form-select">
                                <option value="occupied" selected>Occupied</option>
                                <option value="vacant">Vacant</option>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end justify-content-md-end">
                            <button type="button" class="btn btn-danger btn-sm removeFlat px-3">X</button>
                        </div>

                    </div>
                    @endif

                </div>

                <button type="button" id="addFlat" class="btn btn-outline-primary btn-sm mt-2 px-3">
                    <i class="bi bi-plus-lg me-1"></i> Add More
                </button>

            </div>

            <div class="card-footer bg-white p-4 text-end border-0">
                <button class="btn btn-success px-4 py-2 fw-bold" style="border-radius: 10px;">
                    <i class="bi bi-check2-circle me-1"></i> Save Buildings
                </button>
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
        <div class="row border rounded p-3 mb-3 flat-row bg-light align-items-center">

            <div class="col-md-3 mb-2 mb-md-0">
                <label class="form-label fw-bold small">Building Number</label>
                <input type="text" name="flats[${flatIndex}][building_no]" class="form-control" placeholder="e.g. 101 tower A" required>
            </div>

            <div class="col-md-4 mb-2 mb-md-0">
                <label class="form-label fw-bold small">Owner Name</label>
                <input type="text" name="flats[${flatIndex}][owner_name]" class="form-control" placeholder="Enter owner name">
            </div>

            <div class="col-md-3 mb-2 mb-md-0">
                <label class="form-label fw-bold small">Status</label>
                <select name="flats[${flatIndex}][status]" class="form-select">
                    <option value="occupied" selected>Occupied</option>
                    <option value="vacant">Vacant</option>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end justify-content-md-end">
                <button type="button" class="btn btn-danger btn-sm removeFlat px-3">X</button>
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
