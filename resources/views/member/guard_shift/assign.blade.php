@extends('member.layouts.app')

@section('content')
  <div class="container">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3>Assign Guard Shift</h3>

      <a href="{{ route('member.shift.guards.index') }}" class="btn btn-secondary">
        ← Back to Shift List
      </a>
    </div>

    {{-- SUCCESS MESSAGE --}}
    @if (session('success'))
      <div class="alert alert-success">
        {{ session('success') }}
      </div>
    @endif

    {{-- ERROR MESSAGE --}}
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- ASSIGN FORM --}}
    <form action="{{ route('member.shift.guards.store') }}" method="POST" class="card">
      @csrf

      <div class="card-body">
        <div class="row g-3">




          {{-- GUARD --}}
          <div class="col-md-6">
            <label class="form-label">Select Guard</label>
            <select name="guard_id" class="form-select" required>
              <option value="">-- Select Guard --</option>
              @foreach ($guards as $guard)
                <option value="{{ $guard->id }}" {{ old('guard_id') == $guard->id ? 'selected' : '' }}>
                  {{ $guard->name }} ({{ $guard->email }})
                </option>
              @endforeach
            </select>
          </div>

          {{-- GATE --}}
          <div class="col-md-6">
            <label class="form-label">Gate / Location</label>
            <input type="text" name="gate" value="{{ old('gate') }}" class="form-control"
              placeholder="Gate A / Main Gate / Tower 1" required>
          </div>

          {{-- START DATE --}}
          <div class="col-md-6">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_shift_date" value="{{ old('start_shift_date') }}" class="form-control"
              required>
          </div>

          {{-- END DATE --}}
          <div class="col-md-6">
            <label class="form-label">End Date</label>
            <input type="date" name="end_shift_date" value="{{ old('end_shift_date') }}" class="form-control"
              required>
          </div>

          {{-- START TIME --}}
          <div class="col-md-6">
            <label class="form-label">Start Time</label>
            <input type="time" name="start_time" value="{{ old('start_time') }}" class="form-control" required>
          </div>

          {{-- END TIME --}}
          <div class="col-md-6">
            <label class="form-label">End Time</label>
            <input type="time" name="end_time" value="{{ old('end_time') }}" class="form-control" required>
          </div>

        </div>
      </div>

      {{-- FOOTER --}}
      <div class="card-footer text-end">
        <button type="submit" class="btn btn-primary">
          Assign Shift
        </button>
      </div>
    </form>

  </div>
@endsection
