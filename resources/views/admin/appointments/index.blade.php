@extends('admin.layouts.app')

@section('content')

<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Job Posts Management</h4>
        </div>
        <div class="text-end">
            <a href="{{ route('jobs.export', ['type' => 'excel']) }}" class="btn btn-success btn-sm">Export Excel</a>   
        </div>
    </div>

    <div class="row">
        <div class="col-12">

            <div class="card p-3 mb-3">
                <div class="row g-3 mb-4">
                    
                    <div class="col-md-3">
                        <input type="text" id="search" class="form-control" placeholder="Search...">
                    </div>

                    <div class="col-md-3">
                        <select id="status_filter" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    {{-- <div class="col-md-1">
                        <input type="number" id="apps_min" class="form-control" placeholder="Min">
                    </div>
                    <div class="col-md-1">
                        <input type="number" id="apps_max" class="form-control" placeholder="Max">
                    </div> --}}
                </div>
            </div>
            {{-- JOB TABLE --}}
          

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Resident</th>
                <th>Employer</th>
                <th>Status</th>
                <th>Total</th>
                <th>Advance Paid?</th>
                <th>Final Paid?</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @foreach($appointments as $a)
            <tr>
                <td>{{ $a->id }}</td>
                <td>{{ $a->resident_id }}</td>
                <td>{{ $a->employer_id }}</td>
                <td>{{ $a->status }}</td>
                <td>{{ $a->total_amount }}</td>
                <td>{{ $a->advance_paid ? 'YES' : 'NO' }}</td>
                <td>{{ $a->final_paid ? 'YES' : 'NO' }}</td>
                <td>
                    <a href="{{ route('admin.appointments.show',$a->id) }}" class="btn btn-primary btn-sm">View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $appointments->links() }}

</div>

@endsection
