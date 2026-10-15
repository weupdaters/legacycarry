@extends('member.layouts.app')

@section('title', 'Ticket #' . $ticket->ticket_number)

@section('content')
<div class="container-fluid py-4">
  {{-- Header & Back --}}
  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('member.tickets.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
      <i class="mdi mdi-arrow-left me-1"></i> Back to Complaints
    </a>
    <div class="d-flex gap-2 align-items-center">
      @if($ticket->status !== 'closed')
        <form action="{{ route('member.tickets.close', $ticket->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to mark this ticket as closed?');">
          @csrf
          <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
            <i class="mdi mdi-check-circle-outline me-1"></i> Mark Resolved & Close
          </button>
        </form>
      @endif
    </div>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
      <i class="mdi mdi-check-circle me-2"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
      <i class="mdi mdi-alert-circle me-2"></i> {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="row">
    {{-- Left: Ticket Details & Conversation --}}
    <div class="col-lg-8 mb-4">
      {{-- Original Ticket Card --}}
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-start">
          <div>
            <span class="badge bg-light text-dark border mb-2">{{ $ticket->category }}</span>
            <h4 class="fw-bold text-dark mb-1">{{ $ticket->title }}</h4>
            <p class="text-muted small mb-0">Ticket #{{ $ticket->ticket_number }} • Raised on {{ $ticket->created_at->format('d M, Y \a\t h:i A') }}</p>
          </div>
          <div>
            @if($ticket->status === 'open')
              <span class="badge bg-soft-warning text-warning rounded-pill px-3 py-2 fs-6">Open</span>
            @elseif($ticket->status === 'in_progress')
              <span class="badge bg-soft-info text-info rounded-pill px-3 py-2 fs-6">In Progress</span>
            @elseif($ticket->status === 'resolved')
              <span class="badge bg-soft-success text-success rounded-pill px-3 py-2 fs-6">Resolved</span>
            @else
              <span class="badge bg-secondary rounded-pill px-3 py-2 fs-6">Closed</span>
            @endif
          </div>
        </div>
        <div class="card-body px-4 pb-4">
          <div class="p-3 rounded-3 bg-light text-dark mb-3">
            <p class="mb-0" style="white-space: pre-line;">{{ $ticket->description }}</p>
          </div>

          @if($ticket->attachment_url)
            <div class="mt-3">
              <label class="fw-semibold small text-muted d-block mb-1">Attached Document / Photo:</label>
              <a href="{{ $ticket->attachment_url }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                <i class="mdi mdi-paperclip me-1"></i> View Attachment
              </a>
            </div>
          @endif

          @if($ticket->resolution_notes)
            <div class="alert alert-success rounded-3 mt-3 mb-0">
              <h6 class="fw-bold mb-1"><i class="mdi mdi-check-all me-1"></i> Resolution Notes from Society Management:</h6>
              <p class="mb-0 small">{{ $ticket->resolution_notes }}</p>
            </div>
          @endif
        </div>
      </div>

      {{-- Conversation Replies Thread --}}
      <h5 class="fw-bold mb-3"><i class="mdi mdi-forum-outline me-1"></i> Communication History</h5>

      <div class="conversation-thread mb-4">
        @forelse($ticket->replies as $reply)
          @php
            $isMe = (int)$reply->user_id === (int)auth()->id();
          @endphp
          <div class="d-flex {{ $isMe ? 'justify-content-end' : 'justify-content-start' }} mb-3">
            <div class="card border-0 shadow-sm rounded-4 {{ $isMe ? 'bg-primary text-white' : 'bg-white text-dark' }}" style="max-width: 80%;">
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="fw-bold small {{ $isMe ? 'text-white-50' : 'text-primary' }}">
                    {{ $isMe ? 'You' : ($reply->user?->name ?? 'Management') }}
                    @if(!$isMe && $reply->user?->role)
                      <span class="badge bg-light text-dark ms-1">Staff</span>
                    @endif
                  </span>
                  <small class="{{ $isMe ? 'text-white-50' : 'text-muted' }} ms-3 font-11">
                    {{ $reply->created_at->format('d M, h:i A') }}
                  </small>
                </div>
                <p class="mb-1" style="white-space: pre-line;">{{ $reply->message }}</p>

                @if($reply->attachment_url)
                  <div class="mt-2 pt-2 border-top {{ $isMe ? 'border-white-50' : 'border-light' }}">
                    <a href="{{ $reply->attachment_url }}" target="_blank" class="btn btn-sm {{ $isMe ? 'btn-light text-primary' : 'btn-outline-primary' }} rounded-pill py-0 px-2 font-12">
                      <i class="mdi mdi-paperclip me-1"></i> View Attachment
                    </a>
                  </div>
                @endif
              </div>
            </div>
          </div>
        @empty
          <div class="text-center py-4 text-muted bg-white rounded-4 shadow-sm">
            <p class="mb-0 small">No messages yet. Society staff will respond to your ticket soon.</p>
          </div>
        @endforelse
      </div>

      {{-- Reply Form --}}
      @if($ticket->status !== 'closed')
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-body p-4">
            <h6 class="fw-bold mb-3"><i class="mdi mdi-reply me-1"></i> Post a Message / Reply</h6>
            <form action="{{ route('member.tickets.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="mb-3">
                <textarea name="message" rows="3" class="form-control rounded-3 @error('message') is-invalid @enderror" 
                          placeholder="Type your message or follow-up here..." required>{{ old('message') }}</textarea>
                @error('message')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="row align-items-center">
                <div class="col-md-7 mb-2 mb-md-0">
                  <input type="file" name="attachment" class="form-control form-control-sm rounded-3" 
                         accept="image/png,image/jpeg,image/webp,application/pdf">
                  <span class="text-muted font-11">Optional: Attach photo/doc (max 5MB)</span>
                </div>
                <div class="col-md-5 text-md-end">
                  <button type="submit" class="btn btn-primary rounded-pill px-4 btn-sm fw-semibold">
                    <i class="mdi mdi-send me-1"></i> Send Reply
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      @else
        <div class="alert alert-secondary rounded-4 text-center">
          <i class="mdi mdi-lock me-1"></i> This ticket is closed. If you still have issues, please raise a new ticket.
        </div>
      @endif
    </div>

    {{-- Right Sidebar: Ticket Meta --}}
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
          <h5 class="fw-bold mb-1">Ticket Information</h5>
        </div>
        <div class="card-body px-4 pb-4">
          <ul class="list-unstyled mb-0">
            <li class="py-2 border-bottom d-flex justify-content-between">
              <span class="text-muted">Ticket Number:</span>
              <span class="fw-bold text-primary">#{{ $ticket->ticket_number }}</span>
            </li>
            <li class="py-2 border-bottom d-flex justify-content-between">
              <span class="text-muted">Priority:</span>
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
              <span class="text-muted">Status:</span>
              <span class="text-capitalize fw-semibold">{{ str_replace('_', ' ', $ticket->status) }}</span>
            </li>
            <li class="py-2 border-bottom d-flex justify-content-between">
              <span class="text-muted">Created:</span>
              <span class="small">{{ $ticket->created_at->format('d M, Y h:i A') }}</span>
            </li>
            @if($ticket->resolved_at)
              <li class="py-2 border-bottom d-flex justify-content-between">
                <span class="text-muted">Resolved On:</span>
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
