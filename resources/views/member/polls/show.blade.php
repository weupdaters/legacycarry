@extends('member.layouts.app')

@section('title', $poll->title)

@section('content')
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="fw-bold text-dark mb-1">Society Poll</h3>
      <p class="text-muted small mb-0">{{ $poll->society?->name }}</p>
    </div>
    <a href="{{ route('member.polls.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
      <i class="mdi mdi-arrow-left me-1"></i> Back to Polls
    </a>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
      <i class="mdi mdi-check-circle me-2"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
      <i class="mdi mdi-alert-circle me-2"></i> {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        {{-- Card Header --}}
        <div class="card-header bg-white border-0 p-4 pb-0">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge {{ $poll->isVotingOpen() ? 'bg-soft-success text-success' : 'bg-secondary' }} rounded-pill px-3 py-1">
              {{ $poll->isVotingOpen() ? 'Voting Active' : 'Voting Ended' }}
            </span>
            <small class="text-muted">
              <i class="mdi mdi-clock-outline me-1"></i> Ends: {{ $poll->end_date->format('d M Y, h:i A') }}
            </small>
          </div>
          <h4 class="fw-bold text-dark mb-2">{{ $poll->title }}</h4>
          @if($poll->description)
            <p class="text-muted mb-3">{{ $poll->description }}</p>
          @endif
        </div>

        <div class="card-body p-4 pt-2">
          <hr class="opacity-10 my-3">

          {{-- 1. Voting Form (If open and user has not voted) --}}
          @if($poll->isVotingOpen() && !$hasVoted)
            <form method="POST" action="{{ route('member.polls.vote', $poll->id) }}" id="pollVoteForm">
              @csrf
              <h5 class="fw-bold text-dark mb-3">Select One Option:</h5>

              <div class="d-flex flex-column gap-3 mb-4">
                @foreach($poll->options as $option)
                  <label class="card border rounded-4 p-3 mb-0 cursor-pointer option-label hover-shadow" for="opt_{{ $option->id }}" style="cursor: pointer; transition: all 0.2s;">
                    <div class="d-flex align-items-center">
                      <div class="form-check mb-0">
                        <input class="form-check-input fs-5" type="radio" name="poll_option_id" id="opt_{{ $option->id }}" value="{{ $option->id }}" required>
                      </div>
                      <span class="fw-medium text-dark ms-3 fs-6">{{ $option->option_text }}</span>
                    </div>
                  </label>
                @endforeach
              </div>

              <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold w-100 py-3 shadow-sm">
                <i class="mdi mdi-vote me-1"></i> Submit My Vote
              </button>
            </form>

          {{-- 2. User has already voted --}}
          @else
            @if($hasVoted)
              <div class="alert alert-success rounded-4 border-0 d-flex align-items-center p-3 mb-4">
                <i class="mdi mdi-check-decagram fs-2 me-3 text-success"></i>
                <div>
                  <h6 class="fw-bold mb-1">You have cast your vote in this poll</h6>
                  <p class="small mb-0 opacity-80">
                    Voted on {{ $userVote->voted_at->format('d M, Y h:i A') }}.
                  </p>
                </div>
              </div>
            @elseif(!$poll->isVotingOpen())
              <div class="alert alert-secondary rounded-4 border-0 p-3 mb-4">
                <i class="mdi mdi-lock-outline me-1"></i> This poll is closed. Voting has concluded.
              </div>
            @endif

            {{-- 3. Results Section (If permitted) --}}
            @if($canViewResults && $results)
              <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="fw-bold text-dark mb-0">Live Poll Results</h5>
                  <span class="badge bg-primary rounded-pill px-3 py-1">{{ $results['total_votes'] }} Total Votes</span>
                </div>

                @if($results['is_tie'] && $results['total_votes'] > 0)
                  <div class="badge bg-soft-warning text-warning p-2 rounded-3 mb-3 d-block text-start">
                    <i class="mdi mdi-information-outline me-1"></i> Highest vote count is tied between options.
                  </div>
                @endif

                <div class="d-flex flex-column gap-3">
                  @foreach($results['options'] as $opt)
                    @php
                      $isSelectedByUser = $hasVoted && (int)$userVote->poll_option_id === (int)$opt['id'];
                    @endphp
                    <div class="card bg-light border-0 rounded-4 p-3 {{ $isSelectedByUser ? 'border border-primary border-2' : '' }}">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-dark">
                          {{ $opt['option_text'] }}
                          @if($isSelectedByUser)
                            <span class="badge bg-primary ms-1">Your Vote</span>
                          @endif
                          @if($opt['is_winner'] && $results['total_votes'] > 0)
                            <span class="badge bg-success ms-1">
                              <i class="mdi mdi-trophy"></i> {{ $results['is_tie'] ? 'Tied' : 'Leading' }}
                            </span>
                          @endif
                        </span>
                        <span class="fw-bold text-primary">{{ $opt['percentage'] }}% ({{ $opt['votes_count'] }})</span>
                      </div>
                      <div class="progress" style="height: 12px; border-radius: 6px;">
                        <div class="progress-bar {{ $opt['is_winner'] ? 'bg-success' : 'bg-primary' }}"
                             role="progressbar"
                             style="width: {{ $opt['percentage'] }}%;"
                             aria-valuenow="{{ $opt['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            @else
              <div class="text-center py-4 text-muted bg-light rounded-4">
                <i class="mdi mdi-eye-off-outline fs-2 d-block mb-1 text-secondary"></i>
                <p class="mb-0 small">Results will be revealed once the voting period has closed.</p>
              </div>
            @endif
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const labels = document.querySelectorAll('.option-label');
  labels.forEach(label => {
    label.addEventListener('click', function () {
      labels.forEach(l => {
        l.classList.remove('border-primary', 'bg-soft-primary');
      });
      this.classList.add('border-primary', 'bg-soft-primary');
    });
  });
});
</script>
@endsection
