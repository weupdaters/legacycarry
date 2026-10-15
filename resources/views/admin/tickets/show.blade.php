@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
  {{-- Header --}}
  <div class="py-3 d-flex justify-content-between align-items-center">
    <div>
      <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
        <i class="bi bi-arrow-left me-1"></i> Back to Complaints
      </a>
      <h4 class="fw-semibold mb-0">
        Ticket #{{ $ticket->ticket_number }}: {{ $ticket->title }}
      </h4>
    </div>
    <div>
      @if($ticket->status === 'open')
        <span class="badge bg-warning text-dark fs-6 px-3 py-2">Open</span>
      @elseif($ticket->status === 'in_progress')
        <span class="badge bg-info text-white fs-6 px-3 py-2">In Progress</span>
      @elseif($ticket->status === 'resolved')
        <span class="badge bg-success fs-6 px-3 py-2">Resolved</span>
      @else
        <span class="badge bg-secondary fs-6 px-3 py-2">Closed</span>
      @endif
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

  <div class="row">
    {{-- Left: Conversation & Details --}}
    <div class="col-lg-8">
      {{-- Original Ticket Description --}}
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom">
          <div class="d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-primary">Resident Complaint Details</h6>
            <span class="badge bg-light text-dark border">{{ $ticket->category }}</span>
          </div>
        </div>
        <div class="card-body">
          <p class="mb-3" style="white-space: pre-line;">{{ $ticket->description }}</p>

          @if($ticket->attachment_url)
            <div class="border-top pt-3">
              <span class="fw-semibold small text-muted d-block mb-1">Resident Attachment:</span>
              <a href="{{ $ticket->attachment_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-paperclip me-1"></i> View Attachment
              </a>
            </div>
          @endif

          @if($ticket->resolution_notes)
            <div class="alert alert-success mt-3 mb-0">
              <h6 class="fw-bold mb-1"><i class="bi bi-check-circle me-1"></i> Recorded Resolution Notes:</h6>
              <p class="mb-0 small">{{ $ticket->resolution_notes }}</p>
            </div>
          @endif
        </div>
      </div>

      {{-- Messages Thread --}}
      <h5 class="fw-bold mb-3"><i class="bi bi-chat-dots me-1"></i> Activity & Communications</h5>

      <div class="conversation-thread mb-4">
        @forelse($ticket->replies as $reply)
          @php
            $isStaff = (int)$reply->user_id === (int)auth()->id() || in_array($reply->user?->role, ['admin', 'super_admin']);
          @endphp
          <div class="d-flex {{ $isStaff ? 'justify-content-end' : 'justify-content-start' }} mb-3">
            <div class="card border-0 shadow-sm rounded-4 {{ $reply->is_internal ? 'bg-warning-subtle border border-warning' : ($isStaff ? 'bg-primary text-white' : 'bg-white') }}" style="max-width: 85%;">
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="fw-bold small {{ $reply->is_internal ? 'text-warning-emphasis' : ($isStaff ? 'text-white-50' : 'text-primary') }}">
                    {{ $reply->user?->name ?? 'User' }}
                    @if($reply->is_internal)
                      <span class="badge bg-warning text-dark ms-1">INTERNAL NOTE</span>
                    @elseif($isStaff)
                      <span class="badge bg-light text-dark ms-1">Staff</span>
                    @else
                      <span class="badge bg-secondary ms-1">Resident</span>
                    @endif
                  </span>
                  <small class="{{ $reply->is_internal ? 'text-muted' : ($isStaff ? 'text-white-50' : 'text-muted') }} ms-3 font-11">
                    {{ $reply->created_at->format('d M, h:i A') }}
                  </small>
                </div>
                <p class="mb-1" style="white-space: pre-line;">{{ $reply->message }}</p>

                @if($reply->attachment_url)
                  <div class="mt-2 pt-2 border-top {{ $isStaff && !$reply->is_internal ? 'border-white-50' : 'border-light' }}">
                    <a href="{{ $reply->attachment_url }}" target="_blank" class="btn btn-sm {{ $isStaff && !$reply->is_internal ? 'btn-light text-primary' : 'btn-outline-primary' }} rounded-pill py-0 px-2 font-11">
                      <i class="bi bi-paperclip me-1"></i> Attachment
                    </a>
                  </div>
                @endif
              </div>
            </div>
          </div>
        @empty
          <div class="text-center py-4 text-muted bg-white rounded-3 shadow-sm mb-3">
            <p class="mb-0 small">No replies on this ticket yet.</p>
          </div>
        @endforelse
      </div>

      {{-- Reply / Response Box --}}
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom">
          <h6 class="fw-bold mb-0"><i class="bi bi-reply me-1"></i> Send Reply or Internal Note</h6>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.tickets.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
              <textarea name="message" rows="3" class="form-control" placeholder="Write message to resident or staff note..." required></textarea>
            </div>
            <div class="row align-items-center">
              <div class="col-md-5 mb-2 mb-md-0">
                <input type="file" name="attachment" class="form-control form-control-sm" accept="image/png,image/jpeg,image/webp,application/pdf">
              </div>
              <div class="col-md-4 mb-2 mb-md-0">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="is_internal" value="1" id="internalCheck">
                  <label class="form-check-label small" for="internalCheck">
                    Internal Note (Resident won't see)
                  </label>
                </div>
              </div>
              <div class="col-md-3 text-end">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                  <i class="bi bi-send me-1"></i> Send
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    {{-- Right: Status & Resident Metadata --}}
    <div class="col-lg-4">
      {{-- Status Update Card --}}
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom">
          <h6 class="fw-bold mb-0">Update Ticket Status</h6>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.tickets.status', $ticket->id) }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="mb-3">
              <label class="form-label small fw-semibold">Status</label>
              <select name="status" class="form-select form-select-sm" required>
                <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-semibold">Resolution Notes (Optional)</label>
              <textarea name="resolution_notes" rows="3" class="form-control form-control-sm" placeholder="Explain what action was taken to resolve the issue...">{{ $ticket->resolution_notes }}</textarea>
            </div>

            <button type="submit" class="btn btn-success btn-sm w-100">
              <i class="bi bi-check2-circle me-1"></i> Update Status
            </button>
          </form>
        </div>
      </div>

      {{-- Assign Staff Card --}}
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom">
          <h6 class="fw-bold mb-0">Assign Staff / Resolver</h6>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.tickets.assign', $ticket->id) }}" method="POST">
            @csrf
            <div class="mb-3">
              <label class="form-label small fw-semibold">Assigned Person</label>
              <select name="assigned_to" class="form-select form-select-sm" required>
                <option value="">-- Select Staff / Resolver --</option>
                @foreach($staffUsers as $staff)
                  <option value="{{ $staff->id }}" {{ $ticket->assigned_to == $staff->id ? 'selected' : '' }}>
                    {{ $staff->name }} ({{ $staff->role ?? 'Staff' }})
                  </option>
                @endforeach
              </select>
            </div>
            <button type="submit" class="btn btn-outline-primary btn-sm w-100">
              <i class="bi bi-person-check me-1"></i> Assign Ticket
            </button>
          </form>
        </div>
      </div>

      {{-- Resident Info Card --}}
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom">
          <h6 class="fw-bold mb-0">Resident Information</h6>
        </div>
        <div class="card-body">
          <ul class="list-unstyled mb-0">
            <li class="py-2 border-bottom d-flex justify-content-between">
              <span class="text-muted small">Name:</span>
              <span class="fw-semibold">{{ $ticket->user?->name ?? 'N/A' }}</span>
            </li>
            <li class="py-2 border-bottom d-flex justify-content-between">
              <span class="text-muted small">Phone:</span>
              <span class="fw-semibold">{{ $ticket->user?->phone ?? 'N/A' }}</span>
            </li>
            <li class="py-2 border-bottom d-flex justify-content-between">
              <span class="text-muted small">Email:</span>
              <span class="small">{{ $ticket->user?->email ?? 'N/A' }}</span>
            </li>
            <li class="py-2 border-bottom d-flex justify-content-between">
              <span class="text-muted small">Society:</span>
              <span class="fw-semibold text-primary">{{ $ticket->society?->name ?? 'N/A' }}</span>
            </li>
            <li class="py-2 border-bottom d-flex justify-content-between">
              <span class="text-muted small">Priority:</span>
              <span>
                @if($ticket->priority === 'urgent')
                  <span class="badge bg-danger">Urgent</span>
                @elseif($ticket->priority === 'high')
                  <span class="badge bg-warning text-dark">High</span>
                @elseif($ticket->priority === 'medium')
                  <span class="badge bg-info text-dark">Medium</span>
                @else
                  <span class="badge bg-secondary">Low</span>
                @endif
              </span>
            </li>
            <li class="py-2 border-bottom d-flex justify-content-between">
              <span class="text-muted small">Raised On:</span>
              <span class="small">{{ $ticket->created_at->format('d M, Y h:i A') }}</span>
            </li>
            @if($ticket->resolved_at)
              <li class="py-2 d-flex justify-content-between">
                <span class="text-muted small">Resolved On:</span>
                <span class="small text-success">{{ $ticket->resolved_at->format('d M, Y h:i A') }}</span>
              </li>
            @endif
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
