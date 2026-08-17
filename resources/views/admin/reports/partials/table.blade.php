<div class="table-responsive">
  <table class="table table-hover align-middle mb-0">
    <thead class="table-light">
      <tr>
        <th style="width: 70px;">#ID</th>
        <th>Reference</th>
        <th>Type</th>
        <th>Reported By</th>
        <th>Description</th>
        <th>Status</th>
        <th>Date</th>
        <th style="width: 140px;" class="text-end">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($reports as $report)
        <tr>
          <td><span class="text-muted fw-bold">#{{ $report->id }}</span></td>
          
          <td>
            <a href="{{ route('admin.reports.show', $report->id) }}" class="fw-semibold text-primary">
              {{ $report->reference ?? 'RPT-'.$report->id }}
            </a>
          </td>

          <td>
            @php
              $type = strtolower($report->report_type);
              $badgeClass = match($type) {
                'bug' => 'bg-danger-subtle text-danger border border-danger-subtle',
                'payment' => 'bg-warning-subtle text-warning border border-warning-subtle',
                'feedback' => 'bg-info-subtle text-info border border-info-subtle',
                'account' => 'bg-primary-subtle text-primary border border-primary-subtle',
                default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
              };
              $typeIcon = match($type) {
                'bug' => 'bi-bug-fill',
                'payment' => 'bi-credit-card-fill',
                'feedback' => 'bi-chat-quote-fill',
                'account' => 'bi-person-fill',
                default => 'bi-flag-fill',
              };
            @endphp
            <span class="badge {{ $badgeClass }} px-2 py-1">
              <i class="bi {{ $typeIcon }} me-1"></i> {{ ucfirst($report->report_type) }}
            </span>
          </td>

          <td>
            @if($report->user)
              <div class="d-flex align-items-center">
                @if($report->user->avatar_url)
                  <img src="{{ $report->user->avatar_url }}" class="rounded-circle me-2" style="width:32px; height:32px; object-fit:cover;">
                @else
                  <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width:32px; height:32px; font-size:12px;">
                    {{ strtoupper(substr($report->user->name, 0, 1)) }}
                  </div>
                @endif
                <div>
                  <div class="fw-semibold" style="font-size: 13px;">{{ $report->user->name }}</div>
                  <small class="text-muted">{{ $report->email ?? $report->user->email }}</small>
                  @if($report->user->role)
                    <span class="badge bg-light text-secondary border ms-1" style="font-size: 10px;">{{ ucfirst($report->user->role) }}</span>
                  @endif
                </div>
              </div>
            @else
              <div>
                <span class="text-muted">User #{{ $report->user_id }}</span>
                @if($report->email)
                  <br><small class="text-muted">{{ $report->email }}</small>
                @endif
              </div>
            @endif
          </td>

          <td style="max-width: 280px;">
            <div class="text-truncate" title="{{ $report->description }}">
              {{ $report->description }}
            </div>
          </td>

          <td>
            <div class="dropdown d-inline-block">
              @php
                $status = strtolower($report->status);
                $statusBadge = match($status) {
                  'open', 'pending' => 'bg-warning text-dark',
                  'in_progress' => 'bg-info text-white',
                  'resolved' => 'bg-success text-white',
                  'closed' => 'bg-secondary text-white',
                  default => 'bg-light text-dark',
                };
              @endphp
              <button class="btn btn-sm dropdown-toggle py-0 px-2 {{ $statusBadge }}" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 12px; font-weight: 500;">
                {{ ucfirst(str_replace('_', ' ', $report->status)) }}
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><a class="dropdown-item quick-status-btn" href="javascript:void(0)" data-id="{{ $report->id }}" data-status="open"><span class="badge bg-warning text-dark me-1">●</span> Open</a></li>
                <li><a class="dropdown-item quick-status-btn" href="javascript:void(0)" data-id="{{ $report->id }}" data-status="in_progress"><span class="badge bg-info text-white me-1">●</span> In Progress</a></li>
                <li><a class="dropdown-item quick-status-btn" href="javascript:void(0)" data-id="{{ $report->id }}" data-status="resolved"><span class="badge bg-success text-white me-1">●</span> Resolved</a></li>
                <li><a class="dropdown-item quick-status-btn" href="javascript:void(0)" data-id="{{ $report->id }}" data-status="closed"><span class="badge bg-secondary text-white me-1">●</span> Closed</a></li>
              </ul>
            </div>
          </td>

          <td>
            <span style="font-size: 12px;" class="text-muted">
              {{ $report->created_at ? $report->created_at->format('d M Y, h:i A') : '-' }}
            </span>
          </td>

          <td class="text-end">
            <div class="d-flex justify-content-end gap-1">
              {{-- View Details --}}
              <a href="{{ route('admin.reports.show', $report->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                <i class="bi bi-eye"></i> View
              </a>

              {{-- Delete --}}
              <form action="{{ route('admin.reports.destroy', $report->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this report?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Report">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="8" class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
            <h6>No reports match the selected filters</h6>
            <p class="small mb-0">Try changing your search terms, date range, or filter options.</p>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if($reports->hasPages())
  <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div class="text-muted small">
      Showing {{ $reports->firstItem() ?? 0 }} to {{ $reports->lastItem() ?? 0 }} of {{ $reports->total() }} reports
    </div>
    <div>
      {{ $reports->links() }}
    </div>
  </div>
@endif
