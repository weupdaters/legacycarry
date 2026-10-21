@extends('member.layouts.app')

@section('title', 'Society Polls & Surveys')

@section('content')
<style>
  .btn-highlight-create {
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%) !important;
    color: #1e1b4b !important;
    font-weight: 700 !important;
    border: none !important;
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.45) !important;
    transition: all 0.25s ease-in-out !important;
  }
  .btn-highlight-create:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(245, 158, 11, 0.6) !important;
    color: #0f172a !important;
  }
  .btn-highlight-vote {
    background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%) !important;
    color: #ffffff !important;
    border: none !important;
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.35) !important;
    transition: all 0.2s ease !important;
  }
  .btn-highlight-vote:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(124, 58, 237, 0.5) !important;
    color: #ffffff !important;
  }
  .btn-highlight-info {
    background: linear-gradient(135deg, #0284c7 0%, #38bdf8 100%) !important;
    color: #ffffff !important;
    border: none !important;
    box-shadow: 0 3px 8px rgba(2, 132, 199, 0.3) !important;
  }
  .btn-highlight-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%) !important;
    color: #1e293b !important;
    border: none !important;
    box-shadow: 0 3px 8px rgba(245, 158, 11, 0.3) !important;
  }
  .btn-highlight-danger {
    background: linear-gradient(135deg, #e11d48 0%, #f43f5e 100%) !important;
    color: #ffffff !important;
    border: none !important;
    box-shadow: 0 3px 8px rgba(225, 29, 72, 0.3) !important;
  }
  .poll-card-hover {
    transition: all 0.25s ease-in-out;
  }
  .poll-card-hover:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 22px rgba(0, 0, 0, 0.08) !important;
  }
</style>

@php
  $isResidentAdmin = $isResidentAdmin ?? (
      has_permission('poll_create') 
      || has_permission('poll_edit') 
      || has_permission('poll_delete') 
      || has_permission('poll_close')
      || has_permission('resident_index') 
      || (auth()->check() && auth()->user()->role === 'super_admin')
  );
@endphp

<div class="container-fluid py-4">
  {{-- Hero Banner --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 50%, #6366f1 100%);">
        <div class="card-body p-4 text-white d-flex flex-wrap justify-content-between align-items-center gap-3">
          <div>
            <div class="d-flex align-items-center gap-2 mb-1">
              <span class="badge bg-white text-dark px-3 py-1 rounded-pill fw-bold">Community Governance</span>
              @if($isResidentAdmin)
                <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold">
                  <i class="mdi mdi-shield-account me-1"></i> Admin Controls
                </span>
              @endif
            </div>
            <h3 class="fw-bold mb-1 text-white">Polls & Community Voting</h3>
            <p class="opacity-75 mb-0 font-14">
              Participate in society decisions, upcoming initiatives, maintenance surveys, and feedback.
            </p>
          </div>

          {{-- Highlighted Action Button --}}
          @if($isResidentAdmin)
            <div>
              <a href="{{ Route::has('member.polls.create') ? route('member.polls.create') : url('residents/polls/create') }}" 
                 class="btn btn-highlight-create rounded-pill px-4 py-2 font-15 d-inline-flex align-items-center">
                <i class="mdi mdi-plus-circle font-18 me-1"></i> Create Poll
              </a>
            </div>
          @endif
        </div>
      </div>
    </div>
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

  {{-- Active Polls Section --}}
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold text-dark mb-0">
      <i class="mdi mdi-vote-outline text-primary me-2"></i> Active Polls (Open for Voting)
      <span class="badge bg-light text-dark border ms-1 rounded-pill font-13">{{ $activePolls->count() }} Open</span>
    </h4>
    @if($isResidentAdmin)
      <a href="{{ Route::has('member.polls.create') ? route('member.polls.create') : url('residents/polls/create') }}" 
         class="btn btn-highlight-warning btn-sm rounded-pill fw-bold px-3">
        <i class="mdi mdi-plus me-1"></i> Add Poll
      </a>
    @endif
  </div>

  <div class="row mb-5">
    @forelse($activePolls as $poll)
      @php
        $userVote = $poll->getUserVote(auth()->id());
        $hasVoted = !is_null($userVote);
      @endphp
      <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 poll-card-hover">
          <div class="card-body p-4 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="badge bg-soft-primary text-primary rounded-pill px-3 py-1 font-12 fw-semibold">
                <i class="mdi mdi-clock-outline me-1"></i> Closes: {{ $poll->end_date->format('d M, Y') }}
              </span>
              @if($hasVoted)
                <span class="badge bg-soft-success text-success rounded-pill px-3 py-1 font-12 fw-bold">
                  <i class="mdi mdi-check-circle me-1"></i> Voted
                </span>
              @else
                <span class="badge bg-soft-danger text-danger rounded-pill px-3 py-1 font-12 fw-bold">
                  Action Required
                </span>
              @endif
            </div>

            <h5 class="fw-bold text-dark mb-2">{{ $poll->title }}</h5>
            @if($poll->description)
              <p class="text-muted small mb-3 line-clamp-2">{{ Str::limit($poll->description, 120) }}</p>
            @endif

            <div class="mb-3 d-flex align-items-center gap-2">
              <span class="badge bg-light text-secondary border font-12">
                <i class="mdi mdi-account-group-outline me-1 text-primary"></i> {{ $poll->votes->count() }} votes cast
              </span>
              <span class="badge bg-light text-secondary border font-12">
                <i class="mdi mdi-format-list-bulleted me-1 text-primary"></i> {{ $poll->options->count() }} options
              </span>
            </div>

            {{-- Action Controls --}}
            <div class="mt-auto d-flex flex-column gap-2">
              <a href="{{ route('member.polls.show', $poll->id) }}" 
                 class="btn {{ $hasVoted ? 'btn-outline-primary' : 'btn-highlight-vote' }} w-100 rounded-pill fw-bold py-2">
                <i class="mdi {{ $hasVoted ? 'mdi-check-circle-outline' : 'mdi-vote-outline' }} me-1"></i>
                {{ $hasVoted ? 'View Your Selection' : 'Vote Now' }}
              </a>

              @if($isResidentAdmin)
                <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                  {{-- Results Button --}}
                  <a href="{{ Route::has('member.polls.results') ? route('member.polls.results', $poll->id) : url('residents/polls/' . $poll->id . '/results') }}" 
                     class="btn btn-highlight-info btn-sm rounded-pill fw-bold flex-grow-1 px-3 py-1" 
                     title="Analytics & Live Results">
                    <i class="mdi mdi-chart-bar me-1"></i> Results
                  </a>

                  {{-- Edit Button --}}
                  <a href="{{ Route::has('member.polls.edit') ? route('member.polls.edit', $poll->id) : url('residents/polls/' . $poll->id . '/edit') }}" 
                     class="btn btn-highlight-warning btn-sm rounded-pill fw-bold px-3 py-1" 
                     title="Edit Poll">
                    <i class="mdi mdi-pencil me-1"></i> Edit
                  </a>

                  {{-- Close Button --}}
                  <form method="POST" action="{{ Route::has('member.polls.close') ? route('member.polls.close', $poll->id) : url('residents/polls/' . $poll->id . '/close') }}" 
                        class="d-inline" 
                        onsubmit="return confirm('Close this poll early? No further votes can be cast.');">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning btn-sm rounded-pill fw-bold px-3 py-1" title="Close Poll Early">
                      <i class="mdi mdi-lock-outline me-1"></i> Close
                    </button>
                  </form>

                  {{-- Delete Button --}}
                  <form method="POST" action="{{ Route::has('member.polls.destroy') ? route('member.polls.destroy', $poll->id) : url('residents/polls/' . $poll->id) }}" 
                        class="d-inline" 
                        onsubmit="return confirm('Permanently delete this poll and all its cast votes?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-highlight-danger btn-sm rounded-pill fw-bold px-3 py-1" title="Delete Poll">
                      <i class="mdi mdi-delete-outline me-1"></i> Delete
                    </button>
                  </form>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
          <i class="mdi mdi-inbox-outline mdi-48px text-secondary mb-2 d-block"></i>
          <h6 class="fw-bold">No Active Polls</h6>
          <p class="small mb-3">There are currently no active polls open for voting in your society.</p>
          @if($isResidentAdmin)
            <a href="{{ Route::has('member.polls.create') ? route('member.polls.create') : url('residents/polls/create') }}" class="btn btn-highlight-create btn-sm rounded-pill px-4 fw-bold">
              <i class="mdi mdi-plus me-1"></i> Create First Poll
            </a>
          @endif
        </div>
      </div>
    @endforelse
  </div>

  {{-- Past / Closed Polls --}}
  <h4 class="fw-bold text-dark mb-3"><i class="mdi mdi-history text-secondary me-2"></i> Past & Closed Polls</h4>
  <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light font-13 text-secondary text-uppercase">
            <tr>
              <th class="ps-4 py-3">Poll Question</th>
              <th class="py-3">Ended On</th>
              <th class="py-3">Total Votes</th>
              <th class="py-3">Your Status</th>
              <th class="text-end pe-4 py-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($closedPolls as $poll)
              @php
                $userVote = $poll->getUserVote(auth()->id());
                $hasVoted = !is_null($userVote);
              @endphp
              <tr>
                <td class="ps-4 py-3">
                  <span class="fw-bold text-dark font-15">{{ $poll->title }}</span>
                </td>
                <td class="text-muted font-13">{{ $poll->end_date->format('d M, Y') }}</td>
                <td>
                  <span class="badge bg-light text-dark border font-12 fw-semibold">
                    <i class="mdi mdi-vote text-primary me-1"></i> {{ $poll->votes->count() }} votes
                  </span>
                </td>
                <td>
                  @if($hasVoted)
                    <span class="badge bg-soft-success text-success rounded-pill px-3 py-1 font-12 fw-bold">Voted</span>
                  @else
                    <span class="badge bg-light text-muted rounded-pill px-3 py-1 font-12">Did not vote</span>
                  @endif
                </td>
                <td class="text-end pe-4 py-3">
                  <div class="d-inline-flex justify-content-end gap-2 align-items-center">
                    <a href="{{ Route::has('member.polls.results') ? route('member.polls.results', $poll->id) : url('residents/polls/' . $poll->id . '/results') }}" 
                       class="btn btn-highlight-info btn-sm rounded-pill fw-bold px-3 py-1">
                      <i class="mdi mdi-chart-bar me-1"></i> Results
                    </a>

                    @if($isResidentAdmin)
                      <a href="{{ Route::has('member.polls.edit') ? route('member.polls.edit', $poll->id) : url('residents/polls/' . $poll->id . '/edit') }}" 
                         class="btn btn-highlight-warning btn-sm rounded-pill fw-bold px-3 py-1" 
                         title="Edit Poll">
                        <i class="mdi mdi-pencil me-1"></i> Edit
                      </a>

                      <form method="POST" action="{{ Route::has('member.polls.destroy') ? route('member.polls.destroy', $poll->id) : url('residents/polls/' . $poll->id) }}" 
                            class="d-inline" 
                            onsubmit="return confirm('Permanently delete this closed poll?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-highlight-danger btn-sm rounded-pill fw-bold px-3 py-1" title="Delete Poll">
                          <i class="mdi mdi-delete me-1"></i> Delete
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center py-4 text-muted">No past polls recorded.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="p-3">
        {{ $closedPolls->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
