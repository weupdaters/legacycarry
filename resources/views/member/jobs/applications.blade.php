@extends('layouts.app')

@section('content')

<style>
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 12px;
    }
    .status-applied { background: #e1efff; color: #0d6efd; }
    .status-hired { background: #e7fbe9; color: #2e9e3f; }
    .status-completed { background: #fff3cd; color: #b8860b; }
    .status-cancel { background: #ffe3e3; color: #d9534f; }
</style>

  <!-- Start Content-->
  <div class="container-fluid">

    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0"> <i class="bi bi-people-fill me-1"></i>
                Applications for: {{ $job->job_title }} </h4>
            
            
        </div>


        {{-- <div class="text-end">
            <a href="{{ route('users.create') }}" class="btn btn-success btn-sm">Add New User</a>
            
        </div> --}}
    </div>

    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

            @if($applications->count() == 0)
                <p class="text-muted text-center py-4">
                    <i class="bi bi-inbox text-secondary" style="font-size:32px;"></i><br>
                    No Applications yet.
                </p>
            @else

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Applicant</th>
                            <th>Contact</th>
                            <th>Expected Cost</th>
                            <th>Status</th>
                            <th>Shortlisted</th>
                            <th>Message</th>
                            <th>Certificate</th>
                            <th>Applied On</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($applications as $app)
                        <tr>
                            <td>{{ $app->id }}</td>

                            {{-- Applicant Name --}}
                            <td>
                                <strong>{{ $app->user->name ?? 'Unknown User' }}</strong><br>
                                <small class="text-muted">User ID: {{ $app->user_id }}</small>
                            </td>

                            {{-- Phone & Email --}}
                            <td>
                                <i class="bi bi-telephone-outbound me-1"></i>
                                {{ $app->user->phone ?? '-' }} <br>

                                <i class="bi bi-envelope me-1"></i>
                                {{ $app->user->email ?? '-' }}
                            </td>

                            {{-- Expected Cost --}}
                            <td>
                                ₹{{ number_format($app->expected_cost ?? 0, 2) }}
                            </td>

                            {{-- STATUS BADGE --}}
                            <td>
                                @php
                                    $statusClass = [
                                        'applied'   => 'status-applied',
                                        'hired'     => 'status-hired',
                                        'completed' => 'status-completed',
                                        'cancel'    => 'status-cancel',
                                    ][$app->status] ?? 'status-applied';
                                @endphp

                                <span class="status-badge {{ $statusClass }}">
                                    {{ ucfirst($app->status) }}
                                </span>
                            </td>

                            {{-- Shortlisted --}}
                            <td>
                                @if($app->shortlisted)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>

                            {{-- Message --}}
                            <td style="max-width:250px;">
                                {{ $app->message_to_employer ?? '-' }}
                            </td>

                            {{-- Certificate --}}
                            <td>
                                @if($app->certificate_photo)
                                    <a href="{{ asset('storage/' . $app->certificate_photo) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </td>

                            {{-- Date --}}
                            <td>
                                {{ $app->created_at->format('d M Y, h:i A') }}
                            </td>

                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

            @endif

        </div>
    </div>

</div>

@endsection
