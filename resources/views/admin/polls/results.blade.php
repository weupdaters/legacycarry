@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
  {{-- Header --}}
  <div class="py-3 d-flex justify-content-between align-items-center">
    <div>
      <h4 class="fw-semibold mb-1">
        <i class="bi bi-bar-chart-fill me-1"></i> Poll Results & Analytics
      </h4>
      <p class="text-muted small mb-0">{{ $poll->title }}</p>
    </div>
    <div>
      <a href="{{ route('admin.polls.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Polls
      </a>
    </div>
  </div>

  <div class="row">
    {{-- Left: Results Breakdown & Progress Bars --}}
    <div class="col-lg-7">
      <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0 fw-semibold">Vote Tally</h5>
          <span class="badge bg-primary fs-6">{{ $results['total_votes'] }} Total Votes</span>
        </div>
        <div class="card-body">
          @if($results['total_votes'] > 0)
            @if($results['is_tie'])
              <div class="alert alert-warning d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
                <div>
                  <strong>Result is a Tie!</strong> Multiple options share the highest vote count.
                </div>
              </div>
            @endif

            <div class="d-flex flex-column gap-4 my-2">
              @foreach($results['options'] as $option)
                <div>
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold text-dark">
                      {{ $option['option_text'] }}
                      @if($option['is_winner'])
                        <span class="badge bg-success ms-2">
                          <i class="bi bi-trophy-fill me-1"></i> {{ $results['is_tie'] ? 'Tied' : 'Leader / Winner' }}
                        </span>
                      @endif
                    </span>
                    <span class="fw-bold text-primary">{{ $option['percentage'] }}% ({{ $option['votes_count'] }} votes)</span>
                  </div>
                  <div class="progress" style="height: 22px; border-radius: 8px;">
                    <div class="progress-bar {{ $option['is_winner'] ? 'bg-success' : 'bg-primary' }}"
                         role="progressbar"
                         style="width: {{ $option['percentage'] }}%; font-weight: 600;"
                         aria-valuenow="{{ $option['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                      @if($option['percentage'] >= 10)
                        {{ $option['percentage'] }}%
                      @endif
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="text-center py-5 text-muted">
              <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
              <p class="mb-0">No votes have been cast yet for this poll.</p>
            </div>
          @endif
        </div>
      </div>

      {{-- Participating Voters List --}}
      <div class="card">
        <div class="card-header bg-white">
          <h5 class="mb-0 fw-semibold">Voter Turnout Audit ({{ $poll->votes->count() }} Residents)</h5>
          <small class="text-muted">Shows who participated in this poll without compromising ballot privacy.</small>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Resident Name</th>
                  <th>Contact</th>
                  <th>Vote Timestamp</th>
                </tr>
              </thead>
              <tbody>
                @forelse($poll->votes as $index => $vote)
                  <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="fw-medium">{{ $vote->user?->name ?? 'Unknown' }}</td>
                    <td><small class="text-muted">{{ $vote->user?->phone ?? $vote->user?->email }}</small></td>
                    <td><small class="text-muted">{{ $vote->voted_at->format('d M, Y H:i:s') }}</small></td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="text-center py-3 text-muted">No resident votes recorded yet.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    {{-- Right: Poll Configuration & Meta --}}
    <div class="col-lg-5">
      <div class="card mb-4">
        <div class="card-header bg-white">
          <h5 class="mb-0 fw-semibold">Poll Information</h5>
        </div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-5 text-muted">Status</dt>
            <dd class="col-sm-7">
              @if($poll->status === 'active')
                <span class="badge bg-success">Active (Voting Open)</span>
              @elseif($poll->status === 'closed')
                <span class="badge bg-secondary">Closed</span>
              @elseif($poll->status === 'scheduled')
                <span class="badge bg-info">Scheduled</span>
              @else
                <span class="badge bg-warning text-dark">Draft</span>
              @endif
            </dd>

            <dt class="col-sm-5 text-muted">Society</dt>
            <dd class="col-sm-7 fw-medium">{{ $poll->society?->name }}</dd>

            <dt class="col-sm-5 text-muted">Target Audience</dt>
            <dd class="col-sm-7">{{ ucfirst($poll->target_audience) }} Residents</dd>

            <dt class="col-sm-5 text-muted">Results Visibility</dt>
            <dd class="col-sm-7">
              @if($poll->results_visibility === 'after_vote')
                After Voting
              @elseif($poll->results_visibility === 'always')
                Always Public
              @else
                After Poll Closes
              @endif
            </dd>

            <dt class="col-sm-5 text-muted">Voting Opens</dt>
            <dd class="col-sm-7 small">{{ $poll->start_date->format('d M, Y h:i A') }}</dd>

            <dt class="col-sm-5 text-muted">Voting Closes</dt>
            <dd class="col-sm-7 small">{{ $poll->end_date->format('d M, Y h:i A') }}</dd>

            <dt class="col-sm-5 text-muted">Created By</dt>
            <dd class="col-sm-7 small">{{ $poll->creator?->name ?? 'Admin' }}</dd>
          </dl>

          @if($poll->description)
            <hr>
            <h6 class="fw-semibold text-muted small">Context / Description</h6>
            <p class="small text-muted mb-0">{{ $poll->description }}</p>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
