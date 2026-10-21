@extends('member.layouts.app')

@section('title', $isResidentAdmin && $activeTab === 'society' ? 'Society Complaints & Helpdesk' : 'My Complaints & Tickets')

@section('content')
<div class="container-fluid py-4">
  {{-- Hero Banner --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);">
        <div class="card-body p-4 text-white d-flex flex-wrap justify-content-between align-items-center gap-3">
          <div>
            <h3 class="fw-bold mb-1">
              {{ $isResidentAdmin && $activeTab === 'society' ? 'Society Complaints & Helpdesk' : 'Complaints & Helpdesk' }}
            </h3>
            <p class="opacity-75 mb-0">
              {{ $isResidentAdmin && $activeTab === 'society' 
                 ? 'Review, respond to, and resolve complaints submitted by residents across your society.' 
                 : 'Raise issues regarding plumbing, electrical, maintenance, or security directly to your society management.' }}
            </p>
          </div>
          <div>
            <a href="{{ Route::has('member.tickets.create') ? route('member.tickets.create') : url('residents/tickets/create') }}" class="btn fw-bold rounded-pill px-4 shadow-sm" style="background: #ffffff !important; color: #0284c7 !important;">
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

  {{-- Admin Scope Toggle --}}
  @if($isResidentAdmin)
    <div class="card border-0 shadow-sm rounded-4 mb-3">
      <div class="card-body p-2 d-flex flex-wrap gap-2">
        <a href="{{ Route::has('member.tickets.index') ? route('member.tickets.index', ['view' => 'society', 'status' => request('status')]) : url('residents/tickets?view=society&status=' . request('status')) }}" class="btn {{ $activeTab === 'society' ? 'btn-primary shadow-sm' : 'btn-light' }} rounded-pill px-4 fw-semibold">
          <i class="mdi mdi-domain me-1"></i> All Society Complaints
          <span class="badge {{ $activeTab === 'society' ? 'bg-white text-primary' : 'bg-secondary text-white' }} ms-1 rounded-pill">{{ $societyTicketsCount }}</span>
        </a>
        <a href="{{ Route::has('member.tickets.index') ? route('member.tickets.index', ['view' => 'my', 'status' => request('status')]) : url('residents/tickets?view=my&status=' . request('status')) }}" class="btn {{ $activeTab === 'my' ? 'btn-primary shadow-sm' : 'btn-light' }} rounded-pill px-4 fw-semibold">
          <i class="mdi mdi-account me-1"></i> My Complaints
          <span class="badge {{ $activeTab === 'my' ? 'bg-white text-primary' : 'bg-secondary text-white' }} ms-1 rounded-pill">{{ $myTicketsCount }}</span>
        </a>
      </div>
    </div>
  @endif

  {{-- Filter Tabs --}}
  <div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ Route::has('member.tickets.index') ? route('member.tickets.index', ['view' => $activeTab]) : url('residents/tickets?view=' . $activeTab) }}" class="btn btn-sm {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }} rounded-pill px-3">
      All Statuses
    </a>
    <a href="{{ Route::has('member.tickets.index') ? route('member.tickets.index', ['view' => $activeTab, 'status' => 'open']) : url('residents/tickets?view=' . $activeTab . '&status=open') }}" class="btn btn-sm {{ request('status') == 'open' ? 'btn-warning text-dark' : 'btn-outline-secondary' }} rounded-pill px-3">
      Open
    </a>
    <a href="{{ Route::has('member.tickets.index') ? route('member.tickets.index', ['view' => $activeTab, 'status' => 'in_progress']) : url('residents/tickets?view=' . $activeTab . '&status=in_progress') }}" class="btn btn-sm {{ request('status') == 'in_progress' ? 'btn-info text-white' : 'btn-outline-secondary' }} rounded-pill px-3">
      In Progress
    </a>
    <a href="{{ Route::has('member.tickets.index') ? route('member.tickets.index', ['view' => $activeTab, 'status' => 'resolved']) : url('residents/tickets?view=' . $activeTab . '&status=resolved') }}" class="btn btn-sm {{ request('status') == 'resolved' ? 'btn-success' : 'btn-outline-secondary' }} rounded-pill px-3">
      Resolved
    </a>
    <a href="{{ Route::has('member.tickets.index') ? route('member.tickets.index', ['view' => $activeTab, 'status' => 'closed']) : url('residents/tickets?view=' . $activeTab . '&status=closed') }}" class="btn btn-sm {{ request('status') == 'closed' ? 'btn-secondary text-white' : 'btn-outline-secondary' }} rounded-pill px-3">
      Closed
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
              @if($isResidentAdmin && $activeTab === 'society')
                <th>Resident</th>
              @endif
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
                @if($isResidentAdmin && $activeTab === 'society')
                  <td>
                    <div class="fw-semibold text-dark">{{ $ticket->user?->name ?? 'Resident' }}</div>
                    <small class="text-muted d-block">
                      <i class="mdi mdi-phone me-1"></i>{{ $ticket->user?->phone ?? $ticket->user?->mobile ?? $ticket->user?->email ?? '-' }}
                    </small>
                    @if($ticket->user?->details?->flat)
                      <span class="badge bg-soft-primary text-primary mt-1">Flat: {{ $ticket->user->details->flat->flat_number }}</span>
                    @endif
                  </td>
                @endif
                <td>
                  <span class="badge bg-light text-dark border">{{ $ticket->category }}</span>
                </td>
                <td>
                  <span class="fw-semibold text-dark">{{ $ticket->title }}</span>
                </td>
                <td>
                  @if($ticket->priority === 'urgent')
                    <span class="badge bg-danger rounded-pill px-2">Urgent</span>
                  @elseif($ticket->priority === 'high')
                    <span class="badge bg-warning text-dark rounded-pill px-2">High</span>
                  @elseif($ticket->priority === 'medium')
                    <span class="badge bg-info text-dark rounded-pill px-2">Medium</span>
                  @else
                    <span class="badge bg-secondary rounded-pill px-2">Low</span>
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
                  <a href="{{ Route::has('member.tickets.show') ? route('member.tickets.show', $ticket->id) : url('residents/tickets/' . $ticket->id) }}" class="btn {{ $isResidentAdmin && $activeTab === 'society' ? 'btn-primary' : 'btn-outline-primary' }} btn-sm rounded-pill px-3">
                    <i class="mdi {{ $isResidentAdmin && $activeTab === 'society' ? 'mdi-tune' : 'mdi-forum-outline' }} me-1"></i>
                    {{ $isResidentAdmin && $activeTab === 'society' ? 'Manage & Chat' : 'View & Chat' }}
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="{{ $isResidentAdmin && $activeTab === 'society' ? '8' : '7' }}" class="text-center py-5 text-muted">
                  <i class="mdi mdi-message-alert-outline mdi-36px d-block mb-2 text-secondary"></i>
                  @if($isResidentAdmin && $activeTab === 'society')
                    No complaints found in your society matching this filter.
                  @else
                    No tickets found. Need assistance? Click "Raise New Ticket".
                  @endif
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="p-3">
        {{ $tickets->appends(request()->query())->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
