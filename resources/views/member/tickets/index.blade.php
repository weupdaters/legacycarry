@extends('member.layouts.app')

@section('title', 'My Complaints & Tickets')

@section('content')
<div class="container-fluid py-4">
  {{-- Hero Banner --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);">
        <div class="card-body p-4 text-white d-flex justify-content-between align-items-center">
          <div>
            <h3 class="fw-bold mb-1">Complaints & Helpdesk</h3>
            <p class="opacity-75 mb-0">Raise issues regarding plumbing, electrical, maintenance, or security directly to your society management.</p>
          </div>
          <div>
            <a href="{{ route('member.tickets.create') }}" class="btn btn-white text-primary fw-bold rounded-pill px-4">
              <i class="mdi mdi-plus-circle me-1"></i> Raise New Ticket
            </a>
          </div>
        </div>
      </div>
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

  {{-- Filter Tabs --}}
  <div class="d-flex gap-2 mb-3">
    <a href="{{ route('member.tickets.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }} rounded-pill px-3">
      All Tickets
    </a>
    <a href="{{ route('member.tickets.index', ['status' => 'open']) }}" class="btn btn-sm {{ request('status') == 'open' ? 'btn-warning text-dark' : 'btn-outline-secondary' }} rounded-pill px-3">
      Open
    </a>
    <a href="{{ route('member.tickets.index', ['status' => 'in_progress']) }}" class="btn btn-sm {{ request('status') == 'in_progress' ? 'btn-info text-white' : 'btn-outline-secondary' }} rounded-pill px-3">
      In Progress
    </a>
    <a href="{{ route('member.tickets.index', ['status' => 'resolved']) }}" class="btn btn-sm {{ request('status') == 'resolved' ? 'btn-success' : 'btn-outline-secondary' }} rounded-pill px-3">
      Resolved
    </a>
  </div>

  {{-- Tickets Table --}}
  <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4">Ticket #</th>
              <th>Category</th>
              <th>Subject / Title</th>
              <th>Priority</th>
              <th>Status</th>
              <th>Created On</th>
              <th class="text-end pe-4">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($tickets as $ticket)
              <tr>
                <td class="ps-4 fw-bold text-primary">#{{ $ticket->ticket_number }}</td>
                <td>
                  <span class="badge bg-light text-dark border">{{ $ticket->category }}</span>
                </td>
                <td>
                  <span class="fw-semibold text-dark">{{ $ticket->title }}</span>
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
                    <span class="badge bg-soft-warning text-warning rounded-pill px-3 py-1">Open</span>
                  @elseif($ticket->status === 'in_progress')
                    <span class="badge bg-soft-info text-info rounded-pill px-3 py-1">In Progress</span>
                  @elseif($ticket->status === 'resolved')
                    <span class="badge bg-soft-success text-success rounded-pill px-3 py-1">Resolved</span>
                  @else
                    <span class="badge bg-secondary rounded-pill px-3 py-1">Closed</span>
                  @endif
                </td>
                <td><small class="text-muted">{{ $ticket->created_at->format('d M, Y H:i') }}</small></td>
                <td class="text-end pe-4">
                  <a href="{{ route('member.tickets.show', $ticket->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    View & Chat
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                  <i class="mdi mdi-message-alert-outline mdi-36px d-block mb-2 text-secondary"></i>
                  No tickets found. Need assistance? Click "Raise New Ticket".
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
