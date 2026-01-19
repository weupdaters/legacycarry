@extends('admin.layouts.app')

@section('content')
  <div class="container-fluid">

    <div class="py-3 d-flex justify-content-between align-items-center">
      <h4 class="fw-semibold m-0">Add Multiple Societies</h4>
    </div>

    {{-- CITY SEARCH LIST --}}
    <datalist id="citiesList">
      @foreach ($cities as $city)
        <option data-id="{{ $city->id }}" value="{{ $city->name }}"></option>
      @endforeach
    </datalist>

    <div class="card">
      <form action="{{ route('societies.storeMultiple') }}" method="POST">
        @csrf

        <div class="card-body">

          <div id="multiRows">

            {{-- SINGLE ROW TEMPLATE --}}
            <div class="row border rounded p-3 mb-3 single-row">

              <div class="col-md-3">
                <label class="form-label">Society Name</label>
                <input type="text" name="societies[0][name]" class="form-control" required>
              </div>

              <div class="col-md-3">
                <label class="form-label">City (Search)</label>

                <!-- user types here -->
                <input type="text" list="citiesList" class="form-control cityInput" placeholder="Search City..."
                  autocomplete="off" autocorrect="off" autocapitalize="none" spellcheck="false" data-form-type="other"
                  name="city_search_{{ uniqid() }}">

                <!-- actual city_id stored here -->
                <input type="hidden" name="societies[0][city_id]" class="cityID">
              </div>

              <div class="col-md-3">
                <label class="form-label">Pincode</label>
                <input type="text" name="societies[0][pincode]" class="form-control">
              </div>

              <div class="col-md-2">
                <label class="form-label">Address</label>
                <input type="text" name="societies[0][address]" class="form-control" required>
              </div>

              <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
              </div>

            </div> <!-- end row -->

          </div> <!-- multiRows -->

          <button type="button" id="addMore" class="btn btn-outline-primary btn-sm mt-2">
            + Add More
          </button>

        </div>

        <div class="card-footer text-end">
          <button class="btn btn-success">Save All</button>
        </div>

      </form>
    </div>

  </div>
@endsection

@push('scripts')
  <script>
    $(document).ready(function() {

      let index = 1;

      /* -------------------------------------
         ADD NEW ROW
      -------------------------------------- */
      $("#addMore").click(function() {

        let clone = $(".single-row:first").clone();

        clone.find("input").each(function() {

          let name = $(this).attr("name");

          if (name) {
            let newName = name.replace(/\[\d+\]/, "[" + index + "]");
            $(this).attr("name", newName);
          }

          $(this).val(""); // clear fields
        });

        $("#multiRows").append(clone);

        index++;
      });

      /* -------------------------------------
         REMOVE ROW
      -------------------------------------- */
      $(document).on("click", ".removeRow", function() {
        if ($(".single-row").length > 1) {
          $(this).closest(".single-row").remove();
        }
      });

      /* -------------------------------------
         CITY SEARCH → CITY ID MAPPING
      -------------------------------------- */
      $(document).on("input", ".cityInput", function() {

        let typed = $(this).val().toLowerCase();
        let selectedCityID = null;

        $("#citiesList option").each(function() {
          if ($(this).val().toLowerCase() === typed) {
            selectedCityID = $(this).data("id");
          }
        });

        // Put city_id into hidden input
        $(this).closest(".single-row").find(".cityID").val(selectedCityID);
      });

    });
  </script>
@endpush
