@extends('member.layouts.app')

@section('content')
  <div class="container">

    {{-- PAGE TITLE --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3>
        Guard Shifts
        <small class="text-muted">({{ ucfirst($statusFilter) }})</small>
      </h3>
      @if (has_permission('guard_shift_create'))
        <a href="{{ route('member.shift.guards.assign') }}" class="btn btn-primary">
          + Assign Shift
        </a>
      @endif
    </div>

    {{-- FILTERS --}}
    <form method="GET" class="card card-body mb-4">
      <div class="row g-3">

        {{-- STATUS --}}
        <div class="col-md-3">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="all" {{ $statusFilter == 'all' ? 'selected' : '' }}>All</option>
            <option value="assigned" {{ $statusFilter == 'assigned' ? 'selected' : '' }}>Assigned</option>
            <option value="active" {{ $statusFilter == 'active' ? 'selected' : '' }}>Active</option>
            <option value="completed" {{ $statusFilter == 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="cancelled" {{ $statusFilter == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
          </select>
        </div>


        {{-- DATE --}}
        <div class="col-md-3">
          <label class="form-label">Start Shift Date</label>
          <input type="date" name="start_shift_date" value="{{ request('start_shift_date') }}" class="form-control">
        </div>


        {{-- END DATE --}}
        <div class="col-md-3">
          <label class="form-label">End Shift Date</label>
          <input type="date" name="end_shift_date" value="{{ request('end_shift_date') }}" class="form-control">
        </div>

        {{-- GUARD --}}
        <div class="col-md-3">
          <label class="form-label">Guard Name</label>
          <input type="text" name="guard" value="{{ request('guard') }}" class="form-control"
            placeholder="Search guard name">
        </div>

        {{-- BUTTON --}}
        <div class="col-md-12 text-end">
          <button class="btn btn-success px-4">
            Filter
          </button>
          <a href="{{ route('shift.guards.index') }}" class="btn btn-secondary ms-2">
            Reset
          </a>
        </div>

      </div>
    </form>

    {{-- TABLE --}}
    <div class="card">
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Guard</th>
              <th>Gate</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Time</th>
              <th>Status</th>
              <th>Assigned By</th>
              <th>Actions</th>
            </tr>
          </thead>

          <tbody>
            @forelse($shifts as $shift)
              <tr>
                <td>{{ $loop->iteration }}</td>

                <td>{{ $shift->guardUser->name ?? '-' }}</td>

                <td>{{ $shift->gate }}</td>

                <td>{{ $shift->start_shift_date }}</td>

                <td>{{ $shift->end_shift_date }}</td>

                <td>
                  {{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }}
                  -
                  {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}
                </td>

                <td>
                  <span
                    class="badge bg-{{ $shift->status == 'assigned'
                        ? 'secondary'
                        : ($shift->status == 'active'
                            ? 'primary'
                            : ($shift->status == 'completed'
                                ? 'success'
                                : 'danger')) }}">
                    {{ ucfirst($shift->status) }}
                  </span>
                </td>

                <td>{{ $shift->assignedByUser->name ?? '-' }}</td>
                @if (has_permission('guard_shift_show'))
                  <td>
                    <a href="{{ route('member.shift.guards.show', $shift->id) }}" class="btn btn-sm btn-outline-primary">
                      View
                    </a>
                  </td>
                @endif
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center text-muted">
                  No shifts found
                </td>
              </tr>
            @endforelse
          </tbody>

        </table>
      </div>
    </div>

  </div>
@endsection
