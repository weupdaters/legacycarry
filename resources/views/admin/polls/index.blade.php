@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
  {{-- Header --}}
  <div class="py-3 d-flex justify-content-between align-items-center">
    <div>
      <h4 class="fw-semibold mb-1">
        <i class="bi bi-pie-chart-fill me-1"></i> Polls & Surveys
      </h4>
      <p class="text-muted small mb-0">Create resident opinion polls, track voting progress, and view election/survey results.</p>
    </div>

    <div>
      <a href="{{ route('admin.polls.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Create Poll
      </a>
    </div>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- Filters --}}
  <div class="card mb-3">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('admin.polls.index') }}" class="row g-2">
        <div class="col-md-4">
          <select name="society_id" class="form-select form-select-sm">
            <option value="">-- All Societies --</option>
            @foreach($societies as $soc)
              <option value="{{ $soc->id }}" {{ request('society_id') == $soc->id ? 'selected' : '' }}>
                {{ $soc->name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <select name="status" class="form-select form-select-sm">
            <option value="">-- All Statuses --</option>
            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-secondary btn-sm w-100">Filter</button>
        </div>
        <div class="col-md-2">
          <a href="{{ route('admin.polls.index') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
        </div>
      </form>
    </div>
  </div>

  {{-- Polls Table --}}
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Question / Title</th>
              <th>Society</th>
              <th>Voting Window</th>
              <th>Audience</th>
              <th>Status</th>
              <th>Votes Cast</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($polls as $poll)
              <tr>
                <td>#{{ $poll->id }}</td>
                <td>
                  <span class="fw-semibold">{{ $poll->title }}</span>
                  @if($poll->description)
                    <p class="text-muted small mb-0 text-truncate" style="max-width: 280px;">{{ $poll->description }}</p>
                  @endif
                </td>
                <td>{{ $poll->society?->name ?? '-' }}</td>
                <td>
                  <small class="d-block text-muted"><strong>From:</strong> {{ $poll->start_date->format('d M, Y H:i') }}</small>
                  <small class="d-block text-muted"><strong>To:</strong> {{ $poll->end_date->format('d M, Y H:i') }}</small>
                </td>
                <td>
                  <span class="badge bg-light text-dark border">
                    {{ ucfirst($poll->target_audience) }}
                  </span>
                </td>
                <td>
                  @if($poll->status === 'active')
                    <span class="badge bg-success">Active</span>
                  @elseif($poll->status === 'closed')
                    <span class="badge bg-secondary">Closed</span>
                  @elseif($poll->status === 'scheduled')
                    <span class="badge bg-info">Scheduled</span>
                  @else
                    <span class="badge bg-warning text-dark">Draft</span>
                  @endif
                </td>
                <td>
                  <span class="badge bg-primary fs-6">{{ $poll->votes_count }}</span>
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm">
                    <a href="{{ route('admin.polls.results', $poll->id) }}" class="btn btn-outline-info" title="View Results & Analytics">
                      <i class="bi bi-bar-chart-fill"></i> Results
                    </a>
                    <a href="{{ route('admin.polls.edit', $poll->id) }}" class="btn btn-outline-secondary" title="Edit">
                      <i class="bi bi-pencil"></i>
                    </a>
                    @if($poll->status === 'active')
                      <form method="POST" action="{{ route('admin.polls.close', $poll->id) }}" class="d-inline" onsubmit="return confirm('Close this poll immediately?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning" title="Close Poll">
                          <i class="bi bi-lock"></i>
                        </button>
                      </form>
                    @endif
                    <form method="POST" action="{{ route('admin.polls.destroy', $poll->id) }}" onsubmit="return confirm('Delete this poll and its votes?');" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline-danger" title="Delete">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center py-4 text-muted">No polls found. Click "Create Poll" to publish one.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $polls->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
