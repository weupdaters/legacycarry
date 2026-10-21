@extends('layouts.app')

@section('content')

<style>
    .employee-badge {
        background: #eef5ff;
        padding: 5px 10px;
        border-radius: 12px;
        color: #0b74d8;
        font-weight: 600;
        font-size: 12px;
    }
</style>

<!-- Start Content-->
<div class="container-fluid">

    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0"> 
            <i class="bi bi-building-fill me-1"></i> Service Providers Management List
        </h4>
    </div>
    </div>


    <div class="card shadow-sm border-0">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-striped">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Experience</th>
                        <th>Services</th>
                        <th>Availability</th>
                        <th>Hourly Rate</th>
            
                        <!-- NEW Job Status Columns -->
                        <th class="text-center">Jobs Pending</th>
                        <th class="text-center">Jobs Active</th>
                        <th class="text-center">Jobs Completed</th>
            
                        <th width="180">Action</th>
                    </tr>
                </thead>
            
                <tbody>
                    @foreach($employers as $emp)
                    <tr>
                        <td>{{ $emp->id }}</td>
            
                        <!-- NAME -->
                        <td>
                            <strong>{{ $emp->name }}</strong><br>
                            <small class="text-muted">User ID: {{ $emp->id }}</small>
                        </td>
            
                        <!-- CONTACT -->
                        <td>
                            <i class="bi bi-telephone"></i> {{ $emp->phone ?? '-' }} <br>
                            <i class="bi bi-envelope"></i> {{ $emp->email }}
                        </td>
            
                        <!-- EMPLOYEE PROFILE LABEL -->
                        
                        <td>
                            @if($emp->verification_status == 'verified')
                                <span class="badge bg-success">Verified</span>
                            @elseif($emp->verification_status == 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($emp->verification_status == 'rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @else
                                <span class="badge bg-secondary">Not Verified</span>
                            @endif
                        </td>
                        

                        <!-- EXPERIENCE -->
                        <td>{{ $emp->employee?->experience_years ? $emp->employee->experience_years.' Years' : '-' }}</td>
            
                        <!-- SERVICES -->
                        <td>
                            @if($emp->employee)
                                @php
                                    $services = $emp->employee->services;
                                    $services = is_string($services) ? json_decode($services, true) : $services;
                                @endphp
            
                                {{ !empty($services) ? implode(', ', $services) : '-' }}
                            @else
                                -
                            @endif
                        </td>
            
                        <!-- AVAILABILITY -->
                        <td>{{ $emp->employee->availability ?? '-' }}</td>
            
                        <!-- HOURLY RATE -->
                        <td>
                            @if($emp->employee)
                                ₹{{ number_format($emp->employee->hourly_rate, 2) }}
                            @else
                                -
                            @endif
                        </td>
            
                        <!-- NEW JOB SUMMARY COUNTS -->
                        <td class="text-center">
                            <span class="badge bg-warning text-dark">{{ $emp->pending_jobs_count }}</span>
                        </td>
            
                        <td class="text-center">
                            <span class="badge bg-primary">{{ $emp->active_jobs_count }}</span>
                        </td>
            
                        <td class="text-center">
                            <span class="badge bg-success">{{ $emp->completed_jobs_count }}</span>
                        </td>
            
                        <!-- ACTION BUTTONS -->
                        <td>
                            <a href="{{ route('employees.view', $emp->id) }}" class="btn btn-info btn-sm">
                                View
                            </a>
            
                            <form action="{{ route('employees.destroy', $emp->id) }}"
                                  method="POST"
                                  style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Delete this employer?')">
                                    Delete
                                </button>
                            </form>
                        </td>
            
                    </tr>
                    @endforeach
                </tbody>
            </table>
            

            <div class="mt-3">
                {{ $employers->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>

</div>

@endsection
