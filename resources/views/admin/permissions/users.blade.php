@extends('admin.layouts.app')

@section('content')
  <div class="container-fluid">

    <!-- Page Title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">Permission Users</h4>
    </div>

    <!-- Card -->
    <div class="card shadow-sm">
      <div class="card-body">

        <!-- Table -->
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 80px;">ID</th>
                <th>Name</th>
                <th style="width: 180px;">Action</th>
              </tr>
            </thead>

            <tbody>
              @forelse($users as $user)
                <tr>
                  <td>{{ $user->id }}</td>
                  <td class="fw-semibold">
                    {{ $user->name }}
                  </td>
                  <td>
                    <a href="{{ route('admin.permissions.edit', $user->id) }}" class="btn btn-sm btn-info">
                      Assign Permission
                    </a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="text-center text-muted py-4">
                    No users found
                  </td>
                </tr>
              @endforelse
            </tbody>

          </table>
        </div>

      </div>
    </div>

  </div>
@endsection
