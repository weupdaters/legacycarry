@extends('admin.layouts.app')

@section('content')
  <!-- Start Content-->
  <div class="container-fluid">

    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
      <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0"> Payments Management </h4>
        <div class="text-muted">Complete payment records | Exportable to CSV</div>
      </div>

      <div class="text-end mt-3 mt-sm-0">
        <a href="{{ route('admin.payments.export', request()->query()) }}" class="btn btn-success btn-sm me-2">
          <i class="bi bi-download"></i> Export CSV
        </a>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary btn-sm">Reset Filters</a>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card shadow-sm border-0">
          <div class="card-body">

            <div class="row g-3 mb-4">
              <!-- GLOBAL SEARCH -->
              <div class="col-md-3">
                <label class="filter-label">Search (payment id / reference / notes)</label>
                <input type="text" id="search" class="form-control"
                  placeholder="Search payment id, reference or notes..." value="{{ $filters['search'] ?? '' }}">
              </div>

              <div class="col-md-2">
                <label class="filter-label">Payer ID</label>
                <input type="number" id="payer_id" class="form-control" placeholder="payer id"
                  value="{{ $filters['payer_id'] ?? '' }}">
              </div>

              <div class="col-md-2">
                <label class="filter-label">Payee ID</label>
                <input type="number" id="payee_id" class="form-control" placeholder="payee id"
                  value="{{ $filters['payee_id'] ?? '' }}">
              </div>

              <!-- VERIFICATION STATUS -->
              <div class="col-md-2">
                <label class="filter-label">Status</label>
                <select id="status" class="form-select">
                  <option value="">All</option>
                  <option value="success"
                    {{ isset($filters['status']) && $filters['status'] == 'success' ? 'selected' : '' }}>Success</option>
                  <option value="failed"
                    {{ isset($filters['status']) && $filters['status'] == 'failed' ? 'selected' : '' }}>Failed</option>
                  <option value="pending"
                    {{ isset($filters['status']) && $filters['status'] == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
              </div>

              <div class="col-md-2">
                <label class="filter-label">Date range</label>
                <div class="d-flex gap-2">
                  <input type="date" id="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}">
                  <input type="date" id="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}">
                </div>
              </div>


            </div>

            <!-- TABLE -->
            <div class="table-responsive" id="searchTable">
              <div class="row g-3 mb-4">

                <div class="col-md-3">
                  <div class="card border-0 shadow-sm">
                    <div class="card-body">
                      <div class="text-muted small">Total Amount</div>
                      <h5 class="fw-bold text-primary mb-0">
                        ₹ {{ number_format($totalAmount ?? 0, 2) }}
                      </h5>
                    </div>
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="card border-0 shadow-sm">
                    <div class="card-body">
                      <div class="text-muted small">Advance Payments</div>
                      <h5 class="fw-bold text-warning mb-0">
                        ₹ {{ number_format($advanceAmount ?? 0, 2) }}
                      </h5>
                    </div>
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="card border-0 shadow-sm">
                    <div class="card-body">
                      <div class="text-muted small">Final Payments</div>
                      <h5 class="fw-bold text-success mb-0">
                        ₹ {{ number_format($finalAmount ?? 0, 2) }}
                      </h5>
                    </div>
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="card border-0 shadow-sm">
                    <div class="card-body">
                      <div class="text-muted small">Credit / Debit</div>
                      <div class="fw-semibold text-success">
                        + ₹ {{ number_format($totalCredit ?? 0, 2) }}
                      </div>
                      <div class="fw-semibold text-danger">
                        − ₹ {{ number_format($totalDebit ?? 0, 2) }}
                      </div>
                    </div>
                  </div>
                </div>

              </div>

            @section('table')
              <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th class="text-start">ID</th>
                    {{-- <th>Payment Identifier</th> --}}
                    <th>Payer</th>
                    <th>Payee</th>
                    <th class="text-end">Amount</th>
                    <th>Currency</th>
                    <th>Status</th>
                    <th>Paid At</th>
                    <th width="180">Actions</th>
                  </tr>
                </thead>

                <tbody>
                  @foreach ($payments as $payment)
                    <tr>
                      <td class="text-start">{{ $payment->id }}</td>

                      {{-- <td>
                        <div class="fw-semibold">{{ $payment->payment_id ?? '—' }}</div>
                        <div class="text-muted small">{{ $payment->payment_reference ?? '' }}</div>
                      </td> --}}

                      <td>
                        <div class="d-flex align-items-start gap-3">
                          {{-- Avatar --}}
                          {{-- <div
                            class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center"
                            style="width:40px;height:40px;">
                            {{ strtoupper(substr(optional($payment->payer)->name ?? 'U', 0, 1)) }}
                          </div> --}}

                          {{-- Details --}}
                          <div>
                            <div class="fw-semibold text-primary">
                              {{ optional($payment->payer)->name ?? 'Unknown User' }}
                              <span class="badge bg-primary-subtle text-primary ms-1">Payer</span>
                            </div>

                            <div class="text-muted small">
                              {{ optional($payment->payer)->email ?? '—' }}
                            </div>

                            <div class="text-muted small">
                              ID: {{ $payment->payer_id ?? '-' }}
                            </div>
                          </div>
                        </div>
                      </td>


                      <td>
                        <div class="d-flex align-items-start gap-3">
                          {{-- Avatar --}}
                          {{-- <div
                            class="rounded-circle bg-success text-white fw-bold d-flex align-items-center justify-content-center"
                            style="width:40px;height:40px;">
                            {{ strtoupper(substr(optional($payment->payee)->name ?? 'U', 0, 1)) }}
                          </div> --}}

                          {{-- Details --}}
                          <div>
                            <div class="fw-semibold text-success">
                              {{ optional($payment->payee)->name ?? 'Unknown User' }}
                              <span class="badge bg-success-subtle text-success ms-1">Payee</span>
                            </div>

                            <div class="text-muted small">
                              {{ optional($payment->payee)->email ?? '—' }}
                            </div>

                            <div class="text-muted small">
                              ID: {{ $payment->payee_id ?? '-' }}
                            </div>
                          </div>
                        </div>
                      </td>



                      <td class="text-end">
                        <span class="fw-semibold">{{ number_format($payment->amount, 2, '.', ',') }}</span>
                      </td>

                      <td>{{ $payment->currency ?? 'INR' }}</td>

                      <td>
                        @if ($payment->status === 'success')
                          <span class="badge bg-success">Success</span>
                        @elseif($payment->status === 'failed')
                          <span class="badge bg-danger">Failed</span>
                        @elseif($payment->status === 'pending')
                          <span class="badge bg-warning text-dark">Pending</span>
                        @else
                          <span class="badge bg-secondary">{{ $payment->status ?? '—' }}</span>
                        @endif
                      </td>

                      <td>{{ $payment->paid_at ? $payment->paid_at->toDateTimeString() : '-' }}</td>

                      <td>
                        <div class="d-flex gap-2">
                          <a href="{{ route('admin.payments.show', $payment->id) }}"
                            class="btn btn-outline-primary btn-sm">View</a>

                          <a href="{{ route('admin.payments.export', array_merge(request()->query(), ['payment_id' => $payment->payment_id])) }}"
                            class="btn btn-success btn-sm">Export</a>
                        </div>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>

              {{-- PAGINATION --}}
              <div class="d-flex justify-content-between align-items-center mt-3 px-2">
                <div>
                  Showing <strong>{{ $payments->firstItem() ?? 0 }}</strong>
                  to <strong>{{ $payments->lastItem() ?? 0 }}</strong> of
                  <strong>{{ $payments->total() }}</strong> entries
                </div>

                <div>
                  {!! $payments->onEachSide(2)->links('vendor.pagination.bootstrap-5') !!}
                </div>
              </div>
            @endsection

            @yield('table')

          </div>
          <!-- end table responsive -->

        </div>
      </div>
    </div>
  </div>

</div>
<!-- container-fluid -->
@endsection

@push('scripts')
<script>
  function getPaymentsUrl() {
    return "{{ route('admin.payments.index') }}";
  }

  function buildQueryData() {
    return {
      search: $('#search').val(),
      status: $('#status').val(),
      payer_id: $('#payer_id').val(),
      payee_id: $('#payee_id').val(),
      start_date: $('#start_date').val(),
      end_date: $('#end_date').val(),
      per_page: $('#per_page').val()
    };
  }

  function loadData(url = null) {
    url = url || getPaymentsUrl();

    $.ajax({
      url: url,
      type: "GET",
      data: buildQueryData(),
      success: function(response) {
        // Replace only the table portion
        const newHtml = $(response).find('#searchTable').html();
        if (newHtml) {
          $('#searchTable').html(newHtml);
          // scroll to table for better UX on mobile
          $('html, body').animate({
            scrollTop: $('#searchTable').offset().top - 100
          }, 200);
        } else {
          // fallback: reload whole page if partial not found
          window.location.reload();
        }
      },
      error: function() {
        alert('Failed to load data. Please refresh the page.');
      }
    });
  }

  $(document).ready(function() {

    // Trigger search on enter and on keyup (debounced)
    let typingTimer;
    const typingDelay = 400;

    $('#search').on('keyup', function(e) {
      clearTimeout(typingTimer);
      typingTimer = setTimeout(loadData, typingDelay);
    });

    $('#search').on('keydown', function() {
      clearTimeout(typingTimer);
    });

    // Filters
    $('#status, #payer_id, #payee_id, #start_date, #end_date, #per_page').on('change', function() {
      loadData();
    });

    // Pagination handling: links generated by Laravel will be intercepted
    $(document).on('click', '.pagination a', function(e) {
      e.preventDefault();
      const url = $(this).attr('href');
      if (url) loadData(url);
    });

  });
</script>
@endpush
