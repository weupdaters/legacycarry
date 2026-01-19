@extends('admin.layouts.app')

@section('content')

<div class="container-fluid">

    <div class="py-3 d-flex justify-content-between align-items-center">
        <h4 class="fw-semibold m-0">Add Society</h4>
    </div>

    {{-- DATALIST for searchable cities --}}
    <datalist id="citiesList">
        @foreach($cities as $city)
            <option data-id="{{ $city->id }}" value="{{ $city->name }}"></option>
        @endforeach
    </datalist>

    <div class="card">
        <form action="{{ route('societies.store') }}" method="POST">
            @csrf

            <div class="card-body">

                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">Society Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    {{-- SEARCHABLE CITY FIELD --}}
                    <div class="col-md-4">
                        <label class="form-label">City (Search)</label>

                        <input type="text"
                               list="citiesList"
                               class="form-control cityInput"
                               placeholder="Type city name..."
                               autocomplete="off"
                               autocorrect="off"
                               autocapitalize="none"
                               spellcheck="false">

                        <!-- actual city_id goes here -->
                        <input type="hidden" name="city_id" class="cityID" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Pincode</label>
                        <input type="text" name="pincode" class="form-control">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>

                </div>

            </div>

            <div class="card-footer text-end">
                <button class="btn btn-success">Save Society</button>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
$(document).ready(function() {

    // Convert typed city name → city_id
    $(".cityInput").on("input", function () {

        let typed = $(this).val().toLowerCase();
        let cityId = null;

        // Match with datalist options
        $("#citiesList option").each(function () {
            if ($(this).val().toLowerCase() === typed) {
                cityId = $(this).data("id");
            }
        });

        // Update hidden city_id field
        $(".cityID").val(cityId);
    });

});
</script>

@endpush


@endsection