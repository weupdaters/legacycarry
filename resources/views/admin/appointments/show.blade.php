@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h3>Appointment #{{ $appointment->id }}</h3>

    <ul class="list-group">
        <li class="list-group-item"><strong>Status:</strong> {{ $appointment->status }}</li>
        <li class="list-group-item"><strong>Total Amount:</strong> ₹{{ $appointment->total_amount }}</li>
        <li class="list-group-item"><strong>Advance Paid:</strong> {{ $appointment->advance_paid ? 'YES' : 'NO' }}</li>
        <li class="list-group-item"><strong>Final Paid:</strong> {{ $appointment->final_paid ? 'YES' : 'NO' }}</li>
        <li class="list-group-item"><strong>Start Time:</strong> {{ $appointment->job_start_time }}</li>
        <li class="list-group-item"><strong>End Time:</strong> {{ $appointment->job_end_time }}</li>
        <li class="list-group-item"><strong>Total Minutes:</strong> {{ $appointment->totalMinutes() }}</li>
    </ul>
</div>
@endsection
