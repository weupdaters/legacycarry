@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">

  {{-- PAGE HEADER --}}
  <div class="py-3 d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
      <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Reports
      </a>
      <h4 class="fw-semibold m-0 ms-2">
        Report Details <span class="text-muted fs-6">({{ $report->reference ?? 'RPT-'.$report->id }})</span>
      </h4>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="row g-4">

    {{-- LEFT COLUMN: REPORT DETAILS --}}
    <div class="col-lg-8">

      {{-- MAIN REPORT CARD --}}
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center py-3">
          <span class="d-flex align-items-center">
            <i class="bi bi-info-circle text-primary me-2 fs-5"></i> Issue Information
          </span>

          @php
            $type = strtolower($report->report_type);
            $badgeClass = match($type) {
              'bug' => 'bg-danger text-white',
              'payment' => 'bg-warning text-dark',
              'feedback' => 'bg-info text-white',
              'account' => 'bg-primary text-white',
              default => 'bg-secondary text-white',
            };
          @endphp
          <span class="badge {{ $badgeClass }} px-3 py-2 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
            {{ $report->report_type }}
          </span>
        </div>

        <div class="card-body p-4">
          <div class="row mb-3">
            <div class="col-sm-4 text-muted fw-semibold">Reference ID:</div>
            <div class="col-sm-8 fw-bold text-primary">{{ $report->reference ?? 'RPT-'.$report->id }}</div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-4 text-muted fw-semibold">Report Type:</div>
            <div class="col-sm-8">{{ ucfirst($report->report_type) }}</div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-4 text-muted fw-semibold">Contact Email:</div>
            <div class="col-sm-8">{{ $report->email ?? ($report->user->email ?? 'Not provided') }}</div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-4 text-muted fw-semibold">Date Submitted:</div>
            <div class="col-sm-8">{{ $report->created_at ? $report->created_at->format('d M Y, h:i A') : '-' }}</div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-4 text-muted fw-semibold">Current Status:</div>
            <div class="col-sm-8">
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
              <span class="badge {{ $statusBadge }} px-3 py-1">
                {{ ucfirst(str_replace('_', ' ', $report->status)) }}
              </span>
            </div>
          </div>

          <hr class="my-4">

          <div>
            <h6 class="fw-bold mb-2 text-muted text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">
              <i class="bi bi-file-text me-1"></i> Description / Issue Details
            </h6>
            <div class="p-3 bg-light rounded border text-dark" style="white-space: pre-line; line-height: 1.6; font-size: 14px;">
              {{ $report->description }}
            </div>
          </div>
        </div>
      </div>

      {{-- STATUS UPDATE & ADMIN NOTES --}}
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light fw-bold py-3">
          <i class="bi bi-pencil-square text-primary me-2"></i> Update Status & Admin Notes
        </div>
        <div class="card-body p-4">
          <form action="{{ route('admin.reports.update-status', $report->id) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="mb-3">
              <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
              <select name="status" id="status" class="form-select w-auto">
                <option value="open" {{ $report->status == 'open' ? 'selected' : '' }}>Open / Pending</option>
                <option value="in_progress" {{ $report->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="resolved" {{ $report->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="closed" {{ $report->status == 'closed' ? 'selected' : '' }}>Closed</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="admin_notes" class="form-label fw-semibold">Admin Notes / Resolution Remarks</label>
              <textarea name="admin_notes" id="admin_notes" rows="4" class="form-control" placeholder="Add internal notes or details on how this bug was fixed...">{{ old('admin_notes', $report->admin_notes) }}</textarea>
              <small class="text-muted">These notes are visible to administrators.</small>
            </div>

            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check2-circle me-1"></i> Save Changes
            </button>
          </form>
        </div>
      </div>

    </div>


    {{-- RIGHT COLUMN: REPORTER INFORMATION --}}
    <div class="col-lg-4">

      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light fw-bold py-3">
          <i class="bi bi-person-circle text-primary me-2"></i> Reported By User
        </div>
        <div class="card-body p-4">

          @if($report->user)
            <div class="d-flex align-items-center mb-3">
              @if($report->user->avatar_url)
                <img src="{{ $report->user->avatar_url }}" class="rounded-circle me-3" style="width: 55px; height: 55px; object-fit: cover; border: 2px solid #e2e8f0;">
              @else
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 55px; height: 55px; font-size: 20px; font-weight: 700;">
                  {{ strtoupper(substr($report->user->name, 0, 1)) }}
                </div>
              @endif

              <div>
                <h6 class="mb-0 fw-bold">{{ $report->user->name }}</h6>
                <small class="text-muted">User ID: #{{ $report->user->id }}</small>
                <div>
                  <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 11px;">
                    {{ ucfirst($report->user->role ?? 'User') }}
                  </span>
                </div>
              </div>
            </div>

            <table class="table table-borderless table-sm mb-0">
              <tr>
                <td class="text-muted fw-semibold" style="width: 90px;">Email:</td>
                <td>{{ $report->user->email }}</td>
              </tr>
              <tr>
                <td class="text-muted fw-semibold">Phone:</td>
                <td>{{ $report->user->phone ?? '-' }}</td>
              </tr>
              <tr>
                <td class="text-muted fw-semibold">Joined:</td>
                <td>{{ $report->user->created_at ? $report->user->created_at->format('d M Y') : '-' }}</td>
              </tr>
            </table>

            @if($report->user->role == 'customer')
              <div class="mt-3 pt-3 border-top">
                <a href="{{ route('residents.view', $report->user->id) }}" class="btn btn-outline-primary btn-sm w-100">
                  <i class="bi bi-person me-1"></i> View Resident Profile
                </a>
              </div>
            @elseif($report->user->role == 'employee')
              <div class="mt-3 pt-3 border-top">
                <a href="{{ route('employees.view', $report->user->id) }}" class="btn btn-outline-primary btn-sm w-100">
                  <i class="bi bi-person me-1"></i> View Employee Profile
                </a>
              </div>
            @endif

          @else
            <div class="text-muted py-2">
              <p class="mb-1"><strong>User ID:</strong> {{ $report->user_id }}</p>
              <p class="mb-0"><strong>Contact Email:</strong> {{ $report->email ?? 'Not provided' }}</p>
            </div>
          @endif

        </div>
      </div>

    </div>

  </div>

</div>
@endsection
