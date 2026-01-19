@extends('admin.layouts.app')

@section('content')
  <div class="container">

    <h3 class="mb-4">Assign Guard Shift</h3>

    {{-- SOCIETY FILTER (no page reload needed) --}}
    <div class="card card-body mb-4">
      <div class="row">
        <div class="col-md-4">
          <label class="form-label">Society</label>
          <select id="societySelect" name="society_id" class="form-select">
            <option value="">Select Society</option>
            @foreach ($societies as $society)
              <option value="{{ $society->id }}" {{ request('society_id') == $society->id ? 'selected' : '' }}>
                {{ $society->name }}
              </option>
            @endforeach
          </select>
        </div>
      </div>
    </div>

    {{-- ASSIGN FORM (hidden until a society is selected) --}}
    <div id="assignFormCard" class="card card-body" style="{{ request('society_id') ? '' : 'display:none;' }}">
      {{-- server-side errors --}}
      @if ($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach ($errors->all() as $err)
              <li>{{ $err }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- No-guards message area --}}
      <div id="noGuardsMessage" class="alert alert-warning d-none">
        No guards found for this society. <a href="{{ route('guards.create') }}">Add a guard</a> first.
      </div>

      <form method="POST" action="{{ route('shift.guards.store') }}" id="assignForm">
        @csrf

        {{-- hidden society input (updated by JS) --}}
        <input type="hidden" id="societyInput" name="society_id" value="{{ request('society_id') ?? '' }}">

        <div class="row g-3">

          {{-- GUARD --}}
          <div class="col-md-4">
            <label class="form-label">Guard</label>
            <select id="guardSelect" name="guard_id" class="form-select" required>
              <option value="">Select Guard</option>
              {{-- server-rendered guards if present --}}
              @if (isset($guards) && $guards->count())
                @foreach ($guards as $guard)
                  <option value="{{ $guard->id }}">{{ $guard->name }}</option>
                @endforeach
              @endif
            </select>
          </div>

          {{-- GATE --}}
          <div class="col-md-4">
            <label class="form-label">Gate</label>
            <input type="text" name="gate" class="form-control" required>
          </div>

          {{-- START SHIFT DATE --}}
          <div class="col-md-4">
            <label class="form-label">Start Shift Date</label>
            <input type="date" name="start_shift_date" id="startShiftDate" class="form-control">
          </div>

          {{-- END SHIFT DATE --}}
          <div class="col-md-4">
            <label class="form-label">End Shift Date</label>
            <input type="date" name="end_shift_date" id="endShiftDate" class="form-control">
          </div>

          {{-- FALLBACK single shift_date (optional) --}}
          <div class="col-md-4">
            <label class="form-label">Single Shift Date (optional)</label>
            <input type="date" name="shift_date" class="form-control">
          </div>

          {{-- START TIME --}}
          <div class="col-md-4">
            <label class="form-label">Start Time</label>
            <input type="time" name="start_time" class="form-control" required>
          </div>

          {{-- END TIME --}}
          <div class="col-md-4">
            <label class="form-label">End Time</label>
            <input type="time" name="end_time" class="form-control" required>
          </div>

          {{-- SUBMIT --}}
          <div class="col-md-12 text-end">
            <button id="assignButton" type="submit" class="btn btn-primary px-4">
              Assign Shift
            </button>
            <a href="{{ route('shift.guards.index') }}" class="btn btn-secondary ms-2">Cancel</a>
          </div>

        </div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(document).ready(function() {
      const guardsUrl = "{{ route('admin.guards.bySociety') }}";
      const $societySelect = $('#societySelect');
      const $assignFormCard = $('#assignFormCard');
      const $societyInput = $('#societyInput');
      const $guardSelect = $('#guardSelect');
      const $noGuardsMessage = $('#noGuardsMessage');
      const $assignButton = $('#assignButton');

      function showNoGuards(show) {
        if (show) {
          $noGuardsMessage.removeClass('d-none');
          $assignButton.prop('disabled', true);
          $guardSelect.prop('required', false);
        } else {
          $noGuardsMessage.addClass('d-none');
          $assignButton.prop('disabled', false);
          $guardSelect.prop('required', true);
        }
      }

      function loadGuardsForSociety(societyId) {
        // show form card
        $assignFormCard.show();
        $societyInput.val(societyId || '');

        $guardSelect.html('<option>Loading...</option>');
        showNoGuards(false);

        if (!societyId) {
          $guardSelect.html('<option value="">Select Guard</option>');
          return;
        }

        $.ajax({
          url: guardsUrl,
          type: 'GET',
          data: {
            society_id: societyId
          },
          dataType: 'json',
          success: function(data) {
            let html = '<option value="">Select Guard</option>';

            if (data && data.length > 0) {
              $.each(data, function(index, guard) {
                html += '<option value="' + guard.id + '">' + guard.name + '</option>';
              });
              $guardSelect.html(html);
              showNoGuards(false);
            } else {
              // no guards
              $guardSelect.html('<option value="">Select Guard</option>');
              showNoGuards(true);
            }
          },
          error: function() {
            $guardSelect.html('<option disabled>Error loading guards</option>');
            showNoGuards(true);
          }
        });
      }

      // change handler
      $societySelect.on('change', function() {
        const societyId = $(this).val();
        loadGuardsForSociety(societyId);
      });

      // auto load if society selected on page load
      if ($societySelect.val()) {
        loadGuardsForSociety($societySelect.val());
      }
    });
  </script>
@endpush
