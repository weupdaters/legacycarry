@extends('admin.layouts.app')

@section('content')
  <!-- Start Content-->
  <div class="container-fluid">

    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
      <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0"> Assigned Residents Management
        </h4>

        <div class="text-end">
          <a href="{{ route('resident.assign.create') }}" class="btn btn-success btn-sm">Assign New Resident</a>
          <a href="{{ route('resident.assign.export', ['type' => 'excel']) }}" class="btn btn-success btn-sm">Export
            Excel</a>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-body">
        <h4 class="card-title">Account Ledger</h4>
        <p class="text-muted">Enter account id to view ledger. Debit = money out (payer). Credit = money in (payee).</p>

        <form class="row g-3" method="get" action="{{ route('payments.ledger') }}">
          <div class="col-md-3">
            <label for="account_id" class="form-label">Account ID (user id)</label>
            <input type="number" class="form-control" id="account_id" name="account_id"
              value="{{ old('account_id', $accountId ?? '') }}" required>
          </div>

          <div class="col-md-3">
            <label for="start_date" class="form-label">Start date</label>
            <input type="date" class="form-control" id="start_date" name="start_date"
              value="{{ old('start_date', $startDate ?? '') }}">
          </div>

          <div class="col-md-3">
            <label for="end_date" class="form-label">End date</label>
            <input type="date" class="form-control" id="end_date" name="end_date"
              value="{{ old('end_date', $endDate ?? '') }}">
          </div>

          <div class="col-md-3">
            <label for="status" class="form-label">Status (optional)</label>
            <input type="text" class="form-control" id="status" name="status"
              value="{{ old('status', $statusFilter ?? '') }}">
          </div>

          <div class="col-12">
            <button type="submit" class="btn btn-primary">Show Ledger</button>

            @if (isset($accountId))
              <a href="{{ route('payments.ledger.export', request()->query()) }}" class="btn btn-success ms-2">
                Export as Excel (CSV)
              </a>
            @endif
          </div>
        </form>
      </div>
    </div>

    @if (isset($openingBalance))
      <div class="card mb-4">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h5 class="mb-0">Opening balance</h5>
              <small class="text-muted">Balance as of {{ $startDate }}</small>
            </div>
            <div class="text-end">
              <h4 class="mb-0">{{ number_format($openingBalance, 2, '.', ',') }}</h4>
            </div>
          </div>
        </div>
      </div>
    @endif

    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <th style="min-width:140px">Date</th>
                <th>Payment ID</th>
                <th>Description</th>
                <th class="text-end">Debit</th>
                <th class="text-end">Credit</th>
                <th class="text-end">Running Balance</th>
                <th>Currency</th>
                <th>Method</th>
                <th>Reference</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($ledgerRows as $row)
                <tr>
                  <td>{{ $row['date'] }}</td>
                  <td>{{ $row['payment_id'] }}</td>
                  <td style="max-width:220px; white-space:normal;">{{ $row['description'] }}<br><small
                      class="text-muted">{{ $row['notes'] }}</small></td>
                  <td class="text-end text-debit">
                    {{ $row['debit'] > 0 ? number_format($row['debit'], 2, '.', ',') : '-' }}</td>
                  <td class="text-end text-credit">
                    {{ $row['credit'] > 0 ? number_format($row['credit'], 2, '.', ',') : '-' }}</td>
                  <td class="text-end">{{ number_format($row['balance'], 2, '.', ',') }}</td>
                  <td>{{ $row['currency'] }}</td>
                  <td>{{ $row['payment_method'] }}</td>
                  <td>{{ $row['payment_reference'] }}</td>
                  <td>{{ $row['status'] }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="10" class="text-center text-muted">No transactions found for the selected filters.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  @endsection
