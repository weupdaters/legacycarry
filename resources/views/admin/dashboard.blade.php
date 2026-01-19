@extends('admin.layouts.app')
@section('title', 'Dashboard')

@section('content')

<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0"> Dashboard</h4>
        </div>
    </div>

    {{-- FIRST ROW --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row">

                        {{-- TOTAL EMPLOYEES --}}
                        <div class="col-md-6 col-xl-3 hrm-widget border-end border-bottom">
                            <div class="card shadow-none mb-0">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <span class="bg-gray rounded-circle d-flex justify-content-center align-items-center shadow-sm"
                                              style="width:40px;height:40px;">
                                            <i class="mdi mdi-account-group fs-22 text-dark"></i>
                                        </span>
                                    </div>

                                    <div class="d-flex align-items-center mb-2">
                                        <h3 class="mb-0 fs-26 text-dark me-3">{{ $totalEmployees }}</h3>
                                    </div>
                                    <p class="text-muted mb-0">Total Employees</p>
                                </div>
                            </div>
                        </div>

                        {{-- JOB APPLICANTS --}}
                        <div class="col-md-6 col-xl-3 hrm-widget border-bottom">
                            <div class="card shadow-none mb-0">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <span class="bg-gray rounded-circle d-flex justify-content-center align-items-center shadow-sm"
                                              style="width:40px;height:40px;">
                                            <i class="mdi mdi-briefcase fs-22 text-dark"></i>
                                        </span>
                                    </div>

                                    <h3 class="mb-0 fs-26 text-dark">{{ $jobApplicants }}</h3>
                                    <p class="text-muted mb-0">Job Applicants</p>
                                </div>
                            </div>
                        </div>

                        {{-- NEW EMPLOYEES --}}
                        <div class="col-md-6 col-xl-3 hrm-widget border-end">
                            <div class="card shadow-none mb-0">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <span class="bg-gray rounded-circle d-flex justify-content-center align-items-center shadow-sm"
                                              style="width:40px;height:40px;">
                                            <i class="mdi mdi-account-plus fs-22 text-dark"></i>
                                        </span>
                                    </div>

                                    <h3 class="mb-0 fs-26 text-dark">{{ $newEmployees }}</h3>
                                    <p class="text-muted mb-0">New Employees (30 Days)</p>
                                </div>
                            </div>
                        </div>

                        {{-- RESIGNED EMPLOYEES --}}
                        <div class="col-md-6 col-xl-3 hrm-widget">
                            <div class="card shadow-none mb-0">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <span class="bg-gray rounded-circle d-flex justify-content-center align-items-center shadow-sm"
                                              style="width:40px;height:40px;">
                                            <i class="mdi mdi-account-remove fs-22 text-dark"></i>
                                        </span>
                                    </div>

                                    <h3 class="mb-0 fs-26 text-dark">{{ $resignedEmployees }}</h3>
                                    <p class="text-muted mb-0">Resigned Employees</p>
                                </div>
                            </div>
                        </div>

                    </div> <!-- row end -->
                </div>
            </div>
        </div>
    </div>

    {{-- JOB SUMMARY --}}
    <div class="row mt-3">

        <div class="col-xxl-3 col-xl-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Job Summary</h5>
                </div>
                <div class="card-body">

                    <div class="d-flex justify-content-between p-1">
                        <span>Active Jobs</span>
                        <strong>{{ $activeJobs }}</strong>
                    </div>

                    <div class="d-flex justify-content-between p-1">
                        <span>Inactive</span>
                        <strong>{{ $inactiveJobs }}</strong>
                    </div>

                    <div class="d-flex justify-content-between p-1">
                        <span>Closed</span>
                        <strong>{{ $closedJobs }}</strong>
                    </div>

                    <div class="d-flex justify-content-between p-1">
                        <span>On Hold</span>
                        <strong>{{ $onHoldJobs }}</strong>
                    </div>

                </div>
            </div>
        </div>

        {{-- INCOME CHART (PREPARED FOR REAL DATA) --}}
        <div class="col-xxl-6 col-xl-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Income Statistics</h5>
                </div>
                <div class="card-body">
                    <div id="income-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>

        {{-- INTERVIEW SUMMARY (STATIC SAMPLE, CAN MAKE DYNAMIC) --}}
        <div class="col-xxl-3 col-xl-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Interview Summary</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">No interviews scheduled yet.</p>
                </div>
            </div>
        </div>

    </div>

    {{-- EMPLOYEE LIST --}}
    <div class="row mt-4">
        <div class="col-12">

            <div class="card shadow-sm overflow-hidden">

                <div class="card-header">
                    <h5 class="card-title mb-0">Employee List</h5>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">

                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Experience</th>
                                    <th>Services</th>
                                    <th>Availability</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                            @foreach($employees as $emp)
                                <tr>
                                    <td>EMP{{ str_pad($emp->id, 6, '0', STR_PAD_LEFT) }}</td>

                                    <td class="d-flex align-items-center">
                                        <img src="{{ asset($emp->avatar ?? 'assets/images/users/default.png') }}"
                                             class="avatar avatar-sm rounded-circle me-2">
                                        {{ $emp->name }}
                                    </td>

                                    <td>{{ $emp->email }}</td>

                                    <td>{{ $emp->employee->experience_years ?? '-' }} yrs</td>

                                    <td>
                                        @php
                                            $services = $emp->employee->services ?? [];
                                            $services = is_array($services) ? $services : json_decode($services, true);
                                        @endphp
                                        {{ implode(', ', $services ?? []) }}
                                    </td>

                                    <td>
                                        @if($emp->employee)
                                            <span class="badge bg-info">
                                                {{ $emp->employee->availability }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>

                                    <td>
                                        <a href="{{ route('employees.view', $emp->id) }}"
                                           class="btn btn-sm btn-primary">
                                           View
                                        </a>
                                    </td>

                                </tr>
                            @endforeach
                            </tbody>

                        </table>
                    </div>
                </div>

                <div class="px-3 py-2">
                    {{ $employees->links('pagination::bootstrap-5') }}
                </div>

            </div>

        </div>
    </div>

</div>
@endsection
