@extends('admin.layouts.app')

@section('content')
  <div class="container-fluid">

    {{-- HEADER --}}
    <div class="py-3 d-flex justify-content-between align-items-center">
      <h4 class="fw-semibold">
        <i class="bi bi-shield-lock-fill me-1"></i> Notices Management
      </h4>

      <div class="text-end">
        @if (has_permission('notice_create'))
        <a href="{{ route('admin.notices.create') }}" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-circle"></i> Add Notice
        </a>
        @endif
      </div>
    </div>

    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead>
              <tr>
                <th>Image</th>
                <th>Title</th>
                <th>Type</th>
                <th>Status</th>
                <th>Published</th>
              </tr>
            </thead>
            <tbody>
              @forelse($notices as $notice)
                <tr>
                  <td>
                    @if ($notice->image)
                      <img src="{{ asset('storage/' . $notice->image) }}" width="60" height="60" style="object-fit:cover;"
                        class="rounded">
                    @else
                      <span class="text-muted">No image</span>
                    @endif
                  </td>
                  <td>{{ $notice->title }}</td>
                  <td>
                    <span class="badge bg-info">
                      {{ $notice->notice_type === 'all' ? 'All Residents' : 'Single User' }}
                    </span>
                  </td>
                  <td>
                    <span class="badge bg-{{ $notice->is_published ? 'success' : 'secondary' }}">
                      {{ $notice->is_published ? 'Published' : 'Draft' }}
                    </span>
                  </td>
                  <td>{{ optional($notice->published_at)->format('d M, Y h:i A') ?? '-' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center">No notices found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{ $notices->links() }}
      </div>
    </div>
  </div>
@endsection
