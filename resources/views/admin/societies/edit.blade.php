@extends('admin.layouts.app')

@section('content')

<style>
    #noMatchMsg {
        font-size: 12px;
    }
</style>

<div class="container-fluid">

    <div class="py-3 d-flex justify-content-between align-items-center">
        <h4 class="fw-semibold m-0">
            {{ isset($society) ? 'Edit Society' : 'Add Society' }}
        </h4>

        <a href="{{ route('societies.index') }}" class="btn btn-secondary btn-sm">Back</a>
    </div>

    {{-- DATALIST: CITY SEARCH --}}
    <datalist id="citiesList">
        @foreach($cities as $city)
            <option data-id="{{ $city->id }}" value="{{ $city->name }}"></option>
        @endforeach
    </datalist>

    <div class="card shadow-sm">
        <form action="{{ isset($society) ? route('societies.update', $society->id) : route('societies.store') }}"
              method="POST">

            @csrf
            @if(isset($society))
                @method('PUT')
            @endif

            <div class="card-body">

                <div class="row g-3">

                    {{-- NAME --}}
                    <div class="col-md-4">
                        <label class="form-label">Society Name</label>
                        <input type="text" name="name" class="form-control"
                               value="{{ $society->name ?? '' }}" required>
                    </div>

                    {{-- CITY SEARCH --}}
                    <div class="col-md-4">
                        <label class="form-label">City (Search)</label>

                        <div class="position-relative">
                            <input type="text"
                                   list="citiesList"
                                   id="citySearch"
                                   class="form-control cityInput"
                                   placeholder="Start typing city..."
                                   value="{{ $society->city->name ?? '' }}"
                                   autocomplete="off">

                            <small id="noMatchMsg" class="text-danger d-none">No city found</small>
                        </div>

                        <input type="hidden"
                               name="city_id"
                               class="cityID"
                               value="{{ $society->city_id ?? '' }}">
                    </div>

                    {{-- PINCODE --}}
                    <div class="col-md-4">
                        <label class="form-label">Pincode</label>
                        <input type="text" name="pincode" class="form-control"
                               value="{{ $society->pincode ?? '' }}">
                    </div>

                    {{-- ADDRESS --}}
                    <div class="col-md-12">
                        <label class="form-label">Address</label>
                        <textarea name="address" rows="2" class="form-control">{{ $society->address ?? '' }}</textarea>
                    </div>

                </div>

            </div>

            <div class="card-footer text-end">
                <button class="btn btn-success px-4">
                    {{ isset($society) ? 'Update Society' : 'Save Society' }}
                </button>
            </div>

        </form>
    </div>

</div>

@push('scripts')
<script>
$(document).ready(function () {

    const input = $(".cityInput");
    const idField = $(".cityID");
    const noMatch = $("#noMatchMsg");

    input.on("input", function () {
        let typed = $(this).val().trim().toLowerCase();
        let matchFound = false;
        let matchedID = null;

        if (typed === "") {
            idField.val("");
            noMatch.addClass("d-none");
            return;
        }

        $("#citiesList option").each(function () {
            let cityName = $(this).val().toLowerCase();

            // Finding exact match (set ID)
            if (typed === cityName) {
                matchedID = $(this).data("id");
            }

            // Show suggestions based on partial match
            if (cityName.includes(typed)) {
                matchFound = true;
            }
        });

        idField.val(matchedID);

        if (!matchFound) {
            noMatch.removeClass("d-none");
        } else {
            noMatch.addClass("d-none");
        }
    });

});
</script>
@endpush

@endsection
