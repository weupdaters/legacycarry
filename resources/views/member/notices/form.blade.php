@extends('member.layouts.app')

@section('title', isset($notice) ? 'Edit Notice' : 'Create Notice')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            {{-- BACK BUTTON --}}
            <div class="mb-4">
                <a href="{{ route('member.notices.index') }}" class="btn btn-light rounded-pill px-4">
                    <i class="mdi mdi-arrow-left me-1"></i> Back to Notices
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h4 class="fw-bold text-dark mb-0">
                        {{ isset($notice) ? 'Edit Announcement' : 'Create New Announcement' }}
                    </h4>
                    <p class="text-muted small">Publish an update for <strong>{{ $society->name }}</strong></p>
                </div>

                <div class="card-body p-4">
                    <form action="{{ isset($notice) ? route('member.notices.update', $notice->id) : route('member.notices.store') }}" 
                          method="POST" enctype="multipart/form-data">
                        @csrf
                        @if(isset($notice)) @method('PATCH') @endif

                        <div class="row g-4">
                            {{-- Title --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">Notice Title</label>
                                <input type="text" name="title" class="form-control rounded-3 py-2 @error('title') is-invalid @enderror" 
                                       placeholder="Enter notice title" value="{{ old('title', $notice->title ?? '') }}" required>
                                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Description --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control rounded-3 @error('description') is-invalid @enderror" 
                                          rows="6" placeholder="Describe the announcement..." required>{{ old('description', $notice->description ?? '') }}</textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Type --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Target Audience</label>
                                <select name="notice_type" id="notice_type" class="form-select rounded-3 py-2 @error('notice_type') is-invalid @enderror" required>
                                    <option value="all" {{ old('notice_type', $notice->notice_type ?? '') == 'all' ? 'selected' : '' }}>Entire Society</option>
                                    <option value="single" {{ old('notice_type', $notice->notice_type ?? '') == 'single' ? 'selected' : '' }}>Specific Resident</option>
                                </select>
                                @error('notice_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Target User (Conditional) --}}
                            <div class="col-md-6" id="target_user_container" style="display: {{ old('notice_type', $notice->notice_type ?? '') == 'single' ? 'block' : 'none' }};">
                                <label class="form-label fw-semibold">Select Resident</label>
                                <select name="target_user_id" class="form-select rounded-3 py-2 @error('target_user_id') is-invalid @enderror">
                                    <option value="">-- Choose Resident --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('target_user_id', $notice->target_user_id ?? '') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->phone }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('target_user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Image --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">Attachment Image (Optional)</label>
                                <input type="file" name="image" class="form-control rounded-3 @error('image') is-invalid @enderror">
                                <div class="form-text mt-2">Max size: 2MB. Format: JPG, PNG, WEBP.</div>
                                @if(isset($notice) && $notice->image)
                                    <div class="mt-2">
                                        <p class="small text-muted mb-1">Current Image:</p>
                                        <img src="{{ asset('storage/' . $notice->image) }}" class="rounded shadow-sm" height="80">
                                    </div>
                                @endif
                                @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Submit --}}
                            <div class="col-12 text-end mt-5">
                                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold">
                                    <i class="mdi mdi-check-circle me-1"></i>
                                    {{ isset($notice) ? 'Update Announcement' : 'Publish Announcement' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('notice_type').addEventListener('change', function() {
        const container = document.getElementById('target_user_container');
        if (this.value === 'single') {
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
        }
    });
</script>
@endpush
@endsection
