@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">

  <!-- Header -->
  <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column justify-content-between">
    <div>
      <h4 class="fs-18 fw-semibold m-0"><i class="bi bi-wallet2 text-primary me-2"></i>Withdrawal & Payout Management</h4>
      <div class="text-muted small">Manage user withdrawal requests, Cashfree auto-payouts, and manual payment approvals.</div>
    </div>
    <div class="d-flex gap-2 mt-3 mt-sm-0 flex-wrap">
      <button type="button" class="btn btn-success btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#directPayoutModal">
        <i class="bi bi-send-plus-fill me-1"></i> Send Payout Money
      </button>
      <form action="{{ route('admin.withdrawals.processBatchAuto') }}" method="POST" class="d-inline" onsubmit="return confirm('Process all eligible pending withdrawal requests via Cashfree Auto-Payout now?')">
        @csrf
        <button type="submit" class="btn btn-primary btn-sm shadow-sm">
          <i class="bi bi-lightning-charge-fill me-1"></i> Process Auto-Payouts
        </button>
      </form>
      <a href="{{ route('admin.withdrawals.export', request()->query()) }}" class="btn btn-outline-success btn-sm shadow-sm">
        <i class="bi bi-download me-1"></i> Export CSV
      </a>
      <a href="{{ route('settings', ['tab' => 'withdrawal']) }}" class="btn btn-outline-primary btn-sm shadow-sm">
        <i class="bi bi-gear me-1"></i> Set Payout Limits
      </a>
      <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
    </div>
  </div>

  <!-- Alerts -->
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger shadow-sm">
      <ul class="mb-0">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <!-- KPI Metrics Cards -->
  <div class="row g-3 mb-4">
    <div class="col-xl col-md-4 col-sm-6">
      <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold text-uppercase">Completed</div>
              <h5 class="fw-bold text-success mb-0 mt-1">₹ {{ number_format($totalCompleted ?? 0, 2) }}</h5>
              <div class="text-muted small mt-1">{{ $totalCompletedCount ?? 0 }} Paid Transfers</div>
            </div>
            <div class="avatar-sm bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
              <i class="bi bi-check2-circle fs-4"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl col-md-4 col-sm-6">
      <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold text-uppercase">Pending</div>
              <h5 class="fw-bold text-warning mb-0 mt-1">₹ {{ number_format($totalPending ?? 0, 2) }}</h5>
              <div class="text-muted small mt-1">{{ $totalPendingCount ?? 0 }} Requests</div>
            </div>
            <div class="avatar-sm bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
              <i class="bi bi-clock-history fs-4"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl col-md-4 col-sm-6">
      <div class="card border-0 shadow-sm border-start border-4 border-dark h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold text-uppercase">On Hold</div>
              <h5 class="fw-bold text-dark mb-0 mt-1">₹ {{ number_format($totalHold ?? 0, 2) }}</h5>
              <div class="text-muted small mt-1">{{ $totalHoldCount ?? 0 }} Held Requests</div>
            </div>
            <div class="avatar-sm bg-dark-subtle text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
              <i class="bi bi-pause-circle fs-4"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl col-md-4 col-sm-6">
      <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold text-uppercase">Auto (Cashfree)</div>
              <h5 class="fw-bold text-primary mb-0 mt-1">₹ {{ number_format($totalAutoPayout ?? 0, 2) }}</h5>
              <div class="text-muted small mt-1">{{ $totalAutoPayoutCount ?? 0 }} Transfers</div>
            </div>
            <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
              <i class="bi bi-lightning-charge fs-4"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl col-md-4 col-sm-6">
      <div class="card border-0 shadow-sm border-start border-4 border-danger h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold text-uppercase">Rejected</div>
              <h5 class="fw-bold text-danger mb-0 mt-1">₹ {{ number_format($totalRejected ?? 0, 2) }}</h5>
              <div class="text-muted small mt-1">{{ $totalRejectedCount ?? 0 }} Rejected</div>
            </div>
            <div class="avatar-sm bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
              <i class="bi bi-x-circle fs-4"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter & List Section -->
  <div class="card shadow-sm border-0 mb-4">
    <div class="card-body">

      <!-- Filters Form -->
      <form method="GET" action="{{ route('admin.withdrawals.index') }}" class="row g-3 mb-4 align-items-end">
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Search User / ID / Reference</label>
          <input type="text" name="search" class="form-control form-control-sm" placeholder="User name, phone, WD ID, UTR..." value="{{ request('search') }}">
        </div>

        <div class="col-md-2">
          <label class="form-label small fw-semibold">Status</label>
          <select name="status" class="form-select form-select-sm">
            <option value="all">All Statuses</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="hold" {{ request('status') == 'hold' ? 'selected' : '' }}>On Hold</option>
            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label small fw-semibold">Payout Method</label>
          <select name="payout_type" class="form-select form-select-sm">
            <option value="all">All Types</option>
            <option value="bank" {{ request('payout_type') == 'bank' ? 'selected' : '' }}>Bank Account</option>
            <option value="upi" {{ request('payout_type') == 'upi' ? 'selected' : '' }}>UPI (VPA)</option>
            <option value="wallet" {{ request('payout_type') == 'wallet' ? 'selected' : '' }}>Wallet</option>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label small fw-semibold">Payout Mode</label>
          <select name="mode" class="form-select form-select-sm">
            <option value="all">All Modes</option>
            <option value="auto" {{ request('mode') == 'auto' ? 'selected' : '' }}>Auto (Cashfree)</option>
            <option value="manual" {{ request('mode') == 'manual' ? 'selected' : '' }}>Manual</option>
          </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
            <i class="bi bi-funnel me-1"></i> Filter
          </button>
          <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
        </div>
      </form>

      <!-- Withdrawals Table -->
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 140px;">Withdrawal ID</th>
              <th>User Details</th>
              <th>Amount</th>
              <th>Payout Target (Bank/UPI)</th>
              <th>Mode</th>
              <th>Status</th>
              <th>Date / Time</th>
              <th class="text-end" style="min-width: 220px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($withdrawals as $item)
              @php
                $details = is_array($item->payout_details) ? $item->payout_details : [];
                $masked = $details['masked'] ?? ($details['upi_id'] ?? ($details['account_number'] ?? 'N/A'));
              @endphp
              <tr>
                <!-- ID -->
                <td>
                  <span class="fw-bold text-dark font-monospace">{{ $item->withdrawal_id }}</span>
                  @if($item->cf_reference_id)
                    <div class="text-muted small" title="Reference / UTR ID">
                      <span class="badge bg-light text-secondary border">UTR: {{ Str::limit($item->cf_reference_id, 14) }}</span>
                    </div>
                  @endif
                </td>

                <!-- User -->
                <td>
                  <div class="d-flex align-items-center">
                    <div>
                      <div class="fw-semibold text-dark">{{ $item->user?->name ?? 'Deleted User' }}</div>
                      <div class="text-muted small">{{ $item->user?->phone ?? $item->user?->email }}</div>
                      <span class="badge bg-info-subtle text-info" style="font-size: 10px;">{{ ucfirst($item->user?->role ?? 'User') }}</span>
                    </div>
                  </div>
                </td>

                <!-- Amount -->
                <td>
                  <div class="fs-15 fw-bold text-dark">₹ {{ number_format($item->amount, 2) }}</div>
                </td>

                <!-- Target Account -->
                <td>
                  <div class="d-flex align-items-center gap-1">
                    @if($item->payout_type === 'upi')
                      <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-qr-code me-1"></i>UPI</span>
                      <span class="small fw-semibold text-dark">{{ $details['upi_id'] ?? $masked }}</span>
                    @else
                      <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bi bi-bank me-1"></i>Bank</span>
                      <div>
                        <div class="small fw-semibold text-dark">{{ $details['bank_name'] ?? 'Bank' }} ({{ $masked }})</div>
                        @if(!empty($details['ifsc']))
                          <div class="text-muted" style="font-size: 11px;">IFSC: {{ $details['ifsc'] }}</div>
                        @endif
                      </div>
                    @endif
                  </div>
                </td>

                <!-- Mode -->
                <td>
                  @if($item->is_auto_payout)
                    <span class="badge bg-primary-subtle text-primary border"><i class="bi bi-lightning-charge me-1"></i>Auto</span>
                  @else
                    <span class="badge bg-secondary-subtle text-secondary border"><i class="bi bi-person me-1"></i>Manual</span>
                  @endif
                </td>

                <!-- Status -->
                <td>
                  @switch($item->status)
                    @case('completed')
                      <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Completed</span>
                      @break
                    @case('pending')
                      <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pending</span>
                      @break
                    @case('hold')
                      <span class="badge bg-dark text-white"><i class="bi bi-pause-circle me-1"></i>On Hold</span>
                      @break
                    @case('processing')
                      <span class="badge bg-info text-dark"><i class="bi bi-arrow-repeat me-1"></i>Processing</span>
                      @break
                    @case('rejected')
                      <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                      @break
                    @default
                      <span class="badge bg-secondary">{{ ucfirst($item->status) }}</span>
                  @endswitch
                </td>

                <!-- Date -->
                <td>
                  <div class="small">{{ $item->created_at->format('d M Y') }}</div>
                  <div class="text-muted" style="font-size: 11px;">{{ $item->created_at->format('h:i A') }}</div>
                </td>

                <!-- Actions -->
                <td class="text-end">
                  <div class="d-flex justify-content-end gap-1 flex-wrap">

                    @if(in_array($item->status, ['pending', 'failed', 'hold']))
                      <!-- Pay via Cashfree Direct Transfer -->
                      <form action="{{ route('admin.withdrawals.processPayout', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Initiate instant transfer of ₹{{ number_format($item->amount, 2) }} via Cashfree Payout API?')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Transfer via Cashfree Gateway">
                          <i class="bi bi-send-fill me-1"></i> Pay API
                        </button>
                      </form>

                      <!-- Mark Manual Paid Modal Trigger -->
                      <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#manualPaidModal{{ $item->id }}" title="Mark as Paid Manually">
                        <i class="bi bi-check2-circle me-1"></i> Paid
                      </button>

                      @if($item->status === 'hold')
                        <!-- Unhold / Release Trigger -->
                        <form action="{{ route('admin.withdrawals.unhold', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Release this withdrawal request back to Pending status?')">
                          @csrf
                          <button type="submit" class="btn btn-sm btn-outline-info" title="Release from Hold">
                            <i class="bi bi-play-circle me-1"></i> Release
                          </button>
                        </form>
                      @else
                        <!-- Hold Modal Trigger -->
                        <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#holdModal{{ $item->id }}" title="Put on Hold">
                          <i class="bi bi-pause-circle me-1"></i> Hold
                        </button>
                      @endif

                      <!-- Reject Modal Trigger -->
                      <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $item->id }}" title="Reject Request">
                        <i class="bi bi-x-circle me-1"></i> Reject
                      </button>
                    @endif

                    <!-- View Details Modal Trigger -->
                    <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#viewModal{{ $item->id }}" title="View Full Details">
                      <i class="bi bi-eye"></i>
                    </button>
                  </div>

                  <!-- ==========================================
                       MODAL: MANUAL PAID
                       ========================================== -->
                  <div class="modal fade text-start" id="manualPaidModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                      <form action="{{ route('admin.withdrawals.markManualPaid', $item->id) }}" method="POST">
                        @csrf
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-check2-circle text-success me-2"></i>Mark as Paid Manually</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <div class="alert alert-info py-2 small">
                              <strong>User:</strong> {{ $item->user?->name }} | <strong>Amount:</strong> ₹ {{ number_format($item->amount, 2) }}
                              <br><strong>Account:</strong> {{ $masked }}
                            </div>
                            <div class="mb-3">
                              <label class="form-label fw-semibold">Bank UTR / Transaction Reference <span class="text-danger">*</span></label>
                              <input type="text" name="reference_id" class="form-control" placeholder="e.g. UTR1234567890 / IMPS Reference" required>
                              <div class="form-text">Enter the bank transfer UTR or UPI transaction reference.</div>
                            </div>
                            <div class="mb-3">
                              <label class="form-label fw-semibold">Admin Notes (Optional)</label>
                              <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Paid via ICICI Netbanking"></textarea>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success btn-sm">Confirm Payment</button>
                          </div>
                        </div>
                      </form>
                    </div>
                  </div>

                  <!-- ==========================================
                       MODAL: HOLD
                       ========================================== -->
                  <div class="modal fade text-start" id="holdModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                      <form action="{{ route('admin.withdrawals.hold', $item->id) }}" method="POST">
                        @csrf
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title text-warning"><i class="bi bi-pause-circle text-warning me-2"></i>Hold Payment Request</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <div class="alert alert-warning py-2 small">
                              Putting this request on <strong>Hold</strong> will pause payouts until further verification. The funds remain safely reserved.
                            </div>
                            <div class="mb-3">
                              <label class="form-label fw-semibold">Reason / Note for Hold <span class="text-danger">*</span></label>
                              <textarea name="hold_reason" class="form-control" rows="3" placeholder="e.g. Bank details verification pending / Suspicious activity check" required></textarea>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning btn-sm">Confirm Put on Hold</button>
                          </div>
                        </div>
                      </form>
                    </div>
                  </div>

                  <!-- ==========================================
                       MODAL: REJECT
                       ========================================== -->
                  <div class="modal fade text-start" id="rejectModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                      <form action="{{ route('admin.withdrawals.reject', $item->id) }}" method="POST">
                        @csrf
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title text-danger"><i class="bi bi-x-circle text-danger me-2"></i>Reject Withdrawal Request</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <div class="alert alert-warning py-2 small">
                              Rejecting this request will immediately refund <strong>₹ {{ number_format($item->amount, 2) }}</strong> back to <strong>{{ $item->user?->name }}'s</strong> available balance.
                            </div>
                            <div class="mb-3">
                              <label class="form-label fw-semibold">Rejection Note / Reason <span class="text-danger">*</span></label>
                              <textarea name="rejection_reason" class="form-control" rows="3" placeholder="e.g. Invalid bank account details or incorrect IFSC" required></textarea>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger btn-sm">Reject & Refund Balance</button>
                          </div>
                        </div>
                      </form>
                    </div>
                  </div>

                  <!-- ==========================================
                       MODAL: VIEW DETAILS
                       ========================================== -->
                  <div class="modal fade text-start" id="viewModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title"><i class="bi bi-info-circle text-primary me-2"></i>Withdrawal Request Details</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                          <div class="row g-3">
                            <div class="col-md-6">
                              <div class="card bg-light border-0 p-3 h-100">
                                <h6 class="fw-bold mb-2 text-primary">Request Information</h6>
                                <div class="small mb-1"><strong>Withdrawal ID:</strong> {{ $item->withdrawal_id }}</div>
                                <div class="small mb-1"><strong>Amount:</strong> <span class="fw-bold fs-6 text-success">₹ {{ number_format($item->amount, 2) }}</span></div>
                                <div class="small mb-1"><strong>Status:</strong> <span class="badge bg-secondary">{{ strtoupper($item->status) }}</span></div>
                                <div class="small mb-1"><strong>Mode:</strong> {{ $item->is_auto_payout ? 'Auto (Cashfree)' : 'Manual' }}</div>
                                <div class="small mb-1"><strong>Requested Date:</strong> {{ $item->created_at->format('d M Y, h:i A') }}</div>
                                @if($item->paid_at)
                                  <div class="small mb-1"><strong>Paid At:</strong> {{ $item->paid_at->format('d M Y, h:i A') }}</div>
                                @endif
                              </div>
                            </div>

                            <div class="col-md-6">
                              <div class="card bg-light border-0 p-3 h-100">
                                <h6 class="fw-bold mb-2 text-primary">User Details</h6>
                                <div class="small mb-1"><strong>Name:</strong> {{ $item->user?->name }}</div>
                                <div class="small mb-1"><strong>Email:</strong> {{ $item->user?->email }}</div>
                                <div class="small mb-1"><strong>Phone:</strong> {{ $item->user?->phone }}</div>
                                <div class="small mb-1"><strong>Role:</strong> {{ ucfirst($item->user?->role ?? 'user') }}</div>
                              </div>
                            </div>

                            <div class="col-12">
                              <div class="card bg-light border-0 p-3">
                                <h6 class="fw-bold mb-2 text-primary">Payout Account Details</h6>
                                <div class="row g-2 small">
                                  <div class="col-md-4"><strong>Type:</strong> {{ strtoupper($item->payout_type) }}</div>
                                  <div class="col-md-8"><strong>Details:</strong>
                                    @if($item->payout_type === 'upi')
                                      UPI ID: {{ $details['upi_id'] ?? 'N/A' }}
                                    @else
                                      Bank: {{ $details['bank_name'] ?? 'N/A' }}, Acc: {{ $details['account_number'] ?? ($details['masked'] ?? 'N/A') }}, IFSC: {{ $details['ifsc'] ?? 'N/A' }}
                                    @endif
                                  </div>
                                </div>
                              </div>
                            </div>

                            <!-- Job & Final Payment Source Audit Log -->
                            <div class="col-12">
                              <div class="card border-0 bg-light p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                  <h6 class="fw-bold m-0 text-primary"><i class="bi bi-briefcase me-1"></i>Verified Job & Final Payment Earnings (Kis Job Se Paise Aaye Hain)</h6>
                                  <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-shield-check me-1"></i>Final Payments Only</span>
                                </div>
                                
                                @php
                                  $sourceJobs = $item->source_payments;
                                @endphp

                                @if(!empty($sourceJobs) && count($sourceJobs) > 0)
                                  <div class="table-responsive bg-white rounded border">
                                    <table class="table table-sm table-striped align-middle mb-0" style="font-size: 12px;">
                                      <thead class="table-light">
                                        <tr>
                                          <th>Job / Task</th>
                                          <th>Client / Employer</th>
                                          <th>Earned Amount</th>
                                          <th>Payment Date</th>
                                          <th>Payment Ref / ID</th>
                                          <th>Status</th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                        @foreach($sourceJobs as $src)
                                          <tr>
                                            <td class="fw-semibold text-dark">{{ $src['job_title'] ?? 'Job #' . ($src['job_id'] ?? 'N/A') }}</td>
                                            <td>
                                              {{ $src['payer_name'] ?? 'Client' }}
                                              @if(!empty($src['payer_phone']))
                                                <span class="text-muted d-block" style="font-size: 10px;">{{ $src['payer_phone'] }}</span>
                                              @endif
                                            </td>
                                            <td class="fw-bold text-success">₹ {{ number_format($src['amount'] ?? 0, 2) }}</td>
                                            <td>{{ $src['paid_at'] ?? 'N/A' }}</td>
                                            <td><code class="small">{{ $src['reference'] ?? ($src['payment_id'] ?? '-') }}</code></td>
                                            <td><span class="badge bg-success" style="font-size: 9px;">Final Verified</span></td>
                                          </tr>
                                        @endforeach
                                      </tbody>
                                    </table>
                                  </div>
                                @else
                                  <div class="text-muted small py-2 text-center bg-white rounded border">
                                    <i class="bi bi-exclamation-circle text-warning me-1"></i> No prior completed final payments found for this user.
                                  </div>
                                @endif
                              </div>
                            </div>

                            @if($item->cf_transfer_id || $item->cf_reference_id || $item->admin_notes || $item->rejection_reason)
                              <div class="col-12">
                                <div class="card border-0 bg-light p-3">
                                  <h6 class="fw-bold mb-2 text-primary">Gateway & Processing Audit</h6>
                                  @if($item->cf_transfer_id)
                                    <div class="small mb-1"><strong>Cashfree Transfer ID:</strong> <code>{{ $item->cf_transfer_id }}</code></div>
                                  @endif
                                  @if($item->cf_reference_id)
                                    <div class="small mb-1"><strong>Reference / Bank UTR:</strong> <code>{{ $item->cf_reference_id }}</code></div>
                                  @endif
                                  @if($item->admin_notes)
                                    <div class="small mb-1"><strong>Admin Notes:</strong> {{ $item->admin_notes }}</div>
                                  @endif
                                  @if($item->rejection_reason)
                                    <div class="small text-danger mb-1"><strong>Rejection Reason:</strong> {{ $item->rejection_reason }}</div>
                                  @endif
                                  @if($item->processedBy)
                                    <div class="small text-muted mt-1">Processed By: {{ $item->processedBy->name }} on {{ $item->processed_at?->format('d M Y, h:i A') }}</div>
                                  @endif
                                </div>
                              </div>
                            @endif
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        </div>
                      </div>
                    </div>
                  </div>

                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center py-4 text-muted">
                  <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                  No withdrawal requests found matching your filters.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="small text-muted">
          Showing {{ $withdrawals->firstItem() ?? 0 }} to {{ $withdrawals->lastItem() ?? 0 }} of {{ $withdrawals->total() }} withdrawal records
        </div>
        <div>
          {{ $withdrawals->links('pagination::bootstrap-5') }}
        </div>
      </div>

    </div>
  </div>

</div>

<!-- ==========================================
     MODAL: SEND DIRECT PAYOUT MONEY
     ========================================== -->
<div class="modal fade" id="directPayoutModal" tabindex="-1" aria-labelledby="directPayoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title fw-bold" id="directPayoutModalLabel">
          <i class="bi bi-send-fill me-2"></i>Send Direct Money Payout (Cashfree API)
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="alert alert-info border-0 shadow-sm py-2 mb-3">
          <i class="bi bi-info-circle me-1"></i> Send payout money directly to any bank account or UPI ID using Cashfree live transfer.
        </div>

        <form id="directPayoutModalForm" autocomplete="off">
          @csrf
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Payout Rail / Type <span class="text-danger">*</span></label>
              <select class="form-select" id="modal_payout_type" name="payout_type" required>
                <option value="bank" selected>🏦 Bank Account (IMPS - Instant Transfer)</option>
                <option value="upi">📱 UPI ID (VPA)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Amount to Transfer (₹) <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text fw-bold">₹</span>
                <input type="number" step="0.01" min="1" class="form-control fw-bold" id="modal_payout_amount" name="amount" placeholder="Enter amount" autocomplete="off" required>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small">Beneficiary Full Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="modal_payout_name" name="name" placeholder="Enter beneficiary name" autocomplete="off" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Beneficiary Phone Number</label>
              <input type="text" class="form-control" id="modal_payout_phone" name="phone" placeholder="Enter 10-digit mobile number" autocomplete="off">
            </div>

            <!-- Bank fields -->
            <div class="col-md-6 modal-bank-field">
              <label class="form-label fw-semibold small">Bank Account Number <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="modal_payout_account" name="account_number" placeholder="Enter bank account number" autocomplete="off">
            </div>
            <div class="col-md-6 modal-bank-field">
              <label class="form-label fw-semibold small">Bank IFSC Code <span class="text-danger">*</span></label>
              <input type="text" class="form-control text-uppercase" id="modal_payout_ifsc" name="ifsc" placeholder="e.g. HDFC0001234" autocomplete="off">
            </div>

            <!-- UPI field -->
            <div class="col-md-12 modal-upi-field" style="display: none;">
              <label class="form-label fw-semibold small">UPI VPA ID <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="modal_payout_upi" name="upi_id" placeholder="e.g. username@okhdfcbank" autocomplete="off">
              <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i>Note: UPI mode must be enabled on your Cashfree wallet.</div>
            </div>

            <div class="col-md-12">
              <label class="form-label fw-semibold small">Transfer Remarks / Notes</label>
              <input type="text" class="form-control" id="modal_payout_remarks" name="remarks" placeholder="Enter transfer remarks (optional)" autocomplete="off">
            </div>
          </div>

          <div id="modalPayoutResult" class="mt-3" style="display: none;"></div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success btn-sm px-4" id="modalSubmitPayoutBtn">
          <i class="bi bi-send-fill me-1"></i> ⚡ Transfer Money Now
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const typeSelect = document.getElementById('modal_payout_type');
  const bankFields = document.querySelectorAll('.modal-bank-field');
  const upiFields = document.querySelectorAll('.modal-upi-field');

  if (typeSelect) {
    typeSelect.addEventListener('change', function() {
      if (this.value === 'upi') {
        bankFields.forEach(el => el.style.display = 'none');
        upiFields.forEach(el => el.style.display = 'block');
      } else {
        bankFields.forEach(el => el.style.display = 'block');
        upiFields.forEach(el => el.style.display = 'none');
      }
    });
  }

  const payoutModalEl = document.getElementById('directPayoutModal');
  if (payoutModalEl) {
    payoutModalEl.addEventListener('show.bs.modal', function () {
      if (resultDiv) {
        resultDiv.style.display = 'none';
        resultDiv.innerHTML = '';
      }
      const formEl = document.getElementById('directPayoutModalForm');
      if (formEl) {
        formEl.reset();
        // default back to bank
        if (typeSelect) {
          typeSelect.value = 'bank';
          bankFields.forEach(el => el.style.display = 'block');
          upiFields.forEach(el => el.style.display = 'none');
        }
      }
    });
  }

  const submitBtn = document.getElementById('modalSubmitPayoutBtn');
  const resultDiv = document.getElementById('modalPayoutResult');

  if (submitBtn) {
    submitBtn.addEventListener('click', function() {
      const type = typeSelect.value;
      const amount = parseFloat(document.getElementById('modal_payout_amount').value);
      const name = document.getElementById('modal_payout_name').value;
      const phone = document.getElementById('modal_payout_phone').value;
      const remarks = document.getElementById('modal_payout_remarks').value;
      const account = document.getElementById('modal_payout_account').value;
      const ifsc = document.getElementById('modal_payout_ifsc').value;
      const upi = document.getElementById('modal_payout_upi').value;

      if (!amount || amount < 1) {
        alert('Please enter a valid payout amount (min ₹1.00).');
        return;
      }

      if (type === 'bank' && (!account || !ifsc)) {
        alert('Please enter Bank Account Number and IFSC Code.');
        return;
      }

      if (type === 'upi' && !upi) {
        alert('Please enter UPI ID.');
        return;
      }

      if (!confirm('Are you sure you want to transfer ₹' + amount.toFixed(2) + ' directly via Cashfree now?')) {
        return;
      }

      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';
      resultDiv.style.display = 'block';
      resultDiv.innerHTML = '<div class="alert alert-info py-2 mb-0"><span class="spinner-border spinner-border-sm me-2"></span>Connecting to Cashfree Gateway...</div>';

      fetch("{{ route('admin.withdrawals.testPayoutTransfer') }}", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": "{{ csrf_token() }}",
          "Accept": "application/json"
        },
        body: JSON.stringify({
          payout_type: type,
          amount: amount,
          name: name,
          phone: phone,
          remarks: remarks,
          account_number: account,
          ifsc: ifsc,
          upi_id: upi
        })
      })
      .then(res => res.json())
      .then(resp => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-send-fill me-1"></i> ⚡ Transfer Money Now';

        if (resp.success) {
          const st = (resp.status || 'SUCCESS').toUpperCase();
          const badgeBg = st === 'SUCCESS' ? 'bg-success' : 'bg-warning text-dark';
          resultDiv.innerHTML = `
            <div class="alert alert-success border shadow-sm mb-0">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Payout Transfer Dispatched!</strong>
                <span class="badge ${badgeBg}">${st}</span>
              </div>
              <div class="small">
                <div><strong>Transfer ID:</strong> <code>${resp.transfer_id || 'N/A'}</code></div>
                <div><strong>Cashfree Reference ID:</strong> <code>${resp.reference_id || 'N/A'}</code></div>
                <div><strong>Message:</strong> ${resp.message || 'Payment initiated'}</div>
              </div>
            </div>
          `;
        } else {
          resultDiv.innerHTML = `
            <div class="alert alert-danger border shadow-sm mb-0">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <strong class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>Payout Rejected / Failed</strong>
                <span class="badge bg-danger">${resp.status || 'FAILED'}</span>
              </div>
              <div class="small">${resp.message || 'Payment could not be processed.'}</div>
            </div>
          `;
        }
      })
      .catch(err => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-send-fill me-1"></i> ⚡ Transfer Money Now';
        resultDiv.innerHTML = `<div class="alert alert-danger mb-0 py-2"><i class="bi bi-exclamation-triangle-fill me-1"></i> Error connecting to server: ${err.message}</div>`;
      });
    });
  }
});
</script>
@endsection
