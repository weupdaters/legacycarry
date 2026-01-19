@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Post a Job</h2>

    <form action="{{ route('jobs.store') }}" method="POST">
        @csrf

        <!-- Step 1 -->
        <div class="card mb-4">
            <div class="card-header">Step 1 of 3: Basic Details</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="job_title" class="form-label">Job Title</label>
                    <input type="text" class="form-control" id="job_title" name="job_title" required>
                </div>

                <div class="mb-3">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" class="form-control" id="location" name="location" required>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" name="address" id="address"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Job Type</label><br>
                    <select name="job_type" class="form-select" required>
                        <option value="full_day">Full Day</option>
                        <option value="part_time">Part Time</option>
                        <option value="hourly">Hourly</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="workers_required" class="form-label">Workers Required</label>
                    <input type="number" class="form-control" id="workers_required" name="workers_required" min="1" required>
                </div>
            </div>
        </div
