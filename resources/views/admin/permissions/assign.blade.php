@extends('admin.layouts.app')

@section('content')
  <!-- Start Content-->
  <div class="container-fluid">

    <!-- Page Header -->
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
      <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0">
          Assign Permissions
        </h4>
        <small class="text-muted">
          User : <strong>{{ $user->name }}</strong>
        </small>
      </div>

      <div class="text-end mt-2 mt-sm-0">
        <button type="submit" form="permissionForm" class="btn btn-success btn-sm">
          Save Permissions
        </button>
      </div>
    </div>

    <!-- Card -->
    <div class="row">
      <div class="col-12">
        <div class="card shadow-sm border-0">
          <div class="card-body">

            <form method="POST" action="{{ route('admin.permissions.store', $user->id) }}" id="permissionForm">
              @csrf

              <!-- Check All -->
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="checkAll">
                <label class="form-check-label fw-semibold" for="checkAll">
                  Check All Permissions
                </label>
              </div>

              <!-- Table -->
              <div class="table-responsive">
                <table class="table table-striped align-middle">
                  <thead>
                    <tr>
                      <th style="width: 220px;">Module</th>
                      <th class="text-center">Show</th>
                      <th class="text-center">Index</th>
                      <th class="text-center">Create</th>
                      <th class="text-center">Edit</th>
                    </tr>
                  </thead>

                  <tbody>
                    @foreach ($permissions as $module => $modulePermissions)
                      <tr>
                        <!-- Module -->
                        <td>
                          <div class="form-check">
                            <input class="form-check-input module-checkbox" type="checkbox"
                              id="module_{{ $module }}">
                            <label class="form-check-label fw-semibold" for="module_{{ $module }}">
                              {{ ucfirst($module) }}
                            </label>
                          </div>
                        </td>

                        <!-- Actions -->
                        @foreach (['show', 'index', 'create', 'edit', 'delete'] as $action)
                          @php
                            $perm = $modulePermissions->where('name', $action)->first();
                          @endphp
                          <td class="text-center">
                            @if ($perm)
                              <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                class="form-check-input child-checkbox" @if (in_array($perm->id, $userPermissions)) checked @endif>
                            @endif
                          </td>
                        @endforeach
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>

            </form>

          </div>
        </div>
      </div>
    </div>

  </div>

  {{-- JS (LOGIC UNCHANGED) --}}
  <script>
    document.getElementById('checkAll').addEventListener('change', function() {
      document.querySelectorAll('input[type=checkbox]').forEach(cb => {
        cb.checked = this.checked;
      });
    });

    document.querySelectorAll('.module-checkbox').forEach(parent => {
      parent.addEventListener('change', function() {
        let row = this.closest('tr');
        row.querySelectorAll('.child-checkbox').forEach(ch => {
          ch.checked = this.checked;
        });
      });
    });

    document.querySelectorAll('.child-checkbox').forEach(child => {
      child.addEventListener('change', function() {
        let row = this.closest('tr');
        let parent = row.querySelector('.module-checkbox');
        parent.checked = [...row.querySelectorAll('.child-checkbox')]
          .every(cb => cb.checked);
      });
    });
  </script>
@endsection
