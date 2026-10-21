@extends('member.layouts.app')

@section('title', 'Poll Results & Analytics')

@section('content')
<div class="container-fluid py-4">
  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="fw-bold mb-1 text-dark">
        <i class="mdi mdi-chart-bar text-primary me-2"></i> Poll Results & Analytics
      </h3>
      <p class="text-muted mb-0">{{ $poll->title }}</p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('member.polls.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
        <i class="mdi mdi-arrow-left me-1"></i> Back to Polls
      </a>
    </div>
  </div>

  <div class="row g-4">
    {{-- Left: Results Breakdown & Progress Bars --}}
    <div class="col-lg-7">
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
          <h5 class="mb-0 fw-bold text-dark">Vote Breakdown</h5>
          <span class="badge bg-primary rounded-pill px-3 py-2 fs-6">{{ $results['total_votes'] }} Total Votes</span>
        </div>
        <div class="card-body p-4 pt-0">
          @if($results['total_votes'] > 0)
            @if($results['is_tie'])
              <div class="alert alert-warning border-0 rounded-4 d-flex align-items-center mb-4">
                <i class="mdi mdi-alert-circle fs-4 me-2"></i>
                <div>
                  <strong>Result is a Tie!</strong> Multiple options share the highest vote count.
                </div>
              </div>
            @endif

            <div class="d-flex flex-column gap-4 my-2">
              @foreach($results['options'] as $option)
                <div>
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-bold text-dark">
                      {{ $option['option_text'] }}
                      @if($option['is_winner'])
                        <span class="badge bg-success rounded-pill ms-2 px-2 py-1">
                          <i class="mdi mdi-trophy me-1"></i> {{ $results['is_tie'] ? 'Tied' : 'Winner / Leader' }}
                        </span>
                      @endif
                    </span>
                    <span class="fw-bold text-primary">{{ $option['percentage'] }}% ({{ $option['votes_count'] }} votes)</span>
                  </div>
                  <div class="progress" style="height: 20px; border-radius: 10px;">
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
              <i class="mdi mdi-inbox-outline mdi-48px d-block mb-2 text-secondary"></i>
              <p class="mb-0">No votes have been cast yet for this poll.</p>
            </div>
          @endif
        </div>
      </div>

      {{-- Participating Voters List (if resident admin has poll_edit / poll_results permission) --}}
      @if(has_permission('poll_results') || has_permission('poll_edit'))
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
          <div class="card-header bg-white border-0 py-3 px-4">
            <h5 class="mb-0 fw-bold text-dark">Voter Turnout Audit ({{ $poll->votes->count() }} Residents)</h5>
            <small class="text-muted">Shows who participated in this poll without compromising ballot secret.</small>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="ps-4">#</th>
                    <th>Resident Name</th>
                    <th>Contact</th>
                    <th class="text-end pe-4">Vote Timestamp</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($poll->votes as $index => $vote)
                    <tr>
                      <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                      <td class="fw-semibold text-dark">{{ $vote->user?->name ?? 'Unknown Resident' }}</td>
                      <td><small class="text-muted">{{ $vote->user?->mobile ?? $vote->user?->email ?? '-' }}</small></td>
                      <td class="text-end pe-4"><small class="text-muted">{{ $vote->voted_at ? $vote->voted_at->format('d M, Y h:i A') : '-' }}</small></td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="4" class="text-center py-4 text-muted">No resident votes recorded yet.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      @endif
    </div>

    {{-- Right: Poll Details & Meta --}}
    <div class="col-lg-5">
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 py-3 px-4">
          <h5 class="mb-0 fw-bold text-dark">Poll Details</h5>
        </div>
        <div class="card-body p-4 pt-0">
          <dl class="row mb-0">
            <dt class="col-sm-5 text-muted">Status</dt>
            <dd class="col-sm-7">
              @if($poll->status === 'active')
                <span class="badge bg-soft-success text-success rounded-pill px-3">Active (Open)</span>
              @elseif($poll->status === 'closed')
                <span class="badge bg-soft-secondary text-secondary rounded-pill px-3">Closed</span>
              @elseif($poll->status === 'scheduled')
                <span class="badge bg-soft-info text-info rounded-pill px-3">Scheduled</span>
              @else
                <span class="badge bg-soft-warning text-warning rounded-pill px-3">Draft</span>
              @endif
            </dd>

            <dt class="col-sm-5 text-muted">Target Audience</dt>
            <dd class="col-sm-7 fw-semibold">{{ ucfirst($poll->target_audience) }} Residents</dd>

            <dt class="col-sm-5 text-muted">Visibility</dt>
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
            <h6 class="fw-bold text-dark small mb-2">Description / Background</h6>
            <p class="small text-muted mb-0">{{ $poll->description }}</p>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
