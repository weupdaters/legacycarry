@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="py-3 d-flex align-items-center justify-content-between pb-3">
        <h4 class="fs-18 fw-semibold m-0">
            <i class="bi bi-people-fill me-2 text-primary"></i>
            <i data-feather="edit"></i> Edit Assignment
        </h4>

        <a href="{{ route('resident.assign.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-body p-4">

            {{-- SUCCESS --}}
            @if(session('success'))
                <div class="alert alert-success mb-3">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('resident.assign.update', $assignment->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">

                    {{-- NAME --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Customer Name *</label>
                        <input type="text" name="name" class="form-control shadow-sm" 
                               value="{{ old('name', $assignment->user->name) }}" required>
                    </div>

                    {{-- EMAIL --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Email *</label>
                        <input type="email" name="email" class="form-control shadow-sm"
                               value="{{ old('email', $assignment->user->email) }}" required>
                    </div>

                    {{-- PHONE --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Phone *</label>
                        <input type="text" name="phone" class="form-control shadow-sm"
                               value="{{ old('phone', $assignment->user->phone) }}" required>
                    </div>

                    {{-- PASSWORD --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Password (leave blank to keep same)</label>
                        <input type="password" name="password" class="form-control shadow-sm" placeholder="New password">
                    </div>

                </div>

                <hr class="my-4">

                {{-- SELECT SOCIETY --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Society *</label>

                    <div class="d-flex gap-2">
                        <select name="society_id" class="form-select shadow-sm flex-grow-1" required>
                            @foreach($societies as $soc)
                                <option value="{{ $soc->id }}" 
                                    {{ $soc->id == $assignment->society_id ? 'selected' : '' }}>
                                    {{ $soc->name }}
                                </option>
                            @endforeach
                        </select>

                        <a href="{{ route('societies.create') }}" class="btn btn-outline-primary btn-sm">
                            <i data-feather="plus-circle"></i>
                        </a>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button class="btn btn-primary px-4 py-2">
                        <i class="bi bi-check2-circle me-1"></i> Update Assignment
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

<script>
    feather.replace();
</script>
@endsection
