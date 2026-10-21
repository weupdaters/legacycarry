@extends('admin.layouts.app')

@section('content')

<style>
    .info-label {
        font-weight: 600;
        width: 180px;
        color: #555;
    }
    .info-value {
        font-weight: 500;
    }
    .user-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #ddd;
    }
    .map-container {
        width: 100%;
        height: 260px;
        border-radius: 10px;
        overflow: hidden;
    }
    .doc-preview-img {
        max-width: 100%;
        max-height: 72vh;
        object-fit: contain;
        border-radius: 8px;
    }
    .doc-thumb {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        cursor: pointer;
        transition: transform 0.15s ease-in-out;
    }
    .doc-thumb:hover {
        transform: scale(1.08);
    }
    /* Activity Timeline Styling */
    .timeline-container {
        position: relative;
        padding-left: 28px;
    }
    .timeline-container::before {
        content: '';
        position: absolute;
        top: 14px;
        bottom: 14px;
        left: 11px;
        width: 2px;
        background: #e2e8f0;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }
    .timeline-item:last-child {
        margin-bottom: 0;
    }
    .timeline-marker {
        position: absolute;
        left: -28px;
        top: 2px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        color: #fff;
        z-index: 2;
        box-shadow: 0 0 0 3px #fff;
    }
    .timeline-content {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px 16px;
        transition: all 0.2s ease;
    }
    .timeline-content:hover {
        background: #ffffff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        border-color: #cbd5e1;
    }
    .timeline-timestamp {
        font-size: 12px;
        color: #64748b;
        font-weight: 500;
    }
</style>

<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="py-3 d-flex justify-content-between align-items-center">
        <h4 class="fw-semibold m-0">
            <i class="bi bi-briefcase-fill me-1"></i> Job Details
        </h4>

        <a href="{{ route('jobs.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>


    <div class="row g-4">

        {{-- LEFT SIDE - JOB DETAILS --}}
        <div class="col-lg-8">

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header fw-bold bg-light d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-info-circle me-1"></i> Job Information</span>
                    @if($job->status === 'active')
                        <span class="badge bg-success">Active</span>
                    @elseif($job->status === 'completed')
                        <span class="badge bg-primary">Completed</span>
                    @elseif($job->status === 'cancelled')
                        <span class="badge bg-danger">Cancelled</span>
                    @else
                        <span class="badge bg-warning">{{ ucfirst($job->status) }}</span>
                    @endif
                </div>

                <div class="card-body p-3">

                    {{-- JOB TITLE --}}
                    <h5 class="fw-bold mb-3">{{ $job->job_title }}</h5>

                    <table class="table table-borderless align-middle">
                        <tr>
                            <td class="info-label">Location:</td>
                            <td class="info-value">
                                <i class="bi bi-geo-alt text-danger"></i>
                                {{ $job->location ?? 'Not provided' }}
                            </td>
                        </tr>

                        <tr>
                            <td class="info-label">Address:</td>
                            <td class="info-value">{{ $job->address ?? '-' }}</td>
                        </tr>

                        <tr>
                            <td class="info-label">Job Type:</td>
                            <td class="info-value">
                                <span class="badge bg-light text-dark border">
                                    {{ ucfirst(str_replace('_', ' ', $job->job_type)) }}
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td class="info-label">Workers Required:</td>
                            <td class="info-value">{{ $job->workers_required }}</td>
                        </tr>

                        <tr>
                            <td class="info-label">Skills Required:</td>
                            <td class="info-value">
                                @php
                                    $skills = is_array($job->skills_required)
                                        ? $job->skills_required
                                        : json_decode($job->skills_required, true);
                                @endphp
                                @if($skills && is_array($skills) && count($skills))
                                    @foreach($skills as $skill)
                                        <span class="badge bg-primary me-1 mb-1">{{ $skill }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td class="info-label">Tools Provided:</td>
                            <td class="info-value">
                                @if($job->tools_provided)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i> Yes
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        <i class="bi bi-x-circle-fill me-1"></i> No
                                    </span>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td class="info-label">Documents Required:</td>
                            <td class="info-value">
                                @php
                                    $docRequirements = is_array($job->documents_required)
                                        ? $job->documents_required
                                        : json_decode($job->documents_required, true);
                                @endphp
                                @if($docRequirements && is_array($docRequirements) && count($docRequirements))
                                    @foreach($docRequirements as $docReq)
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 me-1 mb-1">
                                            <i class="bi bi-file-earmark-text me-1"></i>{{ $docReq }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td class="info-label">Safety Instructions:</td>
                            <td class="info-value">{{ $job->safety_instructions ?? '-' }}</td>
                        </tr>

                        <tr>
                            <td class="info-label">Pay:</td>
                            <td class="info-value">
                                <span class="badge bg-success">
                                    {{ ucfirst(str_replace('_', ' ', $job->pay_type)) }} - ₹{{ number_format($job->pay_amount, 2) }}
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td class="info-label">Advance Payment:</td>
                            <td class="info-value">
                                @if($job->advance_payment)
                                    <span class="text-success fw-bold">
                                        <i class="bi bi-check-circle-fill me-1"></i>Yes
                                    </span>
                                    (₹{{ number_format($job->advance_amount, 2) }})
                                @else
                                    <span class="text-danger">
                                        <i class="bi bi-x-circle-fill me-1"></i>No
                                    </span>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td class="info-label">Start Date:</td>
                            <td class="info-value">{{ $job->start_date ? \Carbon\Carbon::parse($job->start_date)->format('Y-m-d H:i:s') : '-' }}</td>
                        </tr>

                        <tr>
                            <td class="info-label">End Date:</td>
                            <td class="info-value">{{ $job->end_date ? \Carbon\Carbon::parse($job->end_date)->format('Y-m-d H:i:s') : '-' }}</td>
                        </tr>

                        @if($job->shift)
                        <tr>
                            <td class="info-label">Shift:</td>
                            <td class="info-value text-capitalize">{{ $job->shift }}</td>
                        </tr>
                        @endif

                        <tr>
                            <td class="info-label">Status:</td>
                            <td class="info-value">
                                @if($job->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($job->status === 'completed')
                                    <span class="badge bg-primary">Completed</span>
                                @elseif($job->status === 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                @else
                                    <span class="badge bg-warning">{{ ucfirst($job->status) }}</span>
                                @endif
                            </td>
                        </tr>

                    </table>

                    {{-- GOOGLE MAP --}}
                    @if($job->location)
                    <div class="mt-4">
                        <h6 class="fw-bold"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Location Map</h6>
                        <div class="map-container border">
                            <iframe
                                width="100%"
                                height="100%"
                                style="border:0;"
                                loading="lazy"
                                allowfullscreen
                                src="https://www.google.com/maps?q={{ urlencode($job->location) }}&output=embed">
                            </iframe>
                        </div>
                    </div>
                    @endif

                </div>
            </div>

            {{-- JOB APPLICATIONS & HIRED STATUS --}}
            @if(isset($job->applications) && $job->applications->count())
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header fw-bold bg-light d-flex justify-content-between align-items-center">
                    <span>
                        <i class="bi bi-people-fill me-1 text-primary"></i> Job Applications ({{ $job->applications->count() }})
                    </span>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Applicant</th>
                                    <th>Expected Cost</th>
                                    <th>Start Datetime</th>
                                    <th>Appointment / Gate</th>
                                    <th>Certificate</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($job->applications as $app)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($app->user && $app->user->avatar_url)
                                                <img src="{{ $app->user->avatar_url }}" class="rounded-circle me-2" style="width:36px;height:36px;object-fit:cover;">
                                            @else
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;font-size:14px;">
                                                    {{ $app->user ? strtoupper(substr($app->user->name, 0, 1)) : 'U' }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold">{{ $app->user->name ?? 'Applicant #'.$app->user_id }}</div>
                                                <small class="text-muted">{{ $app->user->phone ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">₹{{ number_format($app->expected_cost, 2) }}</span>
                                    </td>
                                    <td>
                                        {{ $app->start_datetime ? \Carbon\Carbon::parse($app->start_datetime)->format('d M Y, h:i A') : '-' }}
                                    </td>
                                    <td>
                                        @if($app->appointment)
                                            <div class="small">
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 mb-1">
                                                    <i class="bi bi-calendar2-check me-1"></i> Appt #{{ $app->appointment->id }}
                                                </span>
                                                @if($app->appointment->guard_in_time)
                                                    <div><span class="badge bg-warning-subtle text-dark border"><i class="bi bi-shield-check text-warning me-1"></i> IN: {{ \Carbon\Carbon::parse($app->appointment->guard_in_time)->format('h:i A') }}</span></div>
                                                @endif
                                                @if($app->appointment->guard_out_time)
                                                    <div><span class="badge bg-secondary-subtle text-dark border mt-1"><i class="bi bi-door-open me-1"></i> OUT: {{ \Carbon\Carbon::parse($app->appointment->guard_out_time)->format('h:i A') }}</span></div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted small">No appointment</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($app->certificate_photo)
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#appDocModal{{ $app->id }}">
                                                <i class="bi bi-file-earmark-image me-1"></i> View Doc
                                            </button>
                                        @else
                                            <span class="text-muted small">Not provided</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge @if(in_array($app->status, ['accepted', 'approved', 'hired'])) bg-success @elseif($app->status == 'rejected') bg-danger @elseif($app->status == 'completed') bg-primary @else bg-warning text-dark @endif">
                                            {{ ucfirst($app->status ?? 'pending') }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- ACTIVITY TIMELINE & LIFECYCLE LOG --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header fw-bold bg-light d-flex justify-content-between align-items-center">
                    <span>
                        <i class="bi bi-clock-history me-1 text-primary"></i> Activity Timeline & Lifecycle Log
                    </span>
                    @if(isset($timeline) && $timeline->count())
                        <span class="badge bg-primary rounded-pill">{{ $timeline->count() }} Events</span>
                    @endif
                </div>
                <div class="card-body p-3">
                    @if(isset($timeline) && $timeline->count())
                        <div class="timeline-container">
                            @foreach($timeline as $event)
                                <div class="timeline-item">
                                    <div class="timeline-marker {{ $event['badge_class'] ?? 'bg-primary' }}">
                                        <i class="bi {{ $event['icon'] ?? 'bi-circle-fill' }}"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-1 mb-1">
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 14px;">{{ $event['title'] }}</h6>
                                            <span class="timeline-timestamp">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                {{ $event['timestamp'] ? \Carbon\Carbon::parse($event['timestamp'])->format('d M Y, h:i:s A') : '-' }}
                                            </span>
                                        </div>
                                        <p class="mb-2 text-muted" style="font-size: 13px;">{{ $event['description'] }}</p>
                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                            <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 11px;">
                                                <i class="bi bi-person me-1"></i> {{ $event['user'] }} ({{ $event['user_role'] }})
                                            </span>
                                            <span class="badge {{ $event['badge_class'] ?? 'bg-secondary' }} px-2 py-1" style="font-size: 11px;">
                                                {{ $event['badge'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-hourglass-split fs-2 d-block mb-2"></i>
                            No activity recorded yet for this job.
                        </div>
                    @endif
                </div>
            </div>

            {{-- PAYMENT LOGS & TRANSACTIONS --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header fw-bold bg-light d-flex justify-content-between align-items-center">
                    <span>
                        <i class="bi bi-credit-card-2-front-fill me-1 text-success"></i> Payment Logs & Transactions
                    </span>
                    @if(isset($payments) && $payments->count())
                        <span class="badge bg-success rounded-pill">{{ $payments->count() }} Records</span>
                    @endif
                </div>
                <div class="card-body p-3">
                    @if(isset($payments) && $payments->count())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID / Ref</th>
                                        <th>Payer</th>
                                        <th>Payee</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Date & Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $pay)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">#{{ $pay->id }}</div>
                                            <small class="text-muted">{{ $pay->payment_reference ?? $pay->payment_id ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $pay->payer ? $pay->payer->name : '-' }}</div>
                                            <small class="text-muted">ID: {{ $pay->payer_id }}</small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $pay->payee ? $pay->payee->name : '-' }}</div>
                                            <small class="text-muted">ID: {{ $pay->payee_id }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border text-capitalize">
                                                {{ $pay->payment_type ?? ($pay->notes ? $pay->notes : 'Payment') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">
                                                ₹{{ number_format($pay->amount, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary border">
                                                {{ strtoupper($pay->payment_method ?? 'ONLINE') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($pay->status === 'success' || $pay->status === 'paid' || $pay->status === 'completed')
                                                <span class="badge bg-success">Success</span>
                                            @elseif($pay->status === 'pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @else
                                                <span class="badge bg-danger">{{ ucfirst($pay->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="small text-muted">
                                            {{ $pay->paid_at ? \Carbon\Carbon::parse($pay->paid_at)->format('d M Y, h:i A') : ($pay->created_at ? $pay->created_at->format('d M Y, h:i A') : '-') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-cash-stack fs-2 d-block mb-2"></i>
                            No payment transactions recorded for this job.
                        </div>
                    @endif
                </div>
            </div>

        </div>


        {{-- RIGHT SIDE - USER INFO & DOCUMENTS --}}
        <div class="col-lg-4">

            {{-- USER INFO CARD --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header fw-bold bg-light">
                    <i class="bi bi-person-circle me-1"></i> User Information
                </div>

                <div class="card-body p-3">

                    @if($job->user)

                    {{-- USER HEADER --}}
                    <div class="d-flex align-items-center mb-3">
                        @if($job->user->avatar_url)
                            <img src="{{ $job->user->avatar_url }}" class="user-avatar me-3">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($job->user->name) }}&background=0D8ABC&color=fff"
                                 class="user-avatar me-3">
                        @endif

                        <div>
                            <h6 class="mb-0 fw-bold">{{ $job->user->name }}</h6>
                            <span class="text-muted" style="font-size: 13px;">
                                Member since {{ $job->user->created_at ? $job->user->created_at->format('M Y') : '-' }}
                            </span>
                            @if($job->user->verification_status)
                                <div>
                                    <span class="badge @if($job->user->verification_status == 'verified') bg-success @elseif($job->user->verification_status == 'rejected') bg-danger @else bg-warning @endif px-2 py-1" style="font-size: 11px;">
                                        {{ ucfirst($job->user->verification_status) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <table class="table table-borderless align-middle mb-0">
                        <tr>
                            <td class="info-label" style="width: 100px;">Phone:</td>
                            <td class="info-value">{{ $job->user->phone ?? '-' }}</td>
                        </tr>

                        <tr>
                            <td class="info-label" style="width: 100px;">Email:</td>
                            <td class="info-value">{{ $job->user->email ?? '-' }}</td>
                        </tr>

                        <tr>
                            <td class="info-label" style="width: 100px;">Address:</td>
                            <td class="info-value">{{ $job->user->address ?? '-' }}</td>
                        </tr>
                    </table>

                    @if($job->user->employee)
                        <hr class="my-3">
                        <h6 class="fw-bold mb-2 text-muted" style="font-size: 13px;">EMPLOYEE DETAILS</h6>
                        <div class="small">
                            <p class="mb-1"><strong>Experience:</strong> {{ $job->user->employee->experience_years ?? '-' }} years</p>
                            <p class="mb-1"><strong>Hourly Rate:</strong> ₹{{ number_format($job->user->employee->hourly_rate ?? 0, 2) }}</p>

                            @if($job->user->employee->id_proof_path || $job->user->employee->selfie_path)
                            <div class="mt-2 pt-2 border-top">
                                <strong>Employee Photos/Proof:</strong>
                                <div class="d-flex gap-2 mt-2">
                                    @if($job->user->employee->id_proof_path)
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#userEmpIdModal">
                                            <i class="bi bi-card-image me-1"></i> ID Proof
                                        </button>
                                    </div>
                                    @endif
                                    @if($job->user->employee->selfie_path)
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#userEmpSelfieModal">
                                            <i class="bi bi-camera me-1"></i> Selfie
                                        </button>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    @endif

                    @else
                    <p class="text-muted mb-0">User not available</p>
                    @endif
                </div>
            </div>

            {{-- USER UPLOADED DOCUMENTS VERIFICATION CARD --}}
            @php
                $userDocs = isset($documents) && $documents->count() ? $documents : ($job->user && $job->user->documents ? $job->user->documents : collect());
            @endphp

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header fw-bold bg-light d-flex justify-content-between align-items-center">
                    <span>
                        <i class="bi bi-file-earmark-check-fill me-1 text-primary"></i> Uploaded Documents
                    </span>
                    @if($userDocs->count())
                        <span class="badge bg-primary rounded-pill">{{ $userDocs->count() }}</span>
                    @endif
                </div>

                <div class="card-body p-3">
                    @if($userDocs->count())
                        <div class="list-group list-group-flush">
                            @foreach($userDocs as $doc)
                                @php
                                    $docExt = strtolower(pathinfo($doc->file_path, PATHINFO_EXTENSION));
                                    $isImage = in_array($docExt, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
                                @endphp
                                <div class="list-group-item px-0 py-2 d-flex align-items-center justify-content-between border-bottom">
                                    <div class="d-flex align-items-center">
                                        @if($isImage)
                                            <img src="{{ asset('storage/' . $doc->file_path) }}"
                                                 class="doc-thumb me-2"
                                                 data-bs-toggle="modal"
                                                 data-bs-target="#docModal{{ $doc->id }}"
                                                 alt="{{ $doc->document_type }}"
                                                 onerror="this.style.display='none';">
                                        @else
                                            <div class="doc-thumb bg-light d-flex align-items-center justify-content-center me-2 text-danger"
                                                 data-bs-toggle="modal"
                                                 data-bs-target="#docModal{{ $doc->id }}">
                                                <i class="bi bi-file-earmark-pdf fs-4"></i>
                                            </div>
                                        @endif

                                        <div>
                                            <div class="fw-semibold text-capitalize" style="font-size: 14px;">
                                                {{ str_replace('_', ' ', $doc->document_type) }}
                                            </div>
                                            <div>
                                                @if($doc->status == 'approved' || $doc->status == 'verified')
                                                    <span class="badge bg-success-subtle text-success" style="font-size: 11px;">
                                                        <i class="bi bi-check-circle"></i> Approved
                                                    </span>
                                                @elseif($doc->status == 'rejected')
                                                    <span class="badge bg-danger-subtle text-danger" style="font-size: 11px;">
                                                        <i class="bi bi-x-circle"></i> Rejected
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning" style="font-size: 11px;">
                                                        <i class="bi bi-clock"></i> Pending
                                                    </span>
                                                @endif
                                                @if($doc->updated_at)
                                                    <span class="text-muted" style="font-size: 11px;">• {{ $doc->updated_at->format('d M Y') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#docModal{{ $doc->id }}">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-file-earmark-x fs-2 d-block mb-1 text-secondary"></i>
                            <span style="font-size: 13px;">No documents uploaded by this user.</span>
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>

</div>


{{-- ========================================================================= --}}
{{-- POPUP MODALS FOR USER DOCUMENTS --}}
{{-- ========================================================================= --}}
@if(isset($userDocs) && $userDocs->count())
    @foreach ($userDocs as $doc)
        <div class="modal fade" id="docModal{{ $doc->id }}" tabindex="-1"
            aria-labelledby="docModal{{ $doc->id }}Label" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content shadow-lg border-0">

                    <div class="modal-header bg-light">
                        <h5 class="modal-title text-capitalize fw-bold" id="docModal{{ $doc->id }}Label">
                            <i class="bi bi-file-earmark-text text-primary me-1"></i>
                            {{ str_replace('_', ' ', $doc->document_type) }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4 text-center" style="max-height:78vh; overflow:auto; background-color: #f8f9fa;">
                        @php
                            $ext = strtolower(pathinfo($doc->file_path, PATHINFO_EXTENSION));
                        @endphp

                        @if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']))
                            <div class="text-center">
                                <img src="{{ asset('storage/' . $doc->file_path) }}" class="doc-preview-img shadow rounded" alt="{{ $doc->document_type }}">
                            </div>
                        @else
                            <iframe src="{{ asset('storage/' . $doc->file_path) }}" style="width:100%; height:70vh; border:none; border-radius:8px;" class="shadow-sm"></iframe>
                        @endif

                        @if($doc->remarks)
                            <div class="alert alert-info mt-3 text-start mb-0">
                                <strong>Remarks:</strong> {{ $doc->remarks }}
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer bg-light justify-content-between">
                        <div>
                            <span class="badge @if($doc->status == 'approved' || $doc->status == 'verified') bg-success @elseif($doc->status == 'rejected') bg-danger @else bg-warning @endif px-3 py-2">
                                Status: {{ ucfirst($doc->status ?? 'pending') }}
                            </span>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ asset('storage/' . $doc->file_path) }}" download class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-download me-1"></i> Download
                            </a>
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endforeach
@endif


{{-- ========================================================================= --}}
{{-- POPUP MODALS FOR EMPLOYEE ID PROOF & SELFIE --}}
{{-- ========================================================================= --}}
@if($job->user && $job->user->employee)
    @if($job->user->employee->id_proof_path)
        <div class="modal fade" id="userEmpIdModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold"><i class="bi bi-card-image text-primary me-1"></i> Employee ID Proof</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center p-4" style="background-color: #f8f9fa;">
                        @php
                            $idExt = strtolower(pathinfo($job->user->employee->id_proof_path, PATHINFO_EXTENSION));
                        @endphp
                        @if(in_array($idExt, ['jpg', 'jpeg', 'png', 'webp']))
                            <img src="{{ asset('storage/' . $job->user->employee->id_proof_path) }}" class="doc-preview-img shadow rounded" alt="ID Proof">
                        @else
                            <iframe src="{{ asset('storage/' . $job->user->employee->id_proof_path) }}" style="width:100%; height:65vh; border:none;"></iframe>
                        @endif
                    </div>
                    <div class="modal-footer bg-light">
                        <a href="{{ asset('storage/' . $job->user->employee->id_proof_path) }}" download class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-download me-1"></i> Download
                        </a>
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($job->user->employee->selfie_path)
        <div class="modal fade" id="userEmpSelfieModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold"><i class="bi bi-camera text-primary me-1"></i> Employee Selfie</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center p-4" style="background-color: #f8f9fa;">
                        <img src="{{ asset('storage/' . $job->user->employee->selfie_path) }}" class="doc-preview-img shadow rounded" alt="Employee Selfie">
                    </div>
                    <div class="modal-footer bg-light">
                        <a href="{{ asset('storage/' . $job->user->employee->selfie_path) }}" download class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-download me-1"></i> Download
                        </a>
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif


{{-- ========================================================================= --}}
{{-- POPUP MODALS FOR APPLICANT CERTIFICATES --}}
{{-- ========================================================================= --}}
@if(isset($job->applications))
    @foreach($job->applications as $app)
        @if($app->certificate_photo)
            <div class="modal fade" id="appDocModal{{ $app->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content shadow-lg border-0">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-file-earmark-image text-primary me-1"></i>
                                Certificate Photo - {{ $app->user->name ?? 'Applicant #'.$app->user_id }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center p-4" style="background-color: #f8f9fa;">
                            @php
                                $certExt = strtolower(pathinfo($app->certificate_photo, PATHINFO_EXTENSION));
                            @endphp
                            @if(in_array($certExt, ['jpg', 'jpeg', 'png', 'webp']))
                                <img src="{{ asset('storage/' . $app->certificate_photo) }}" class="doc-preview-img shadow rounded" alt="Certificate">
                            @else
                                <iframe src="{{ asset('storage/' . $app->certificate_photo) }}" style="width:100%; height:65vh; border:none;"></iframe>
                            @endif
                        </div>
                        <div class="modal-footer bg-light">
                            <a href="{{ asset('storage/' . $app->certificate_photo) }}" download class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-download me-1"></i> Download
                            </a>
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endif

@endsection
