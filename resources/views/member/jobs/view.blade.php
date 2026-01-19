@extends('layouts.app')

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
                <div class="card-header fw-bold bg-light">
                    <i class="bi bi-info-circle me-1"></i> Job Information
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
                            <td class="info-value">{{ ucfirst($job->job_type) }}</td>
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
                                <span class="badge bg-primary">{{ $skills ? implode(', ', $skills) : '-' }}</span>
                            </td>
                        </tr>

                        <tr>
                            <td class="info-label">Tools Provided:</td>
                            <td class="info-value">{{ $job->tools_provided ?? '-' }}</td>
                        </tr>

                        <tr>
                            <td class="info-label">Documents Required:</td>
                            <td class="info-value">{{ $job->documents_required ?? '-' }}</td>
                        </tr>

                        <tr>
                            <td class="info-label">Safety Instructions:</td>
                            <td class="info-value">{{ $job->safety_instructions ?? '-' }}</td>
                        </tr>

                        <tr>
                            <td class="info-label">Pay:</td>
                            <td class="info-value">
                                <span class="badge bg-success">
                                    {{ ucfirst($job->pay_type) }} - ₹{{ number_format($job->pay_amount, 2) }}
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td class="info-label">Advance Payment:</td>
                            <td class="info-value">
                                @if($job->advance_payment)
                                    <span class="text-success fw-bold">Yes</span>
                                    (₹{{ number_format($job->advance_amount, 2) }})
                                @else
                                    <span class="text-danger">No</span>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td class="info-label">Start Date:</td>
                            <td class="info-value">{{ $job->start_date }}</td>
                        </tr>

                        <tr>
                            <td class="info-label">End Date:</td>
                            <td class="info-value">{{ $job->end_date }}</td>
                        </tr>

                        <tr>
                            <td class="info-label">Status:</td>
                            <td class="info-value">
                                @if($job->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-warning">{{ ucfirst($job->status) }}</span>
                                @endif
                            </td>
                        </tr>

                    </table>

                    {{-- GOOGLE MAP --}}
                    @if($job->location)
                    <div class="mt-4">
                        <h6 class="fw-bold">Location Map</h6>
                        <div class="map-container">
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
        </div>


        {{-- RIGHT SIDE - USER INFO --}}
        <div class="col-lg-4">

            <div class="card shadow-sm border-0">
                <div class="card-header fw-bold bg-light">
                    <i class="bi bi-person-circle me-1"></i> User Information
                </div>

                <div class="card-body p-3">

                    @if($job->user)

                    {{-- USER HEADER --}}
                    <div class="d-flex align-items-center mb-3">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($job->user->name) }}&background=0D8ABC&color=fff"
                             class="user-avatar me-3">

                        <div>
                            <h6 class="mb-0 fw-bold">{{ $job->user->name }}</h6>
                            <span class="text-muted" style="font-size: 13px;">
                                Member since {{ $job->user->created_at->format('M Y') }}
                            </span>
                        </div>
                    </div>

                    <table class="table table-borderless">
                        <tr>
                            <td class="info-label">Phone:</td>
                            <td class="info-value">{{ $job->user->phone ?? '-' }}</td>
                        </tr>

                        <tr>
                            <td class="info-label">Email:</td>
                            <td class="info-value">{{ $job->user->email }}</td>
                        </tr>

                        <tr>
                            <td class="info-label">Address:</td>
                            <td class="info-value">{{ $job->user->address ?? '-' }}</td>
                        </tr>
                    </table>

                    @else
                    <p class="text-muted">User not available</p>
                    @endif
                </div>
            </div>

        </div>

    </div>


    {{-- ACTION BUTTONS --}}
        {{-- <div class="mt-4">
            <a href="{{ route('jobs.edit', $job->id) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit Job
            </a>

            <form action="{{ route('jobs.destroy', $job->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button onclick="return confirm('Are you sure you want to delete this job?');"
                        class="btn btn-danger">
                    <i class="bi bi-trash"></i> Delete Job
                </button>
            </form>
        </div> --}}

</div>

@endsection
