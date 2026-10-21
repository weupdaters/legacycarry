@extends('member.layouts.app')

@section('title', isset($poll) ? 'Edit Poll' : 'Create Poll')

@section('content')
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="fw-bold mb-1 text-dark">
        <i class="mdi mdi-vote text-primary me-2"></i> {{ isset($poll) ? 'Edit Poll' : 'Create Society Poll' }}
      </h3>
      <p class="text-muted mb-0">Create polls to gather votes and opinions from residents in your society.</p>
    </div>
    <a href="{{ Route::has('member.polls.index') ? route('member.polls.index') : url('residents/polls') }}" class="btn btn-outline-secondary rounded-pill px-4">
      <i class="mdi mdi-arrow-left me-1"></i> Back to Polls
    </a>
  </div>

  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
      <i class="mdi mdi-alert-circle me-2"></i> Please check the form errors below:
      <ul class="mb-0 mt-2">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
      <i class="mdi mdi-alert-circle me-2"></i> {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
      <form method="POST" action="{{ isset($poll) ? (Route::has('member.polls.update') ? route('member.polls.update', $poll->id) : url('residents/polls/' . $poll->id)) : (Route::has('member.polls.store') ? route('member.polls.store') : url('residents/polls')) }}">
        @csrf
        @if(isset($poll))
          @method('PUT')
        @endif

        <div class="row g-4">
          {{-- Target Audience --}}
          <div class="col-md-6">
            <label class="form-label fw-semibold text-dark">Target Audience <span class="text-danger">*</span></label>
            <select name="target_audience" class="form-select rounded-3" required>
              <option value="all" {{ old('target_audience', $poll->target_audience ?? '') == 'all' ? 'selected' : '' }}>All Residents (Owners & Tenants)</option>
              <option value="owners" {{ old('target_audience', $poll->target_audience ?? '') == 'owners' ? 'selected' : '' }}>Property Owners Only</option>
              <option value="tenants" {{ old('target_audience', $poll->target_audience ?? '') == 'tenants' ? 'selected' : '' }}>Tenants Only</option>
            </select>
          </div>

          {{-- Status --}}
          <div class="col-md-6">
            <label class="form-label fw-semibold text-dark">Status <span class="text-danger">*</span></label>
            <select name="status" class="form-select rounded-3" required>
              <option value="active" {{ old('status', $poll->status ?? 'active') == 'active' ? 'selected' : '' }}>Active (Open for voting now)</option>
              <option value="scheduled" {{ old('status', $poll->status ?? '') == 'scheduled' ? 'selected' : '' }}>Scheduled (Opens on start date)</option>
              <option value="draft" {{ old('status', $poll->status ?? '') == 'draft' ? 'selected' : '' }}>Draft (Hidden)</option>
              <option value="closed" {{ old('status', $poll->status ?? '') == 'closed' ? 'selected' : '' }}>Closed (Voting stopped)</option>
            </select>
          </div>

          {{-- Question / Title --}}
          <div class="col-12">
            <label class="form-label fw-semibold text-dark">Poll Question / Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control rounded-3" placeholder="e.g. Should we renovate the community hall clubhouse this quarter?" value="{{ old('title', $poll->title ?? '') }}" required>
          </div>

          {{-- Description --}}
          <div class="col-12">
            <label class="form-label fw-semibold text-dark">Additional Details / Context</label>
            <textarea name="description" class="form-control rounded-3" rows="3" placeholder="Provide background information to help residents make an informed decision...">{{ old('description', $poll->description ?? '') }}</textarea>
          </div>

          {{-- Start Date --}}
          <div class="col-md-4">
            <label class="form-label fw-semibold text-dark">Start Date & Time <span class="text-danger">*</span></label>
            <input type="datetime-local" name="start_date" class="form-control rounded-3"
              value="{{ old('start_date', isset($poll) ? $poll->start_date->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" required>
          </div>

          {{-- End Date --}}
          <div class="col-md-4">
            <label class="form-label fw-semibold text-dark">End Date & Time <span class="text-danger">*</span></label>
            <input type="datetime-local" name="end_date" class="form-control rounded-3"
              value="{{ old('end_date', isset($poll) ? $poll->end_date->format('Y-m-d\TH:i') : now()->addDays(7)->format('Y-m-d\TH:i')) }}" required>
          </div>

          {{-- Results Visibility --}}
          <div class="col-md-4">
            <label class="form-label fw-semibold text-dark">Results Visibility <span class="text-danger">*</span></label>
            <select name="results_visibility" class="form-select rounded-3" required>
              <option value="after_vote" {{ old('results_visibility', $poll->results_visibility ?? 'after_vote') == 'after_vote' ? 'selected' : '' }}>
                After Voting (Resident sees results after voting)
              </option>
              <option value="always" {{ old('results_visibility', $poll->results_visibility ?? '') == 'always' ? 'selected' : '' }}>
                Always Public (Anyone can view live tally anytime)
              </option>
              <option value="after_close" {{ old('results_visibility', $poll->results_visibility ?? '') == 'after_close' ? 'selected' : '' }}>
                Only After Poll Closes (Hidden until voting ends)
              </option>
            </select>
          </div>

          {{-- Answer Options (2 to 4 options) --}}
          <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <label class="form-label fw-semibold text-dark mb-0">
                Answer Options (2 to 4 Options) <span class="text-danger">*</span>
              </label>
              @if(!isset($poll) || !$poll->votes()->exists())
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" id="addOptionBtn">
                  <i class="mdi mdi-plus me-1"></i> Add Option
                </button>
              @endif
            </div>

            @if(isset($poll) && $poll->votes()->exists())
              <div class="alert alert-info py-2 small rounded-3 border-0">
                <i class="mdi mdi-information-outline me-1"></i> Options cannot be edited because votes have already been cast on this poll.
              </div>
            @endif

            <div id="optionsContainer">
              @php
                $existingOptions = isset($poll) ? $poll->options->pluck('option_text')->toArray() : ['Yes, agree', 'No, disagree'];
                $oldOptions = old('options', $existingOptions);
              @endphp

              @foreach($oldOptions as $idx => $optValue)
                <div class="input-group mb-2 option-row">
                  <span class="input-group-text option-number fw-semibold bg-light">{{ $idx + 1 }}</span>
                  <input type="text" name="options[]" class="form-control" placeholder="Option {{ $idx + 1 }}" value="{{ $optValue }}" required {{ isset($poll) && $poll->votes()->exists() ? 'readonly' : '' }}>
                  @if(!isset($poll) || !$poll->votes()->exists())
                    <button type="button" class="btn btn-outline-danger remove-option-btn" title="Remove option">
                      <i class="mdi mdi-delete-outline"></i>
                    </button>
                  @endif
                </div>
              @endforeach
            </div>
            <small class="text-muted">Residents will be able to select exactly one option when casting their vote.</small>
          </div>

          <div class="col-12 text-end pt-3 border-top">
            <a href="{{ Route::has('member.polls.index') ? route('member.polls.index') : url('residents/polls') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-semibold">
              <i class="mdi mdi-check me-1"></i> {{ isset($poll) ? 'Update Poll' : 'Publish Poll' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const container = document.getElementById('optionsContainer');
  const addBtn = document.getElementById('addOptionBtn');

  if (addBtn) {
    addBtn.addEventListener('click', function () {
      const rows = container.querySelectorAll('.option-row');
      if (rows.length >= 4) {
        alert('Maximum 4 options allowed per poll.');
        return;
      }
      const newIndex = rows.length + 1;
      const row = document.createElement('div');
      row.className = 'input-group mb-2 option-row';
      row.innerHTML = `
        <span class="input-group-text option-number fw-semibold bg-light">${newIndex}</span>
        <input type="text" name="options[]" class="form-control" placeholder="Option ${newIndex}" required>
        <button type="button" class="btn btn-outline-danger remove-option-btn" title="Remove option">
          <i class="mdi mdi-delete-outline"></i>
        </button>
      `;
      container.appendChild(row);
      updateOptionNumbers();
    });
  }

  if (container) {
    container.addEventListener('click', function (e) {
      if (e.target.closest('.remove-option-btn')) {
        const rows = container.querySelectorAll('.option-row');
        if (rows.length <= 2) {
          alert('A poll must have at least 2 options.');
          return;
        }
        e.target.closest('.option-row').remove();
        updateOptionNumbers();
      }
    });
  }

  function updateOptionNumbers() {
    const rows = container.querySelectorAll('.option-row');
    rows.forEach((row, i) => {
      row.querySelector('.option-number').textContent = (i + 1);
    });
  }
});
</script>
@endsection
