@extends('member.layouts.app')

@section('title', 'Society Polls & Surveys')

@section('content')
<div class="container-fluid py-4">
  {{-- Hero Banner --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%);">
        <div class="card-body p-4 text-white d-flex justify-content-between align-items-center">
          <div>
            <h3 class="fw-bold mb-1">Polls & Community Voting</h3>
            <p class="opacity-75 mb-0">Have your say in society decisions, upcoming initiatives, and community opinions.</p>
          </div>
          @if(has_permission('poll_create'))
            <div>
              <a href="{{ Route::has('member.polls.create') ? route('member.polls.create') : url('residents/polls/create') }}" class="btn btn-white text-primary fw-bold rounded-pill px-4">
                <i class="mdi mdi-plus me-1"></i> Create Poll
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
  <h4 class="fw-bold text-dark mb-3"><i class="mdi mdi-vote-outline text-primary me-2"></i> Active Polls (Open for Voting)</h4>
  <div class="row mb-5">
    @forelse($activePolls as $poll)
      @php
        $userVote = $poll->getUserVote(auth()->id());
        $hasVoted = !is_null($userVote);
      @endphp
      <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 transition-hover">
          <div class="card-body p-4 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="badge bg-soft-primary text-primary rounded-pill px-3 py-1">
                Closes: {{ $poll->end_date->format('d M, Y') }}
              </span>
              @if($hasVoted)
                <span class="badge bg-soft-success text-success rounded-pill px-3 py-1">
                  <i class="mdi mdi-check-circle me-1"></i> Voted
                </span>
              @else
                <span class="badge bg-soft-danger text-danger rounded-pill px-3 py-1">Action Required</span>
              @endif
            </div>

            <h5 class="fw-bold text-dark mb-2">{{ $poll->title }}</h5>
            @if($poll->description)
              <p class="text-muted small mb-3 line-clamp-2">{{ Str::limit($poll->description, 120) }}</p>
            @endif

            <div class="mb-3">
              <small class="text-muted"><i class="mdi mdi-account-group-outline me-1"></i> {{ $poll->votes->count() }} votes cast so far</small>
            </div>

            <div class="mt-auto d-flex flex-column gap-2">
              <a href="{{ route('member.polls.show', $poll->id) }}" class="btn {{ $hasVoted ? 'btn-outline-primary' : 'btn-primary' }} w-100 rounded-pill fw-semibold py-2">
                {{ $hasVoted ? 'View Your Vote' : 'Vote Now' }}
              </a>

              @if(has_permission('poll_edit') || has_permission('poll_delete') || has_permission('poll_close') || has_permission('poll_results'))
                <div class="d-flex flex-wrap gap-1 pt-2 border-top">
                  @if(has_permission('poll_results') || has_permission('poll_edit'))
                    <a href="{{ Route::has('member.polls.results') ? route('member.polls.results', $poll->id) : url('residents/polls/' . $poll->id . '/results') }}" class="btn btn-outline-info btn-sm rounded-pill flex-grow-1" title="Analytics & Results">
                      <i class="mdi mdi-chart-bar me-1"></i> Results
                    </a>
                  @endif
                  @if(has_permission('poll_edit'))
                    <a href="{{ Route::has('member.polls.edit') ? route('member.polls.edit', $poll->id) : url('residents/polls/' . $poll->id . '/edit') }}" class="btn btn-outline-primary btn-sm rounded-pill flex-grow-1" title="Edit Poll">
                      <i class="mdi mdi-pencil me-1"></i> Edit
                    </a>
                  @endif
                  @if(has_permission('poll_close') || has_permission('poll_edit'))
                    <form method="POST" action="{{ Route::has('member.polls.close') ? route('member.polls.close', $poll->id) : url('residents/polls/' . $poll->id . '/close') }}" class="d-inline" onsubmit="return confirm('Close this poll early? No more votes can be cast.');">
                      @csrf
                      <button type="submit" class="btn btn-outline-warning btn-sm rounded-pill" title="Close Poll">
                        <i class="mdi mdi-lock-outline"></i> Close
                      </button>
                    </form>
                  @endif
                  @if(has_permission('poll_delete'))
                    <form method="POST" action="{{ Route::has('member.polls.destroy') ? route('member.polls.destroy', $poll->id) : url('residents/polls/' . $poll->id) }}" class="d-inline" onsubmit="return confirm('Delete this poll and all its votes?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill" title="Delete Poll">
                        <i class="mdi mdi-delete-outline"></i>
                      </button>
                    </form>
                  @endif
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4 text-center text-muted">
          <i class="mdi mdi-inbox-outline mdi-36px text-secondary mb-1"></i>
          <p class="mb-0">There are currently no active polls open for voting in your society.</p>
        </div>
      </div>
    @endforelse
  </div>

  {{-- Past / Closed Polls --}}
  <h4 class="fw-bold text-dark mb-3"><i class="mdi mdi-history text-secondary me-2"></i> Past & Closed Polls</h4>
  <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4">Poll Question</th>
              <th>Ended On</th>
              <th>Total Votes</th>
              <th>Your Status</th>
              <th class="text-end pe-4">Results</th>
            </tr>
          </thead>
          <tbody>
            @forelse($closedPolls as $poll)
              @php
                $userVote = $poll->getUserVote(auth()->id());
                $hasVoted = !is_null($userVote);
              @endphp
              <tr>
                <td class="ps-4">
                  <span class="fw-semibold text-dark">{{ $poll->title }}</span>
                </td>
                <td class="text-muted">{{ $poll->end_date->format('d M, Y') }}</td>
                <td><span class="badge bg-light text-dark border">{{ $poll->votes->count() }} votes</span></td>
                <td>
                  @if($hasVoted)
                    <span class="badge bg-soft-success text-success rounded-pill">Voted</span>
                  @else
                    <span class="badge bg-light text-muted">Did not vote</span>
                  @endif
                </td>
                <td class="text-end pe-4">
                  <div class="d-flex justify-content-end gap-1">
                    <a href="{{ Route::has('member.polls.results') ? route('member.polls.results', $poll->id) : url('residents/polls/' . $poll->id . '/results') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                      <i class="mdi mdi-chart-bar me-1"></i> Results
                    </a>
                    @if(has_permission('poll_edit'))
                      <a href="{{ Route::has('member.polls.edit') ? route('member.polls.edit', $poll->id) : url('residents/polls/' . $poll->id . '/edit') }}" class="btn btn-outline-primary btn-sm rounded-pill" title="Edit">
                        <i class="mdi mdi-pencil"></i>
                      </a>
                    @endif
                    @if(has_permission('poll_delete'))
                      <form method="POST" action="{{ Route::has('member.polls.destroy') ? route('member.polls.destroy', $poll->id) : url('residents/polls/' . $poll->id) }}" class="d-inline" onsubmit="return confirm('Delete this poll?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill" title="Delete">
                          <i class="mdi mdi-delete"></i>
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
