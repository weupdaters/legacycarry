@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">

    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">
                Edit Resident: {{ $user->name }}
            </h4>
            
            
        </div>

        <div class="text-end">
            <a href="{{ route('residents.index') }}" class="btn btn-secondary btn-sm">Back to Residents</a>
        </div>
        {{-- <div class="text-end">
            <a href="{{ route('users.create') }}" class="btn btn-success btn-sm">Add New User</a>
            
        </div> --}}
    </div>

    
    <div class="row">
        <div class="col-12">
            <div class="card">
               

                <div class="card-body">
                   
 

    <form action="{{ route('residents.update', $user->id) }}" method="POST" enctype="multipart/form-data" class="row g-3">
        @csrf
        @method('PATCH')

        {{-- Basic user --}}
        <div class="col-12 col-md-6">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label">Email</label>
            <input name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}">
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label">Phone</label>
            <input name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label">Status</label>
            <select name="is_active" class="form-select">
                <option value="1" {{ old('is_active', $user->is_active) ? 'selected' : '' }}>Active</option>
                <option value="0" {{ !old('is_active', $user->is_active) ? 'selected' : '' }}>Banned</option>
            </select>
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label">Avatar (jpg, png)</label>
            <input name="avatar" type="file" accept="image/*" class="form-control">
            @if($user->avatar)
                <small class="text-muted">Current: <a href="{{ asset('storage/'.$user->avatar) }}" data-preview-image="{{ asset('storage/'.$user->avatar) }}" data-preview-title="{{ $user->name }} Avatar">view</a></small>
            @endif
        </div>

        {{-- Details (nested) --}}
        <div class="col-12">
            <h6 class="mt-3">Profile Details</h6>
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label">Full name</label>
            <input name="details[full_name]" class="form-control"
                   value="{{ old('details.full_name', $user->details->full_name ?? '') }}">
        </div>

        <div class="col-6 col-md-2">
            <label class="form-label">Gender</label>
            <select name="details[gender]" class="form-select">
                <option value="">--</option>
                <option value="male" {{ (old('details.gender', $user->details->gender ?? '')=='Male') ? 'selected' : '' }}>Male</option>
                <option value="female" {{ (old('details.gender', $user->details->gender ?? '')=='Female') ? 'selected' : '' }}>Female</option>
                <option value="other" {{ (old('details.gender', $user->details->gender ?? '')=='other') ? 'selected' : '' }}>Other</option>
            </select>
        </div>

        <div class="col-6 col-md-2">
            <label class="form-label">DOB</label>
            <input name="details[date_of_birth]" type="date" class="form-control"
                   value="{{ old('details.date_of_birth', $user->details->date_of_birth ?? '') }}">
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label">Occupation</label>
            <input name="details[occupation]" class="form-control"
                   value="{{ old('details.occupation', $user->details->occupation ?? '') }}">
        </div>

        <div class="col-12">
            <label class="form-label">Address</label>
            <textarea name="details[address]" class="form-control" rows="2">{{ old('details.address', $user->details->address ?? '') }}</textarea>
        </div>

        <div class="col-6 col-md-3">
            <label class="form-label">Marital status</label>
            <input name="details[marital_status]" class="form-control"
                   value="{{ old('details.marital_status', $user->details->marital_status ?? '') }}">
        </div>

        {{-- Society & Flat --}}
        <div class="col-6 col-md-3">
            <label class="form-label">Society</label>
            {{-- If you pass $societies to view --}}
            <select name="details[society_id]" class="form-select">
                <option value="">-- Select --</option>
                @if(isset($societies))
                    @foreach($societies as $s)
                        <option value="{{ $s->id }}" {{ (old('details.society_id', $user->details->society->id ?? '') == $s->id) ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                @else
                    <option value="{{ old('details.society_id', $user->details->society->id ?? '') }}" selected>
                        {{ $user->details->society->name ?? 'N/A' }}
                    </option>
                @endif
            </select>
        </div>

        <div class="col-6 col-md-3">
            <label class="form-label">Building No</label>
            <input name="details[flat][building_no]" class="form-control"
                   value="{{ old('details.flat.building_no', $user->details->flat->building_no ?? '') }}">
        </div>

        <div class="col-6 col-md-3">
            <label class="form-label">Flat Owner</label>
            <input name="details[flat][owner_name]" class="form-control"
                   value="{{ old('details.flat.owner_name', $user->details->flat->owner_name ?? '') }}">
        </div>

        <div class="col-6 col-md-3">
            <label class="form-label">Flat status</label>
            <input name="details[flat][status]" class="form-control"
                   value="{{ old('details.flat.status', $user->details->flat->status ?? '') }}">
        </div>

        {{-- Emergency --}}
        <div class="col-6 col-md-6">
            <label class="form-label">Emergency contact name</label>
            <input name="details[emergency_contact_name]" class="form-control"
                   value="{{ old('details.emergency_contact_name', $user->details->emergency_contact_name ?? '') }}">
        </div>

        <div class="col-6 col-md-6">
            <label class="form-label">Emergency contact phone</label>
            <input name="details[emergency_contact_phone]" class="form-control"
                   value="{{ old('details.emergency_contact_phone', $user->details->emergency_contact_phone ?? '') }}">
        </div>

        {{-- Joined (readonly) --}}
        <div class="col-12 col-md-4">
            <label class="form-label">Joined</label>
            <input class="form-control" value="{{ $user->created_at->format('d M Y') }}" readonly>
        </div>

        {{-- Submit --}}
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary">Save changes</button>
            <a href="{{ route('residents.index', $user->id) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
