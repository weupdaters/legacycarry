@extends('member.layouts.app')

@section('content')
  <div class="container">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3>Shift Details</h3>
      <a href="{{ route('member.shift.guards.index') }}" class="btn btn-secondary">
        ← Back
      </a>
    </div>

    {{-- SHIFT DETAILS --}}
    <div class="card mb-4">
      <div class="card-body">
        <p><b>Guard:</b> {{ $shift->guardUser->name }}</p>
        <p><b>Gate:</b> {{ $shift->gate }}</p>
        <p><b>Date:</b> {{ $shift->start_shift_date }} to {{ $shift->end_shift_date }}
        </p>
        <p><b>Time:</b>
          {{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }}
          -
          {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}
        </p>
        <p>
          <b>Status:</b>
          <span class="badge bg-primary">{{ ucfirst($shift->status) }}</span>
        </p>
      </div>
    </div>

    {{-- CHANGE REQUEST (ADMIN ONLY) --}}
    @if ($shift->requests->where('status', 'pending')->count())
      @foreach ($shift->requests->where('status', 'pending') as $req)
        <div class="card mb-4 border-warning">
          <div class="card-header bg-warning">
            <strong>Pending Shift Change Request</strong>
          </div>

          <div class="card-body">
            <p><b>Requested By:</b> {{ $req->requestedBy->name }}</p>

            <p><b>Requested Changes:</b></p>
            <ul>
              @if ($req->new_date)
                <li>New Date: {{ $req->new_date->format('d M Y') }}</li>
              @endif
              @if ($req->new_start)
                <li>New Start Time: {{ $req->new_start }}</li>
              @endif
              @if ($req->new_end)
                <li>New End Time: {{ $req->new_end }}</li>
              @endif
            </ul>

            <p><b>Reason:</b> {{ $req->reason }}</p>

            {{-- ACTION BUTTONS --}}
            <div class="d-flex gap-2">
              <form method="POST" action="{{ route('shift.request.decide', $req->id) }}">
                @csrf
                <input type="hidden" name="status" value="approved">
                <button class="btn btn-success">
                  Approve
                </button>
              </form>

              <form method="POST" action="{{ route('shift.request.decide', $req->id) }}">
                @csrf
                <input type="hidden" name="status" value="rejected">
                <button class="btn btn-danger">
                  Reject
                </button>
              </form>
            </div>
          </div>
        </div>
      @endforeach
    @else
      <div class="alert alert-info">
        No pending shift change requests.
      </div>
    @endif

    {{-- LOGS --}}
    <div class="card">
      <div class="card-header">
        <strong>Shift Activity Logs</strong>
      </div>
      <div class="card-body p-0">
        <table class="table table-bordered mb-0">
          <thead>
            <tr>
              <th>Date</th>
              <th>Action</th>
              <th>By</th>
              <th>Notes</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($shift->logs as $log)
              <tr>
                <td>{{ $log->created_at }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $log->action)) }}</td>
                <td>{{ $log->actionBy->name ?? '-' }}</td>
                <td>{{ $log->notes }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

  </div>
@endsection
