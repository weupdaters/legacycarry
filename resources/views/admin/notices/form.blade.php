@php
  $isEdit = isset($notice) && $notice;
@endphp

<div class="card">
  <div class="card-body">

    <div class="mb-3">
      <label class="form-label">Title</label>
      <input type="text" name="title" class="form-control" value="{{ old('title', $isEdit ? $notice->title : '') }}"
        required>
      @error('title')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>

    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" rows="6" class="form-control" required>{{ old('description', $isEdit ? $notice->description : '') }}</textarea>
      @error('description')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>

    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">Send To</label>
        <select name="notice_type" class="form-select" id="notice_type" required>
          <option value="society"
            {{ old('notice_type', $isEdit ? $notice->notice_type : 'society') === 'society' ? 'selected' : '' }}>
            Society (All Residents)
          </option>
          <option value="single"
            {{ old('notice_type', $isEdit ? $notice->notice_type : '') === 'single' ? 'selected' : '' }}>
            Single Person
          </option>
        </select>
        @error('notice_type')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>

      <div class="col-md-4 mb-3" id="target_user_wrap" style="display:none;">
        <label class="form-label">Select User</label>
        <select name="target_user_id" class="form-select">
          <option value="">-- Select User --</option>
          @foreach ($users as $user)
            <option value="{{ $user->id }}"
              {{ old('target_user_id', $isEdit ? $notice->target_user_id : '') == $user->id ? 'selected' : '' }}>
              {{ $user->name }} {{ $user->flat_no ? '(' . $user->flat_no . ')' : '' }}
            </option>
          @endforeach
        </select>
        @error('target_user_id')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>

      <div class="col-md-4 mb-3">
        <label class="form-label">Image</label>
        <input type="file" name="image" class="form-control">
        @if ($isEdit && $notice->image_url)
          <div class="mt-2">
            <img src="{{ $notice->image_url }}" width="90" class="rounded">
          </div>
        @endif
        @error('image')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>
    </div>

    <div class="form-check mb-3">
      <input type="checkbox" name="is_published" value="1" class="form-check-input"
        {{ old('is_published', $isEdit ? $notice->is_published : true) ? 'checked' : '' }}>
      <label class="form-check-label">Publish now</label>
    </div>

    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ route('admin.notices.index') }}" class="btn btn-secondary">Cancel</a>
  </div>
</div>

@push('scripts')
  <script>
    function toggleTargetUser() {
      const type = document.getElementById('notice_type').value;
      const wrap = document.getElementById('target_user_wrap');
      wrap.style.display = (type === 'single') ? 'block' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
      const select = document.getElementById('notice_type');
      if (select) {
        select.addEventListener('change', toggleTargetUser);
        toggleTargetUser();
      }
    });
  </script>
@endpush
