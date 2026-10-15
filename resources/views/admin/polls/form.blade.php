@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
  <div class="py-3 d-flex justify-content-between align-items-center">
    <h4 class="fw-semibold">
      <i class="bi bi-pie-chart-fill me-1"></i> {{ isset($poll) ? 'Edit Poll' : 'Create New Poll' }}
    </h4>
    <a href="{{ route('admin.polls.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i> Back to Polls
    </a>
  </div>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ isset($poll) ? route('admin.polls.update', $poll->id) : route('admin.polls.store') }}">
        @csrf
        @if(isset($poll))
          @method('PUT')
        @endif

        <div class="row g-3">
          {{-- Society --}}
          <div class="col-md-6">
            <label class="form-label fw-semibold">Target Society <span class="text-danger">*</span></label>
            <select name="society_id" class="form-select" required>
              <option value="">-- Select Society --</option>
              @foreach($societies as $soc)
                <option value="{{ $soc->id }}" {{ old('society_id', $poll->society_id ?? '') == $soc->id ? 'selected' : '' }}>
                  {{ $soc->name }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- Target Audience --}}
          <div class="col-md-6">
            <label class="form-label fw-semibold">Target Audience <span class="text-danger">*</span></label>
            <select name="target_audience" class="form-select" required>
              <option value="all" {{ old('target_audience', $poll->target_audience ?? '') == 'all' ? 'selected' : '' }}>All Residents (Owners & Tenants)</option>
              <option value="owners" {{ old('target_audience', $poll->target_audience ?? '') == 'owners' ? 'selected' : '' }}>Property Owners Only</option>
              <option value="tenants" {{ old('target_audience', $poll->target_audience ?? '') == 'tenants' ? 'selected' : '' }}>Tenants Only</option>
            </select>
          </div>

          {{-- Question / Title --}}
          <div class="col-12">
            <label class="form-label fw-semibold">Poll Question / Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" placeholder="e.g. Should we renovate the community hall clubhouse this quarter?" value="{{ old('title', $poll->title ?? '') }}" required>
          </div>

          {{-- Description --}}
          <div class="col-12">
            <label class="form-label fw-semibold">Additional Details / Context</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Provide background information to help residents make an informed decision...">{{ old('description', $poll->description ?? '') }}</textarea>
          </div>

          {{-- Start Date --}}
          <div class="col-md-3">
            <label class="form-label fw-semibold">Start Date & Time <span class="text-danger">*</span></label>
            <input type="datetime-local" name="start_date" class="form-control"
              value="{{ old('start_date', isset($poll) ? $poll->start_date->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" required>
          </div>

          {{-- End Date --}}
          <div class="col-md-3">
            <label class="form-label fw-semibold">End Date & Time <span class="text-danger">*</span></label>
            <input type="datetime-local" name="end_date" class="form-control"
              value="{{ old('end_date', isset($poll) ? $poll->end_date->format('Y-m-d\TH:i') : now()->addDays(7)->format('Y-m-d\TH:i')) }}" required>
          </div>

          {{-- Status --}}
          <div class="col-md-3">
            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
            <select name="status" class="form-select" required>
              <option value="draft" {{ old('status', $poll->status ?? '') == 'draft' ? 'selected' : '' }}>Draft (Not yet visible)</option>
              <option value="scheduled" {{ old('status', $poll->status ?? '') == 'scheduled' ? 'selected' : '' }}>Scheduled (Opens on start date)</option>
              <option value="active" {{ old('status', $poll->status ?? '') == 'active' ? 'selected' : '' }}>Active (Open for voting now)</option>
              <option value="closed" {{ old('status', $poll->status ?? '') == 'closed' ? 'selected' : '' }}>Closed (Voting stopped)</option>
            </select>
          </div>

          {{-- Results Visibility --}}
          <div class="col-md-3">
            <label class="form-label fw-semibold">Results Visibility <span class="text-danger">*</span></label>
            <select name="results_visibility" class="form-select" required>
              <option value="after_vote" {{ old('results_visibility', $poll->results_visibility ?? '') == 'after_vote' ? 'selected' : '' }}>
                After Voting (Resident sees results after casting vote)
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
          <div class="col-12 mt-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <label class="form-label fw-semibold mb-0">
                Answer Options (2 to 4 Options) <span class="text-danger">*</span>
              </label>
              @if(!isset($poll) || !$poll->votes()->exists())
                <button type="button" class="btn btn-outline-primary btn-sm" id="addOptionBtn">
                  <i class="bi bi-plus me-1"></i> Add Option
                </button>
              @endif
            </div>

            @if(isset($poll) && $poll->votes()->exists())
              <div class="alert alert-info py-2 small">
                <i class="bi bi-info-circle me-1"></i> Options cannot be edited because votes have already been cast on this poll.
              </div>
            @endif

            <div id="optionsContainer">
              @php
                $existingOptions = isset($poll) ? $poll->options->pluck('option_text')->toArray() : ['Yes, agree', 'No, disagree'];
                $oldOptions = old('options', $existingOptions);
              @endphp

              @foreach($oldOptions as $idx => $optValue)
                <div class="input-group mb-2 option-row">
                  <span class="input-group-text option-number fw-semibold">{{ $idx + 1 }}</span>
                  <input type="text" name="options[]" class="form-control" placeholder="Option {{ $idx + 1 }}" value="{{ $optValue }}" required {{ isset($poll) && $poll->votes()->exists() ? 'readonly' : '' }}>
                  @if(!isset($poll) || !$poll->votes()->exists())
                    <button type="button" class="btn btn-outline-danger remove-option-btn" title="Remove option">
                      <i class="bi bi-trash"></i>
                    </button>
                  @endif
                </div>
              @endforeach
            </div>
            <small class="text-muted">Residents will be able to select exactly one option when casting their vote.</small>
          </div>

          <div class="col-12 mt-4 text-end">
            <a href="{{ route('admin.polls.index') }}" class="btn btn-secondary me-2">Cancel</a>
            <button type="submit" class="btn btn-primary px-4">
              {{ isset($poll) ? 'Update Poll' : 'Publish / Save Poll' }}
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
        <span class="input-group-text option-number fw-semibold">${newIndex}</span>
        <input type="text" name="options[]" class="form-control" placeholder="Option ${newIndex}" required>
        <button type="button" class="btn btn-outline-danger remove-option-btn" title="Remove option">
          <i class="bi bi-trash"></i>
        </button>
      `;
      container.appendChild(row);
      updateOptionNumbers();
    });
  }

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

  function updateOptionNumbers() {
    const rows = container.querySelectorAll('.option-row');
    rows.forEach((row, i) => {
      row.querySelector('.option-number').textContent = (i + 1);
    });
  }
});
</script>
@endsection
