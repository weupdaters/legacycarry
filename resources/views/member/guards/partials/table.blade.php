<div class="card">
  <div class="table-responsive">
    <table class="table table-striped mb-0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name / Email</th>
          <th>Phone</th>
          <th>Society</th>
          <th>Status</th>
          <th width="220">Action</th>
        </tr>
      </thead>

      <tbody>
        @forelse($guards as $g)
          <tr>
            <td>{{ $g->id }}</td>

            <td>
              <strong>{{ $g->name }}</strong><br>
              <small class="text-muted">{{ $g->email }}</small><br>
              <small class="text-muted">
                Added {{ $g->created_at->format('d M Y') }}
              </small>
            </td>

            <td>{{ $g->phone }}</td>

            <td>
              {{ $g->details->society->name ?? 'N/A' }}
            </td>

            <td>
              <span class="badge {{ $g->is_active ? 'bg-success' : 'bg-danger' }}">
                {{ $g->is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>

            <td>
              <a href="{{ route('member.guards.view', $g->id) }}" class="btn btn-info btn-sm" title="View">
                <i class="mdi mdi-eye"></i>
              </a>

              <a href="{{ route('member.guards.edit', $g->id) }}" class="btn btn-primary btn-sm" title="Edit">
                <i class="mdi mdi-pencil"></i>
              </a>

              <button onclick="toggleStatus({{ $g->id }})" class="btn btn-warning btn-sm" title="Change Status">
                <i class="mdi mdi-refresh"></i>
              </button>

              <button onclick="deleteGuard({{ $g->id }})" class="btn btn-danger btn-sm" title="Delete">
                <i class="mdi mdi-delete"></i>
              </button>
            </td>

          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center py-4">
              No guards found
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- PAGINATION --}}
  <div class="d-flex justify-content-between align-items-center p-3">
    <div>
      Showing {{ $guards->firstItem() }} to {{ $guards->lastItem() }}
      of {{ $guards->total() }} entries
    </div>

    <div>
      {{ $guards->links('vendor.pagination.bootstrap-5') }}
    </div>
  </div>
</div>
