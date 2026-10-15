@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
  {{-- Header --}}
  <div class="py-3 d-flex justify-content-between align-items-center">
    <div>
      <h4 class="fw-semibold mb-1">
        <i class="bi bi-ticket-detailed-fill me-1"></i> Society Complaints & Helpdesk
      </h4>
      <p class="text-muted small mb-0">Manage resident complaints, assign tickets to staff, and track resolution.</p>
    </div>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- Filters --}}
  <div class="card mb-3">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('admin.tickets.index') }}" class="row g-2 align-items-center">
        @if(!$adminSocietyId && auth()->user()->role === 'super_admin')
          <div class="col-md-3">
            <select name="society_id" class="form-select form-select-sm">
              <option value="">-- All Societies --</option>
              @foreach($societies as $soc)
                <option value="{{ $soc->id }}" {{ request('society_id') == $soc->id ? 'selected' : '' }}>
                  {{ $soc->name }}
                </option>
              @endforeach
            </select>
          </div>
        @endif

        <div class="col-md-3">
          <select name="status" class="form-select form-select-sm">
            <option value="">-- All Statuses --</option>
            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
          </select>
        </div>

        <div class="col-md-2">
          <select name="priority" class="form-select form-select-sm">
            <option value="">-- All Priorities --</option>
            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
            <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
          </select>
        </div>

        <div class="col-md-2">
          <select name="category" class="form-select form-select-sm">
            <option value="">-- All Categories --</option>
            <option value="Plumbing" {{ request('category') == 'Plumbing' ? 'selected' : '' }}>Plumbing</option>
            <option value="Electrical" {{ request('category') == 'Electrical' ? 'selected' : '' }}>Electrical</option>
            <option value="Maintenance" {{ request('category') == 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
            <option value="Security" {{ request('category') == 'Security' ? 'selected' : '' }}>Security</option>
            <option value="Cleanliness" {{ request('category') == 'Cleanliness' ? 'selected' : '' }}>Cleanliness</option>
            <option value="Noise" {{ request('category') == 'Noise' ? 'selected' : '' }}>Noise</option>
            <option value="Parking" {{ request('category') == 'Parking' ? 'selected' : '' }}>Parking</option>
            <option value="Other" {{ request('category') == 'Other' ? 'selected' : '' }}>Other</option>
          </select>
        </div>

        <div class="col-md-2">
          <button type="submit" class="btn btn-secondary btn-sm w-100">
            <i class="bi bi-funnel me-1"></i> Filter
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Tickets Table --}}
  <div class="card shadow-sm border-0">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-3">Ticket #</th>
              <th>Resident</th>
              <th>Society</th>
              <th>Category</th>
              <th>Subject</th>
              <th>Priority</th>
              <th>Status</th>
              <th>Assigned To</th>
              <th>Created</th>
              <th class="text-end pe-3">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($tickets as $ticket)
              <tr>
                <td class="ps-3 fw-bold text-primary">#{{ $ticket->ticket_number }}</td>
                <td>
                  <div class="fw-semibold text-dark">{{ $ticket->user?->name ?? 'N/A' }}</div>
                  <small class="text-muted">{{ $ticket->user?->phone ?? $ticket->user?->email }}</small>
                </td>
                <td>
                  <span class="badge bg-light text-dark border">{{ $ticket->society?->name ?? 'N/A' }}</span>
                </td>
                <td>
                  <span class="badge bg-info-subtle text-info">{{ $ticket->category }}</span>
                </td>
                <td>
                  <div class="fw-semibold text-truncate" style="max-width: 250px;">{{ $ticket->title }}</div>
                </td>
                <td>
                  @if($ticket->priority === 'urgent')
                    <span class="badge bg-danger">Urgent</span>
                  @elseif($ticket->priority === 'high')
                    <span class="badge bg-warning text-dark">High</span>
                  @elseif($ticket->priority === 'medium')
                    <span class="badge bg-info text-dark">Medium</span>
                  @else
                    <span class="badge bg-secondary">Low</span>
                  @endif
                </td>
                <td>
                  @if($ticket->status === 'open')
                    <span class="badge bg-warning text-dark">Open</span>
                  @elseif($ticket->status === 'in_progress')
                    <span class="badge bg-info text-white">In Progress</span>
                  @elseif($ticket->status === 'resolved')
                    <span class="badge bg-success">Resolved</span>
                  @else
                    <span class="badge bg-secondary">Closed</span>
                  @endif
                </td>
                <td>
                  <small class="text-muted">{{ $ticket->assignee?->name ?? 'Unassigned' }}</small>
                </td>
                <td>
                  <small class="text-muted">{{ $ticket->created_at->format('d M, Y') }}</small>
                </td>
                <td class="text-end pe-3">
                  <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="btn btn-outline-primary btn-sm">
                    Manage <i class="bi bi-chevron-right ms-1"></i>
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="10" class="text-center py-5 text-muted">
                  <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                  No complaint tickets found for this society.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="p-3">
        {{ $tickets->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
