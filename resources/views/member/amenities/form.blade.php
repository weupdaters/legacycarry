@extends('member.layouts.app')

@section('title', isset($amenity) ? 'Edit Amenity' : 'Add New Amenity')

@section('content')
<div class="container-fluid py-4">
  <div class="row justify-content-center">
    <div class="col-lg-10">
      {{-- Back Button --}}
      <div class="mb-3 d-flex justify-content-between align-items-center">
        <a href="{{ Route::has('member.amenities.index') ? route('member.amenities.index') : url('residents/amenities') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
          <i class="mdi mdi-arrow-left me-1"></i> Back to Amenities
        </a>
        <h4 class="fw-bold mb-0 text-dark">
          {{ isset($amenity) ? 'Edit Amenity: ' . $amenity->name : 'Add New Society Amenity' }}
        </h4>
      </div>

      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
          @if ($errors->any())
            <div class="alert alert-danger rounded-4 border-0 mb-4">
              <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form method="POST" action="{{ isset($amenity) ? (Route::has('member.amenities.update') ? route('member.amenities.update', $amenity->id) : url('residents/amenities/' . $amenity->id)) : (Route::has('member.amenities.store') ? route('member.amenities.store') : url('residents/amenities')) }}" enctype="multipart/form-data">
            @csrf
            @if(isset($amenity))
              @method('PUT')
            @endif

            <div class="row g-3">
              {{-- Amenity Name --}}
              <div class="col-md-6">
                <label class="form-label fw-semibold">Amenity Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. Swimming Pool, Community Hall, Gym"
                       value="{{ old('name', $amenity->name ?? '') }}" required maxlength="150">
              </div>

              {{-- Booking Mode --}}
              <div class="col-md-6">
                <label class="form-label fw-semibold">Confirmation Mode <span class="text-danger">*</span></label>
                <select name="booking_mode" class="form-select rounded-3" required>
                  <option value="automatic" {{ old('booking_mode', $amenity->booking_mode ?? '') == 'automatic' ? 'selected' : '' }}>
                    Automatic (Instant confirmation if slot is free)
                  </option>
                  <option value="admin_approval" {{ old('booking_mode', $amenity->booking_mode ?? '') == 'admin_approval' ? 'selected' : '' }}>
                    Admin Approval (Request remains Pending until approved)
                  </option>
                </select>
              </div>

              {{-- Description --}}
              <div class="col-12">
                <label class="form-label fw-semibold">Description / Booking Rules</label>
                <textarea name="description" class="form-control rounded-3" rows="3" placeholder="Rules, guidelines, dress code, terms & conditions...">{{ old('description', $amenity->description ?? '') }}</textarea>
              </div>

              {{-- Photo / Image --}}
              <div class="col-md-6">
                <label class="form-label fw-semibold">Amenity Image</label>
                <input type="file" name="image" class="form-control rounded-3" accept="image/png,image/jpeg,image/webp">
                <small class="text-muted font-11">Max 2MB (JPG, PNG, WEBP)</small>
                @if(isset($amenity) && $amenity->image)
                  <div class="mt-2">
                    <img src="{{ asset('storage/' . $amenity->image) }}" class="rounded-3 border" height="50">
                  </div>
                @endif
              </div>

              {{-- Status --}}
              <div class="col-md-6">
                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                <select name="status" class="form-select rounded-3" required>
                  <option value="active" {{ old('status', $amenity->status ?? '') == 'active' ? 'selected' : '' }}>Active (Available for residents)</option>
                  <option value="inactive" {{ old('status', $amenity->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive (Under maintenance / Closed)</option>
                </select>
              </div>

              {{-- Operating Timings --}}
              <div class="col-md-3">
                <label class="form-label fw-semibold">Opening Time <span class="text-danger">*</span></label>
                <input type="time" name="opening_time" class="form-control rounded-3" value="{{ old('opening_time', isset($amenity) ? substr($amenity->opening_time, 0, 5) : '06:00') }}" required>
              </div>

              <div class="col-md-3">
                <label class="form-label fw-semibold">Closing Time <span class="text-danger">*</span></label>
                <input type="time" name="closing_time" class="form-control rounded-3" value="{{ old('closing_time', isset($amenity) ? substr($amenity->closing_time, 0, 5) : '22:00') }}" required>
              </div>

              <div class="col-md-3">
                <label class="form-label fw-semibold">Slot Duration (Mins) <span class="text-danger">*</span></label>
                <input type="number" name="slot_duration_minutes" class="form-control rounded-3" min="15" max="480" step="15" value="{{ old('slot_duration_minutes', $amenity->slot_duration_minutes ?? 60) }}" required>
              </div>

              <div class="col-md-3">
                <label class="form-label fw-semibold">Max Duration (Mins) <span class="text-danger">*</span></label>
                <input type="number" name="max_booking_duration_minutes" class="form-control rounded-3" min="15" max="1440" step="15" value="{{ old('max_booking_duration_minutes', $amenity->max_booking_duration_minutes ?? 120) }}" required>
              </div>

              {{-- Advance Limit --}}
              <div class="col-12">
                <label class="form-label fw-semibold">Advance Booking Limit (Days) <span class="text-danger">*</span></label>
                <input type="number" name="advance_booking_days" class="form-control rounded-3" min="1" max="365" value="{{ old('advance_booking_days', $amenity->advance_booking_days ?? 7) }}" required>
                <small class="text-muted font-11">How many days in advance residents can book.</small>
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
            </div>

            {{-- Submit --}}
            <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
              <a href="{{ Route::has('member.amenities.index') ? route('member.amenities.index') : url('residents/amenities') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
              <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold">
                <i class="mdi mdi-check me-1"></i> {{ isset($amenity) ? 'Update Amenity' : 'Save Amenity' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
