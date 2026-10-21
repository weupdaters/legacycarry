@extends('layouts.app')

@section('content')

<style>
    .glass-card {
        backdrop-filter: blur(12px);
        background: rgba(255,255,255,0.55);
        border-radius: 18px;
        border: 1px solid rgba(255,255,255,0.25);
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    }
    .profile-avatar {
        width: 85px;height:85px;border-radius:50%;object-fit:cover;
        box-shadow:0 6px 18px rgba(0,0,0,0.2);
    }
    .section-title {
        font-size: 13px;font-weight:700;color:#6c757d;text-transform:uppercase;
        margin-bottom:6px;
    }
    .gradient-header {
        background: linear-gradient(135deg, #4e73df, #1cc88a);
        color:white;border-radius:18px 18px 0 0;
    }
    .highlight-box {
        background:#f1f9ff;border-left:5px solid #007bff;padding:10px;border-radius:10px;
        margin-bottom:12px;font-size:14px;
    }
</style>

<div class="container-fluid">

    {{-- PAGE TITLE --}}
    <div class="py-3 d-flex justify-content-between align-items-center">
        <h4 class="fw-semibold m-0">Employer Profile</h4>
        <a href="{{ route('employees.index') }}" class="btn btn-secondary btn-sm">Back</a>
    </div>

    {{-- USER HEADER --}}
    <div class="glass-card p-4 d-flex gap-4 align-items-center mb-4">

        {{-- Avatar --}}
        <div>
            @if($user->avatar && file_exists(public_path('storage/'.$user->avatar)))
                <img src="{{ asset('storage/'.$user->avatar) }}" class="profile-avatar">
            @else
                <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center"
                     style="width:85px;height:85px;font-size:34px;">
                     {{ strtoupper($user->name[0]) }}
                </div>
            @endif
        </div>

        {{-- Info --}}
        <div class="flex-grow-1">
            <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
            <div class="text-muted">
                <i class="bi bi-envelope"></i> {{ $user->email }} • 
                <i class="bi bi-telephone"></i> {{ $user->phone }}
            </div>
        </div>

        {{-- Status --}}
        <div class="mt-3 p-3 border rounded shadow-sm bg-light">

            {{-- Current Status Badge --}}
            <div class="mb-2 d-flex align-items-center">
                <strong class="me-2">Current Status:</strong>
        
                <span class="badge 
                    @if($user->verification_status=='verified') bg-success 
                    @elseif($user->verification_status=='pending') bg-warning 
                    @elseif($user->verification_status=='rejected') bg-danger 
                    @else bg-secondary 
                    @endif
                    px-3 py-2">
                    <i data-feather="
                        @if($user->verification_status=='verified') check-circle
                        @elseif($user->verification_status=='pending') clock
                        @elseif($user->verification_status=='rejected') x-circle
                        @else help-circle
                        @endif
                    " class="me-1"></i>
        
                    {{ ucfirst($user->verification_status) }}
                </span>
            </div>
        
        
            {{-- DROPDOWN + SUBMIT BUTTON --}}
            <form action="{{ route('employees.update-verification', $user->id) }}" 
                  method="POST" 
                  class="d-flex align-items-center mt-2">
                @csrf
                @method('PATCH')
        
                <strong class="me-2">Change:</strong>
        
                <select class="form-select form-select-sm w-auto me-3" name="verification_status">
                    <option value="unverified" {{ $user->verification_status == 'unverified' ? 'selected' : '' }}>
                        Unverified
                    </option>
                    <option value="pending" {{ $user->verification_status == 'pending' ? 'selected' : '' }}>
                        Pending
                    </option>
                    <option value="verified" {{ $user->verification_status == 'verified' ? 'selected' : '' }}>
                        Verified
                    </option>
                    <option value="rejected" {{ $user->verification_status == 'rejected' ? 'selected' : '' }}>
                        Rejected
                    </option>
                </select>
        
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-arrow-repeat me-1"></i> Update
                </button>
            </form>
        
        </div>
        
        
    </div>



    {{-- EMPLOYEE DETAILS --}}
    @if($user->employee)
    <div class="glass-card p-4 mb-4">

        <div class="section-title">Important Employee Information</div>

        {{-- Highlight Important --}}
        <div class="highlight-box">
            <strong>Experience:</strong> {{ $user->employee->experience_years }} years • 
            <strong>Hourly Rate:</strong> ₹{{ number_format($user->employee->hourly_rate,2) }} • 
            <strong>Availability:</strong> {{ $user->employee->availability }}
        </div>

        <div class="row">

            {{-- Work Info --}}
            <div class="col-md-4 mb-3">
                <div class="section-title">Work Details</div>

                @php
                    $services = $user->employee->services;
                    $services = is_string($services) ? json_decode($services,true) : $services;
                @endphp

                <p><strong>Services:</strong> {{ $services ? implode(', ', $services) : '-' }}</p>
                <p><strong>Work Radius:</strong> {{ $user->employee->work_radius_km }} km</p>
                <p><strong>About:</strong> {{ $user->employee->about ?? '-' }}</p>
            </div>

            {{-- Verification --}}
            <div class="col-md-4 mb-3">
                <div class="section-title">Verification</div>

                <p><strong>ID Number:</strong> {{ $user->employee->id_number }}</p>
                <p><strong>Phone Verified:</strong>
                    <span class="badge bg-{{ $user->employee->phone_verified ? 'success' : 'danger' }}">
                        {{ $user->employee->phone_verified ? 'Yes' : 'No' }}
                    </span>
                </p>

                <div class="section-title mt-3">Documents</div>

                <p>
                    <strong>ID Proof:</strong><br>
                    @if($user->employee->id_proof_path)
                        <a href="{{ asset('uploads/id_proof/'.$user->employee->id_proof_path) }}"
                           class="btn btn-sm btn-primary" target="_blank">View ID</a>
                    @else
                        <span class="text-muted">Not uploaded</span>
                    @endif
                </p>

                <p>
                    <strong>Selfie:</strong><br>
                    @if($user->employee->selfie_path)
                        <img src="{{ asset('uploads/selfies/'.$user->employee->selfie_path) }}"
                             style="width:70px;height:70px;border-radius:8px;object-fit:cover;">
                    @else
                        <span class="text-muted">Not uploaded</span>
                    @endif
                </p>
            </div>

        </div>
    </div>
    @endif



    {{-- PAYMENT METHODS --}}
  
        @if($payments->count())

        @php
            $primary = $payments->where('is_primary', 1)->first() ?? $payments->first();
            $others = $payments->where('id', '!=', $primary->id);
        
            $primaryDetails = is_array($primary->details)
                ? $primary->details
                : json_decode($primary->details, true);
        @endphp
        
        <div class="glass-card mb-4">
        
            <div class="gradient-header p-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="mdi mdi-credit-card-check"></i> Payment Methods</h5>
                <span class="badge bg-success">Primary: {{ strtoupper($primary->method_type) }}</span>
            </div>
        
            <div class="p-4 row">
        
                {{-- PRIMARY LEFT --}}
                <div class="col-md-6 mb-3">
                    <div class="section-title">Primary Method</div>
        
                    <p class="mb-1"><strong>{{ strtoupper($primary->method_type) }} — {{ ucfirst($primary->provider) }}</strong></p>
        
                    @foreach($primaryDetails as $k => $v)
                        <small class="d-block">{{ ucwords(str_replace('_',' ', $k)) }}: {{ $v }}</small>
                    @endforeach
        
                    <small class="text-muted d-block mt-2">Added: {{ $primary->created_at->format('d M Y') }}</small>
        
                    <p class="mt-2 mb-0">
                        <strong>Status:</strong>
                        <span class="badge bg-{{ 
                            $primary->verification_status == 'verified' ? 'success' :
                            ($primary->verification_status == 'failed' ? 'danger' :
                            ($primary->verification_status == 'pending' ? 'warning' : 'secondary')) 
                        }}">
                            {{ ucfirst($primary->verification_status) }}
                        </span>
    
                    </p>
                    <div class="mt-2 d-flex gap-2">
    
                        <!-- APPROVE -->
                        <form action="{{ route('payments.updateStatus', $primary->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="verified">
                            <button class="btn btn-success btn-sm">Approve</button>
                        </form>
                    
                        <!-- REJECT -->
                        <form action="{{ route('payments.updateStatus', $primary->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="failed">
                            <button class="btn btn-danger btn-sm">Reject</button>
                        </form>
                    
                        <!-- BLOCK -->
                        <form action="{{ route('payments.updateStatus', $primary->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="unverified">
                            <button class="btn btn-warning btn-sm text-dark">Block</button>
                        </form>
                    
                    </div>
                    
                </div>
        
                {{-- OTHER METHODS RIGHT --}}
                <div class="col-md-6 mb-3">
                    <div class="section-title">Other Methods</div>
        
                    @if($others->count())
                        @foreach($others as $p)
        
                            @php
                                $details = is_array($p->details)
                                    ? $p->details
                                    : json_decode($p->details, true);
                            @endphp
        
                            <div class="border rounded p-2 mb-2">
                                <strong>{{ strtoupper($p->method_type) }} — {{ ucfirst($p->provider) }}</strong>
        
                                @foreach($details as $k => $v)
                                    <small class="d-block">{{ ucwords(str_replace('_', ' ', $k)) }}: {{ $v }}</small>
                                @endforeach
        
                                <small class="text-muted d-block mt-1">Added: {{ $p->created_at->format('d M Y') }}</small>
        
                                <small>
                                    <strong>Status:</strong>
                                    <span class="badge bg-{{ 
                                        $p->verification_status == 'verified' ? 'success' :
                                        ($p->verification_status == 'failed' ? 'danger' :
                                        ($p->verification_status == 'pending' ? 'warning' : 'secondary')) 
                                    }}">
                                        {{ ucfirst($p->verification_status) }}
                                    </span>
                                    
                                </small>
    
                                <div class="d-flex flex-wrap gap-2 mt-2 justify-content-end">
    
                                    @if($p->verification_status !== 'verified')
                                        <form action="{{ route('payments.updateStatus', $p->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="verified">
                                            <button class="btn btn-success btn-sm px-3">Approve</button>
                                        </form>
                                    @endif
                                
                                    @if($p->verification_status !== 'failed')
                                        <form action="{{ route('payments.updateStatus', $p->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="failed">
                                            <button class="btn btn-danger btn-sm px-3">Reject</button>
                                        </form>
                                    @endif
                                
                                    @if($p->verification_status !== 'unverified')
                                        <form action="{{ route('payments.updateStatus', $p->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="unverified">
                                            <button class="btn btn-warning btn-sm px-3 text-dark">Block</button>
                                        </form>
                                    @endif
                                
                                </div>
                                
                            </div>
        
                        @endforeach
                    @else
                        <small class="text-muted">No other payment methods</small>
                    @endif
                </div>
        
            </div>
        </div>
        
        @else
        <div class="glass-card p-4 text-center text-muted mb-4">
            <i class="mdi mdi-information-outline"></i> No payment methods added.
        </div>
        @endif




    {{-- JOBS CREATED --}}
    <div class="glass-card mb-5">

        <div class="gradient-header p-3">
            <h5 class="fw-bold m-0"><i class="bi bi-briefcase-fill"></i> Jobs Created</h5>
        </div>

        <table class="table table-hover mb-0">
            <thead class="bg-light">
                <tr>
                    <th>ID</th>
                    <th>Job Title</th>
                    <th>Pay</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>View Job</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jobs as $job)
                    <tr>
                        <td>{{ $job->id }}</td>
                        <td>{{ $job->job_title }}</td>
                        <td>₹{{ number_format($job->pay_amount,2) }}</td>
                        <td>
                            <span class="badge bg-info">{{ ucfirst($job->status) }}</span>
                        </td>
                        <td>{{ $job->created_at->format('d M Y') }}</td>
                        <td><button class="btn btn-sm btn-primary"
                                onclick="window.location='{{ route('jobs.view', $job->id) }}'">
                                View Job
                            </button></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">
                            No jobs created.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>


    {{-- DOCUMENT VERIFICATION SECTION --}}
    <div class="glass-card mb-4">
        <div class="gradient-header p-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">
                <i data-feather="file-text"></i> Documents Verification
            </h5>
        </div>

        <div class="p-4">

            @if($documents->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Document Type</th>
                                <th>File</th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th>Updated At</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($documents as $doc)
                            <tr>
                                <td>{{ $doc->id }}</td>

                                <td class="fw-semibold text-capitalize">{{ str_replace('_', ' ', $doc->document_type) }}</td>

                                <td>
                                    <a href="{{ asset($doc->file_path) }}" 
                                    target="_blank" 
                                    class="btn btn-outline-primary btn-sm">
                                        <i data-feather="download"></i> View
                                    </a>
                                </td>

                                {{-- STATUS DROPDOWN --}}
                                <td>
                                    <form action="{{ route('documents.update-status', $doc->id) }}" method="POST" class="d-flex">
                                        @csrf
                                        @method('PATCH')

                                        <select name="status" class="form-select form-select-sm w-auto">
                                            <option value="pending"  {{ $doc->status=='pending'  ? 'selected' : '' }}>Pending</option>
                                            <option value="approved" {{ $doc->status=='approved' ? 'selected' : '' }}>Approved</option>
                                            <option value="rejected" {{ $doc->status=='rejected' ? 'selected' : '' }}>Rejected</option>
                                        </select>
                                </td>

                                {{-- REMARKS --}}
                                <td>
                                    <input type="text" name="remarks" class="form-control form-control-sm"
                                        value="{{ $doc->remarks ?? '' }}"
                                        placeholder="Add notes...">
                                </td>

                                <td>{{ $doc->updated_at->format('d M Y | h:i A') }}</td>

                                {{-- SUBMIT --}}
                                <td>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i data-feather="check-circle"></i> Update
                                    </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            @else
                <div class="text-center text-muted py-3">
                    <i data-feather="info"></i> No documents uploaded by this user.
                </div>
            @endif

        </div>
    </div>

</div>

@endsection


@push('scripts')

    <script>
$(document).ready(function () {
    $('#verifySwitch').change(function () {
        if ($(this).is(':checked')) {
            $('#verifyLabel').text('Verified');
        } else {
            $('#verifyLabel').text('Unverified');
        }

        // Auto submit form
        $('#verifyForm').submit();
    });
});

$(document).ready(function () {
    function updateColor() {
        if ($('#verifySwitch').is(':checked')) {
            $('#verifySwitch').addClass('bg-success').removeClass('bg-danger');
        } else {
            $('#verifySwitch').addClass('bg-danger').removeClass('bg-success');
        }
    }

    updateColor();

    $('#verifySwitch').change(function () {
        updateColor();
    });
});

</script>
    
@endpush