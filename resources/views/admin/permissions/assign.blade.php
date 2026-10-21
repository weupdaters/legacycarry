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
                      <th style="width: 200px;">Module</th>
                      <th class="text-center" style="width: 90px;">Show</th>
                      <th class="text-center" style="width: 90px;">Index</th>
                      <th class="text-center" style="width: 90px;">Create</th>
                      <th class="text-center" style="width: 90px;">Edit</th>
                      <th class="text-center" style="width: 90px;">Delete</th>
                      <th class="text-start ps-3">Additional Permissions</th>
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
                              {{ ucwords(str_replace('_', ' ', $module)) }}
                            </label>
                          </div>
                        </td>

                        <!-- Standard Actions -->
                        @foreach (['show', 'index', 'create', 'edit', 'delete'] as $action)
                          @php
                            $perm = $modulePermissions->where('name', $action)->first();
                          @endphp
                          <td class="text-center">
                            @if ($perm)
                              <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                class="form-check-input child-checkbox" 
                                title="{{ $perm->label ?? ucfirst($action) }}"
                                @if (in_array($perm->id, $userPermissions)) checked @endif>
                            @else
                              <span class="text-muted" style="opacity: 0.25;">-</span>
                            @endif
                          </td>
                        @endforeach

                        <!-- Additional Module Permissions -->
                        @php
                          $extraPermissions = $modulePermissions->filter(function($p) {
                              return !in_array($p->name, ['show', 'index', 'create', 'edit', 'delete']);
                          });
                        @endphp
                        <td class="ps-3">
                          @if ($extraPermissions->isNotEmpty())
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                              @foreach ($extraPermissions as $extra)
                                <div class="form-check form-check-inline m-0 bg-white border rounded px-2 py-1 shadow-sm">
                                  <input type="checkbox" name="permissions[]" value="{{ $extra->id }}"
                                    id="perm_{{ $extra->id }}"
                                    class="form-check-input child-checkbox me-1"
                                    @if (in_array($extra->id, $userPermissions)) checked @endif>
                                  <label class="form-check-label small" for="perm_{{ $extra->id }}" style="cursor: pointer; font-size: 11px;">
                                    {{ $extra->label ?? ucwords(str_replace('_', ' ', $extra->name)) }}
                                  </label>
                                </div>
                              @endforeach
                            </div>
                          @else
                            <span class="text-muted" style="opacity: 0.25;">-</span>
                          @endif
                        </td>
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

  {{-- JS --}}
  <script>
    function updateParentCheckboxes() {
      document.querySelectorAll('tbody tr').forEach(row => {
        let parent = row.querySelector('.module-checkbox');
        let children = row.querySelectorAll('.child-checkbox');
        if (parent && children.length > 0) {
          parent.checked = [...children].every(cb => cb.checked);
        }
      });
      let allCheckboxes = document.querySelectorAll('.child-checkbox');
      let checkAll = document.getElementById('checkAll');
      if (checkAll && allCheckboxes.length > 0) {
        checkAll.checked = [...allCheckboxes].every(cb => cb.checked);
      }
    }

    document.addEventListener('DOMContentLoaded', updateParentCheckboxes);
    updateParentCheckboxes();

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
        updateParentCheckboxes();
      });
    });

    document.querySelectorAll('.child-checkbox').forEach(child => {
      child.addEventListener('change', function() {
        let row = this.closest('tr');
        let parent = row.querySelector('.module-checkbox');
        if (parent) {
          parent.checked = [...row.querySelectorAll('.child-checkbox')].every(cb => cb.checked);
        }
        let allCheckboxes = document.querySelectorAll('.child-checkbox');
        let checkAll = document.getElementById('checkAll');
        if (checkAll && allCheckboxes.length > 0) {
          checkAll.checked = [...allCheckboxes].every(cb => cb.checked);
        }
      });
    });
  </script>
@endsection
