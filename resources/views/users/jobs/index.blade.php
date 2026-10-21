@extends('users.layouts.app')

@section('content')

<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Job Posts Management</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-12">

            <div class="card">
                <div class="card-body">

                    {{-- SUCCESS MESSAGE --}}
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    {{-- JOB TABLE --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">

                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Job Title</th>
                                    <th>Location</th>
                                    <th>Job Type</th>
                                    <th>Pay</th>
                                    <th>Status</th>
                                    <th>Job Applications</th>
                                    <th width="180">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($jobs as $job)
                                <tr>
                                    <td>{{ $job->id }}</td>

                                    {{-- TITLE --}}
                                    <td>{{ $job->job_title }}</td>

                                    {{-- LOCATION --}}
                                    <td>{{ $job->location ?? 'Unknown' }}</td>

                                    {{-- JOB TYPE --}}
                                    <td>{{ ucfirst($job->job_type) }}</td>

                                    {{-- PAY INFO --}}
                                    <td>
                                        {{ ucfirst($job->pay_type) }} :
                                        <strong>{{ number_format($job->pay_amount, 2) }}</strong>
                                    </td>

                                    {{-- STATUS --}}
                                    <td>
                                        @if($job->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-warning">{{ ucfirst($job->status) }}</span>
                                        @endif
                                    </td>

                                    {{-- JOB APPLICATIONS COUNT --}}
                                    <td>
                                        @php
                                            $count = $job->applications->count();
                                        @endphp
                                    
                                        <a href="{{ route('jobs.applications', $job->id) }}" class="text-decoration-none">
                                    
                                            {{-- IF APPLICATIONS EXIST --}}
                                            @if($count > 0)
                                                <span class="badge rounded-pill px-3 py-2" 
                                                    style="background:linear-gradient(135deg,#4fb0ff,#007bff); font-size:13px;">
                                                    <i class="bi bi-people-fill me-1"></i>
                                                    {{ $count }} Applications
                                                </span>
                                    
                                            {{-- IF ZERO APPLICATIONS --}}
                                            @else
                                                <span class="badge rounded-pill px-3 py-2"
                                                    style="background:#dcdcdc; color:#555; font-size:13px;">
                                                    <i class="bi bi-person-x me-1"></i>
                                                    No Applications
                                                </span>
                                            @endif
                                    
                                        </a>
                                    </td>
                                    
                                    

                                    {{-- ACTION BUTTONS --}}
                                    <td>

                                        <a href="{{ route('jobs.view', $job->id) }}" 
                                           class="btn btn-info btn-sm">
                                            View
                                        </a>

                                        {{-- <a href="{{ route('jobs.edit', $job->id) }}" 
                                           class="btn btn-primary btn-sm">
                                            Edit
                                        </a> --}}

                                        {{-- <form action="{{ route('jobs.destroy', $job->id) }}" 
                                              method="POST" 
                                              style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm"
                                                onclick="return confirm('Delete this job?');">
                                                Delete
                                            </button>
                                        </form> --}}
                                    </td>
                                </tr>

                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        No job posts found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>

                    {{-- PAGINATION --}}
                    <div class="mt-3">
                        {!! $jobs->onEachSide(2)->links('vendor.pagination.bootstrap-5') !!}
                     
                    </div>

                </div>
            </div>

        </div>
    </div>

</div>

@endsection
