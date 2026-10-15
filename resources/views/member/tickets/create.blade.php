@extends('member.layouts.app')

@section('title', 'Raise New Complaint')

@section('content')
<div class="container-fluid py-4">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      {{-- Back Button --}}
      <div class="mb-3">
        <a href="{{ route('member.tickets.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
          <i class="mdi mdi-arrow-left me-1"></i> Back to Complaints
        </a>
      </div>

      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
          <h4 class="fw-bold mb-1">Raise a Complaint / Helpdesk Ticket</h4>
          <p class="text-muted small">Your ticket will be routed directly to your Society Management office.</p>
        </div>

        <div class="card-body p-4">
          @if ($errors->any())
            <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4">
              <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form action="{{ route('member.tickets.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Category --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Issue Category <span class="text-danger">*</span></label>
              <select name="category" class="form-select rounded-3 @error('category') is-invalid @enderror" required>
                <option value="">-- Select Issue Category --</option>
                @foreach($categories as $key => $label)
                  <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
              @error('category')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Subject / Title --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Subject / Title <span class="text-danger">*</span></label>
              <input type="text" name="title" class="form-control rounded-3 @error('title') is-invalid @enderror" 
                     placeholder="e.g. Master bedroom tap leaking severely" value="{{ old('title') }}" required maxlength="255">
              @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Priority --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Urgency / Priority</label>
              <select name="priority" class="form-select rounded-3 @error('priority') is-invalid @enderror">
                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low (Can be addressed in regular hours)</option>
                <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium (Needs prompt attention)</option>
                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High (Affecting daily routine)</option>
                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent (Emergency: water leak, fire hazard, power cut)</option>
              </select>
              @error('priority')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Description --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Detailed Description <span class="text-danger">*</span></label>
              <textarea name="description" rows="5" class="form-control rounded-3 @error('description') is-invalid @enderror" 
                        placeholder="Please provide full details of the issue, location inside flat or common area, convenient visiting hours, etc." required>{{ old('description') }}</textarea>
              @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Photo / Attachment --}}
            <div class="mb-4">
              <label class="form-label fw-semibold">Photo / Attachment (Optional)</label>
              <input type="file" name="attachment" class="form-control rounded-3 @error('attachment') is-invalid @enderror" 
                     accept="image/png,image/jpeg,image/webp,application/pdf">
              <div class="form-text small">Accepted formats: JPG, PNG, WEBP, PDF (Max 5MB). Upload a photo of the damaged/broken item.</div>
              @error('attachment')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Submit --}}
            <div class="d-flex justify-content-end gap-2">
              <a href="{{ route('member.tickets.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
              <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                <i class="mdi mdi-send me-1"></i> Submit Ticket
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
