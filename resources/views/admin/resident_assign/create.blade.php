@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="py-3 d-flex align-items-center justify-content-between pb-3">
        <h4 class="fs-18 fw-semibold m-0">
            <i class="bi bi-people-fill me-2 text-primary"></i>
            Assign Resident to Society
        </h4>

        <a href="{{ route('residents.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    {{-- CARD START --}}
    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-body p-4">

            {{-- SUCCESS ALERT --}}
            @if(session('success'))
                <div class="alert alert-success mb-4">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('resident.assign.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- EXISTING CUSTOMER --}}
                {{-- <div class="p-3 bg-light rounded-3 mb-4 border position-relative">
                    <span class="badge bg-primary position-absolute"
                          style="top:-10px; left:10px; font-size:13px;">
                        Existing Customer
                    </span>

                    <label class="fw-semibold mb-2">Select Customer</label>
                    <select name="existing_user_id" id="existing_user_id" class="form-select shadow-sm">
                        <option value="">-- Select Customer --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">
                                {{ $customer->name }} ({{ $customer->email }})
                            </option>
                        @endforeach
                    </select>

                    <div class="text-muted mt-1">If customer not found, fill below form.</div>
                </div> --}}

                {{-- NEW CUSTOMER FORM --}}
                <div >

                    <div class="d-flex align-items-center mb-3">
                        <h5 class="fw-bold me-2 mb-0">Create New Customer</h5>
                        <span class="badge bg-secondary">Required</span>
                    </div>

                    <div class="row g-3">

                        {{-- NAME --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Full Name *</label>
                            <input type="text" name="name" class="form-control shadow-sm">
                        </div>

                        {{-- PHONE --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Phone *</label>
                            <input type="text" name="phone" class="form-control shadow-sm">
                        </div>

                        {{-- EMAIL --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Email *</label>
                            <input type="email" name="email" class="form-control shadow-sm">
                        </div>

                        {{-- PASSWORD --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Password (auto if empty)</label>
                            <input type="password" name="password" class="form-control shadow-sm">
                        </div>

                        {{-- AVATAR --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Avatar</label>
                            <input type="file" name="avatar" class="form-control shadow-sm">
                        </div>

                    </div>

                    {{-- AUTO DEFAULT HIDDEN FIELDS --}}
                    <input type="hidden" name="verification_status" value="pending">
                    <input type="hidden" name="language" value="English">
                    <input type="hidden" name="region" value="India">
                    <input type="hidden" name="is_active" value="1">
                    <input type="hidden" name="role" value="customer">

                </div>

                <hr class="my-4">

                {{-- SELECT SOCIETY --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Assign Society *</label>

                    <div class="d-flex gap-2">
                        <select name="society_id" class="form-select shadow-sm flex-grow-1" required>
                            @foreach($societies as $soc)
                                <option value="{{ $soc->id }}">{{ $soc->name }}</option>
                            @endforeach
                        </select>

                        {{-- ADD SOCIETY BUTTON --}}
                        <a href="{{ route('societies.create') }}" 
                           class="btn btn-outline-primary btn-sm">
                           <i data-feather="plus-circle"></i>
                        </a>
                    </div>
                </div>

                <div class="text-end">
                    <button class="btn btn-primary px-4 py-2">
                        <i class="bi bi-check-circle me-1"></i>
                        Assign Resident
                    </button>
                </div>

            </form>

        </div>
    </div>
    {{-- CARD END --}}

</div>

{{-- jQuery --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function () {

        function toggleNewForm() {
            if ($("#existing_user_id").val() !== "") {
                $("#new_user_form").slideUp(200);
            } else {
                $("#new_user_form").slideDown(200);
            }
        }

        $("#existing_user_id").on("change", function () {
            toggleNewForm();
        });

        toggleNewForm();
    });
</script>

@endsection
