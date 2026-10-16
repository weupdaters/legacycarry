@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
  <div class="py-3 d-flex justify-content-between align-items-center">
    <h4 class="fw-semibold">
      <i class="bi bi-grid-fill me-1"></i> {{ isset($amenity) ? 'Edit Amenity' : 'Add New Amenity' }}
    </h4>
    <a href="{{ route('admin.amenities.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i> Back to List
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

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ isset($amenity) ? route('admin.amenities.update', $amenity->id) : route('admin.amenities.store') }}" enctype="multipart/form-data">
        @csrf
        @if(isset($amenity))
          @method('PUT')
        @endif

        <div class="row g-3">
          {{-- Society --}}
          <div class="col-md-6">
            <label class="form-label fw-semibold">Society <span class="text-danger">*</span></label>
            <select name="society_id" class="form-select" required>
              <option value="">-- Select Society --</option>
              @foreach($societies as $soc)
                <option value="{{ $soc->id }}" {{ old('society_id', $amenity->society_id ?? '') == $soc->id ? 'selected' : '' }}>
                  {{ $soc->name }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- Name --}}
          <div class="col-md-6">
            <label class="form-label fw-semibold">Amenity Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Swimming Pool, Clubhouse, Gym" value="{{ old('name', $amenity->name ?? '') }}" required>
          </div>

          {{-- Description --}}
          <div class="col-12">
            <label class="form-label fw-semibold">Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Rules, guidelines, or details about this facility...">{{ old('description', $amenity->description ?? '') }}</textarea>
          </div>

          {{-- Booking Mode --}}
          <div class="col-md-6">
            <label class="form-label fw-semibold">Booking Confirmation Mode <span class="text-danger">*</span></label>
            <select name="booking_mode" class="form-select" required>
              <option value="admin_approval" {{ old('booking_mode', $amenity->booking_mode ?? '') == 'admin_approval' ? 'selected' : '' }}>
                Admin Approval Required (Requests stay pending until approved)
              </option>
              <option value="automatic" {{ old('booking_mode', $amenity->booking_mode ?? '') == 'automatic' ? 'selected' : '' }}>
                Automatic Confirmation (Instant confirm if slot is available)
              </option>
            </select>
            <small class="text-muted">Single clear rule for resident booking requests.</small>
          </div>

          {{-- Status --}}
          <div class="col-md-6">
            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
            <select name="status" class="form-select" required>
              <option value="active" {{ old('status', $amenity->status ?? '') == 'active' ? 'selected' : '' }}>Active (Available for booking)</option>
              <option value="inactive" {{ old('status', $amenity->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive (Under maintenance/Disabled)</option>
            </select>
          </div>

          {{-- Timings --}}
          <div class="col-md-3">
            <label class="form-label fw-semibold">Opening Time <span class="text-danger">*</span></label>
            <input type="time" name="opening_time" class="form-control" value="{{ old('opening_time', isset($amenity) ? substr($amenity->opening_time, 0, 5) : '06:00') }}" required>
          </div>

          <div class="col-md-3">
            <label class="form-label fw-semibold">Closing Time <span class="text-danger">*</span></label>
            <input type="time" name="closing_time" class="form-control" value="{{ old('closing_time', isset($amenity) ? substr($amenity->closing_time, 0, 5) : '22:00') }}" required>
          </div>

          <div class="col-md-3">
            <label class="form-label fw-semibold">Slot Duration (Minutes) <span class="text-danger">*</span></label>
            <input type="number" name="slot_duration_minutes" class="form-control" min="15" max="480" step="15" value="{{ old('slot_duration_minutes', $amenity->slot_duration_minutes ?? 60) }}" required>
          </div>

          <div class="col-md-3">
            <label class="form-label fw-semibold">Max Duration (Minutes) <span class="text-danger">*</span></label>
            <input type="number" name="max_booking_duration_minutes" class="form-control" min="15" max="1440" step="15" value="{{ old('max_booking_duration_minutes', $amenity->max_booking_duration_minutes ?? 120) }}" required>
          </div>

          {{-- Advance Days --}}
          <div class="col-12">
            <label class="form-label fw-semibold">Advance Booking Limit (Days) <span class="text-danger">*</span></label>
            <input type="number" name="advance_booking_days" class="form-control" min="1" max="365" value="{{ old('advance_booking_days', $amenity->advance_booking_days ?? 7) }}" required>
            <small class="text-muted">How many days in advance residents can book.</small>
          </div>

          {{-- Available Days --}}
          <div class="col-12">
            <label class="form-label fw-semibold d-block">Available Days</label>
            @php
              $currentDays = old('available_days', $amenity->available_days ?? $daysOfWeek);
            @endphp
            <div class="d-flex flex-wrap gap-3">
              @foreach($daysOfWeek as $day)
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="available_days[]" value="{{ $day }}" id="day_{{ $day }}"
                    {{ in_array($day, (array)$currentDays) ? 'checked' : '' }}>
                  <label class="form-check-label" for="day_{{ $day }}">{{ $day }}</label>
                </div>
              @endforeach
            </div>
          </div>

          {{-- Image --}}
          <div class="col-md-6">
            <label class="form-label fw-semibold">Amenity Image</label>
            <input type="file" name="image" class="form-control" accept="image/*">
            @if(isset($amenity) && $amenity->image)
              <div class="mt-2">
                <img src="{{ asset('storage/' . $amenity->image) }}" class="rounded border" width="100" height="70" style="object-fit: cover;">
              </div>
            @endif
          </div>

          <div class="col-12 mt-4 text-end">
            <a href="{{ route('admin.amenities.index') }}" class="btn btn-secondary me-2">Cancel</a>
            <button type="submit" class="btn btn-primary px-4">
              {{ isset($amenity) ? 'Update Amenity' : 'Save Amenity' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
