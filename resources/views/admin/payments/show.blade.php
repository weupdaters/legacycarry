@extends('admin.layouts.app')

@section('content')
  <div class="container-fluid">

    <!-- PAGE HEADER -->
    <div class="py-3 d-flex align-items-center justify-content-between flex-wrap">
      <div>
        <h4 class="fs-18 fw-semibold mb-1">Payment Details</h4>
        <span class="text-muted">Full payment information</span>
      </div>

      <div class="d-flex gap-2 mt-2 mt-sm-0">
        <a href="{{ route('admin.payments.export', ['payment_id' => $payment->payment_id]) }}"
          class="btn btn-success btn-sm">
          Export CSV
        </a>

        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary btn-sm">
          Back to List
        </a>
      </div>
    </div>

    <div class="row">

      <!-- LEFT COLUMN -->
      <div class="col-lg-6">
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-header bg-light fw-semibold">
            Payment Information
          </div>

          <div class="card-body">

            <div class="mb-3">
              <label class="text-muted small">Payment Identifier</label>
              <div class="fw-semibold">{{ $payment->payment_id ?? '-' }}</div>
            </div>

            <div class="mb-3">
              <label class="text-muted small">Payment Reference</label>
              <div>{{ $payment->payment_reference ?? '-' }}</div>
            </div>

            <div class="mb-3">
              <label class="text-muted small">Payment Method</label>
              <div>{{ $payment->payment_method ?? '-' }}</div>
            </div>

            <div class="mb-3">
              <label class="text-muted small">Amount</label>
              <div class="fs-20 fw-bold text-primary">
                {{ number_format($payment->amount, 2, '.', ',') }}
                <span class="fs-14 text-muted">{{ $payment->currency }}</span>
              </div>
            </div>

            <div class="mb-3">
              <label class="text-muted small">Status</label><br>
              @if ($payment->status === 'success')
                <span class="badge bg-success">Success</span>
              @elseif($payment->status === 'failed')
                <span class="badge bg-danger">Failed</span>
              @elseif($payment->status === 'pending')
                <span class="badge bg-warning text-dark">Pending</span>
              @else
                <span class="badge bg-secondary">{{ $payment->status }}</span>
              @endif
            </div>

          </div>
        </div>
      </div>

      <!-- RIGHT COLUMN -->
      <div class="col-lg-6">
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-header bg-light fw-semibold">
            Parties Information
          </div>

          <div class="card-body">

            <div class="mb-3">
              <label class="text-muted small">Payer</label>
              <div class="fw-semibold">
                ID: {{ $payment->payer_id ?? '-' }}
              </div>
              <div class="text-muted small">
                {{ optional($payment->payer)->name }} <br>
                {{ optional($payment->payer)->email }}
              </div>
            </div>

            <div class="mb-3">
              <label class="text-muted small">Payee</label>
              <div class="fw-semibold">
                ID: {{ $payment->payee_id ?? '-' }}
              </div>
              <div class="text-muted small">
                {{ optional($payment->payee)->name }} <br>
                {{ optional($payment->payee)->email }}
              </div>
            </div>

            <div class="mb-3">
              <label class="text-muted small">Cash Verified By</label>
              <div>
                {{ $payment->cash_verified_by ?? '-' }}
              </div>
              <div class="text-muted small">
                {{ optional($payment->cashVerifiedBy)->name ?? '-' }}
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- FULL WIDTH -->
      <div class="col-12">
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-header bg-light fw-semibold">
            Dates & Notes
          </div>

          <div class="card-body">

            <div class="row mb-3">
              <div class="col-md-4">
                <label class="text-muted small">Paid At</label>
                <div>{{ $payment->paid_at ? $payment->paid_at->toDateTimeString() : '-' }}</div>
              </div>

              <div class="col-md-4">
                <label class="text-muted small">Created At</label>
                <div>{{ $payment->created_at ? $payment->created_at->toDateTimeString() : '-' }}</div>
              </div>

              <div class="col-md-4">
                <label class="text-muted small">Cash Verified At</label>
                <div>{{ $payment->cash_verified_at ? $payment->cash_verified_at->toDateTimeString() : '-' }}</div>
              </div>
            </div>

            <div>
              <label class="text-muted small">Notes</label>
              <div class="border rounded p-3 bg-light">
                {{ $payment->notes ?? 'No notes available.' }}
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>

  </div>
@endsection
