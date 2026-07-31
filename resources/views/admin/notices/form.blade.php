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
        <label class="form-label">Society</label>
        <select name="society_id" class="form-select" id="society_id" required>
          <option value="">-- Select Society --</option>
          @foreach ($societies as $soc)
            <option value="{{ $soc->id }}"
              {{ old('society_id', $isEdit ? $notice->society_id : '') == $soc->id ? 'selected' : '' }}>
              {{ $soc->name }}
            </option>
          @endforeach
        </select>
        @error('society_id')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>

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
        <label class="form-label">Select User (Filtered by Society)</label>
        <select name="target_user_id" class="form-select" id="target_user_id">
          <option value="">-- Select User --</option>
          @foreach ($users as $user)
            <option value="{{ $user->id }}"
              data-society="{{ $user->details->society_id ?? '' }}"
              {{ old('target_user_id', $isEdit ? $notice->target_user_id : '') == $user->id ? 'selected' : '' }}>
              {{ $user->name }} {{ $user->details->flat_number ? '(' . $user->details->flat_number . ')' : '' }}
            </option>
          @endforeach
        </select>
        @error('target_user_id')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Image</label>
        <input type="file" name="image" class="form-control">
        @if ($isEdit && $notice->image)
          <div class="mt-2">
            <img src="{{ asset('storage/' . $notice->image) }}" width="90" class="rounded shadow-sm">
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

    <button type="submit" class="btn btn-primary px-4">Save Notice</button>
    <a href="{{ route('admin.notices.index') }}" class="btn btn-light px-4">Cancel</a>
  </div>
</div>

@push('scripts')
  <script>
    function filterUsers() {
      const societyId = document.getElementById('society_id').value;
      const userSelect = document.getElementById('target_user_id');
      const users = userSelect.querySelectorAll('option');

      users.forEach(opt => {
        if (opt.value === "") return;
        const userSoc = opt.getAttribute('data-society');
        if (societyId === "" || userSoc === societyId) {
          opt.style.display = 'block';
          opt.disabled = false;
        } else {
          opt.style.display = 'none';
          opt.disabled = true;
        }
      });
      
      // If currently selected user is hidden, reset to empty
      if(userSelect.selectedOptions[0] && userSelect.selectedOptions[0].style.display === 'none'){
          userSelect.value = "";
      }
    }

    function toggleTargetUser() {
      const type = document.getElementById('notice_type').value;
      const wrap = document.getElementById('target_user_wrap');
      wrap.style.display = (type === 'single') ? 'block' : 'none';
      if (type === 'single') {
        filterUsers();
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      const noticeType = document.getElementById('notice_type');
      const societySelect = document.getElementById('society_id');

      if (noticeType) {
        noticeType.addEventListener('change', toggleTargetUser);
      }
      if (societySelect) {
        societySelect.addEventListener('change', filterUsers);
      }

      toggleTargetUser();
    });
  </script>
@endpush
