@extends('member.layouts.app')

@section('content')
  <div class="container-fluid">
    <h4 class="my-3">All Guard Pass History</h4>

    {{-- FILTERS --}}
    <form method="GET" class="card mb-4">
      <div class="card-body row g-2">

        <div class="col-md-2">
          <select name="range" class="form-select">
            <option value="today" {{ $range == 'today' ? 'selected' : '' }}>Today</option>
            <option value="week" {{ $range == 'week' ? 'selected' : '' }}>Week</option>
            <option value="month" {{ $range == 'month' ? 'selected' : '' }}>Month</option>
            <option value="custom" {{ $range == 'custom' ? 'selected' : '' }}>Custom</option>
          </select>
        </div>

        <div class="col-md-2"><input type="date" name="from" value="{{ $from }}" class="form-control"></div>
        <div class="col-md-2"><input type="date" name="to" value="{{ $to }}" class="form-control"></div>

        <div class="col-md-2">
          <select name="type" class="form-select">
            <option value="">All Types</option>
            <option value="visitor" {{ $type == 'visitor' ? 'selected' : '' }}>Visitor</option>
            <option value="worker" {{ $type == 'worker' ? 'selected' : '' }}>Worker</option>
            <option value="vehicle" {{ $type == 'vehicle' ? 'selected' : '' }}>Vehicle</option>
          </select>
        </div>

        <div class="col-md-2">
          <select name="guard_id" class="form-select">
            <option value="">All Guards</option>
            @foreach ($guards as $g)
              <option value="{{ $g->id }}" {{ $guardId == $g->id ? 'selected' : '' }}>
                {{ $g->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2">
          <select name="entry_status" class="form-select">
            <option value="">Entry & Exit</option>
            <option value="in" {{ $entryStatus == 'in' ? 'selected' : '' }}>Entry Only</option>
            <option value="out" {{ $entryStatus == 'out' ? 'selected' : '' }}>Exit Only</option>
          </select>
        </div>

        <div class="col-md-2"><input name="name" value="{{ $name }}" class="form-control" placeholder="Name">
        </div>
        <div class="col-md-2"><input name="vehicle" value="{{ $vehicle }}" class="form-control"
            placeholder="Vehicle"></div>
        <div class="col-md-2"><input name="job" value="{{ $job }}" class="form-control" placeholder="Job">
        </div>
        <div class="col-md-2"><input name="phone" value="{{ $phone }}" class="form-control" placeholder="Phone">
        </div>


        <div class="col-md-12 text-end">
          <button class="btn btn-primary">Apply Filters</button>
          <a href="{{ route('guards.all-pass-history') }}" class="btn btn-outline-secondary">Reset</a>
        </div>

      </div>
    </form>

    {{-- TABLE --}}
    <div class="card">
      <div class="card-body table-responsive">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>Type</th>
              <th>To Name</th>
              <th>From Resident Name</th>
              <th>Building / Floor No.</th>
              <th>Vehicle</th>
              <th>Job</th>
              <th>Entry</th>
              <th>Entry Guard</th>
              <th>Exit</th>
              <th>Exit Guard</th>
            </tr>
          </thead>
          <tbody>
            @forelse($logs as $l)
              <tr>
                <td>{{ $l['type'] }}</td>
                <td>{{ $l['name'] }}<br>({{ $l['to_phone'] }})</td>
                <td>
                  @if (!empty($l['resident_id']))
                    @if (has_permission('resident_show'))
                      <a href="{{ route('residents.view', ['id' => $l['resident_id']]) }}" target="_blank">

                        {{ $l['resident_name'] }}
                      </a>
                    @else
                      {{ $l['resident_name'] }}
                    @endif

                    ({{ $l['resident_phone'] }})
                  @else
                    {{ $l['resident_name'] }}<br>
                    ({{ $l['resident_phone'] }})
                  @endif
                </td>
                <td>{{ $l['building_no'] }}
                  @if ($l['floor_number'] > 0)
                    / {{ $l['floor_number'] }}
                  @endif
                </td>

                <td>{{ $l['vehicle'] }}</td>
                <td>{{ $l['job'] }}</td>
                <td>{{ optional($l['entry_time'])->format('d M Y, h:i A') }}</td>
                <td>{{ $l['entry_guard'] }}</td>
                <td>{{ optional($l['exit_time'])->format('d M Y, h:i A') }}</td>
                <td>{{ $l['exit_guard'] }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center text-muted">No records found</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>
@endsection
