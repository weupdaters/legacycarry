<table class="table table-striped">
  <thead>
    <tr>
      <th>ID</th>
      <th>Title</th>
      <th>Description</th>
      <th>Image</th>
      <th>Sent To (User ID)</th>
      <th>Created At</th>
    </tr>
  </thead>

  <tbody>
    @forelse($notifications as $notification)
      <tr>
        <td>{{ $notification->id }}</td>
        <td>{{ $notification->title }}</td>
        <td>{{ Str::limit($notification->description, 60) }}</td>

        <td>
          @if ($notification->image)
            <img src="{{ $notification->image }}" width="50" height="50" style="object-fit:cover;border-radius:6px;">
          @else
            <span class="text-muted">No Image</span>
          @endif
        </td>

        <td>
          <span class="badge bg-primary">
            User #{{ $notification->sent_to }}
          </span>
        </td>

        <td>{{ $notification->created_at->format('d M Y, h:i A') }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="6" class="text-center text-muted">
          No notifications found.
        </td>
      </tr>
    @endforelse
  </tbody>
</table>

{{-- PAGINATION --}}
<div class="d-flex justify-content-between align-items-center mt-3">
  <div>
    Showing {{ $notifications->firstItem() }} to {{ $notifications->lastItem() }}
    of {{ $notifications->total() }}
  </div>

  <div>
    {!! $notifications->onEachSide(1)->links('vendor.pagination.bootstrap-5') !!}
  </div>
</div>
