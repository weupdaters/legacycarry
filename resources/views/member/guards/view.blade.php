@extends('member.layouts.app')

@section('content')
  <style>
    .glass-card {
      backdrop-filter: blur(12px);
      background: rgba(255, 255, 255, 0.65);
      border-radius: 18px;
      border: 1px solid rgba(255, 255, 255, 0.35);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    }

    .gradient-header {
      background: linear-gradient(135deg, #4e73df, #1cc88a);
      border-radius: 18px 18px 0 0;
      color: white;
    }

    .section-title {
      font-size: 14px;
      font-weight: 700;
      color: #6c757d;
      margin-bottom: 6px;
      text-transform: uppercase;
    }

    .info-badge {
      padding: 4px 10px;
      font-size: 12px;
      border-radius: 8px;
    }

    .icon-box {
      width: 45px;
      height: 45px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 22px;
      background: linear-gradient(135deg, #0dcaf0, #0d6efd);
      box-shadow: 0 6px 15px rgba(0, 123, 255, 0.25);
    }
  </style>

  <div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="py-3 d-flex justify-content-between flex-column flex-sm-row align-items-sm-center">
      <h4 class="fw-semibold m-0">Resident Details</h4>
      <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="{{ route('member.guards.index') }}">Guards</a></li>
        <li class="breadcrumb-item active">View</li>
      </ol>
    </div>

    {{-- PROFILE CARD --}}
    <div class="glass-card p-4 mb-4 d-flex align-items-center gap-4">

      @php
        $avatar =
            $user->avatar && file_exists(public_path('storage/' . $user->avatar))
                ? asset('storage/' . $user->avatar)
                : null;
      @endphp

      <!-- Avatar -->
      <div style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#avatarModal">
        @if ($avatar)
          <img src="{{ $avatar }}" class="rounded-circle" style="width:60px;height:60px;object-fit:cover;">
        @else
          <div class="rounded-circle bg-light border d-flex justify-content-center align-items-center"
            style="width:60px;height:60px;font-size:22px;font-weight:bold;">
            {{ strtoupper($user->name[0]) }}
          </div>
        @endif
      </div>

      <!-- Avatar Modal -->
      <div class="modal fade" id="avatarModal">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content text-center p-3">
            @if ($avatar)
              <img src="{{ $avatar }}" class="img-fluid rounded">
            @else
              <div class="rounded-circle bg-light border mx-auto d-flex justify-content-center align-items-center"
                style="width:120px;height:120px;font-size:48px;font-weight:bold;">
                {{ strtoupper($user->name[0]) }}
              </div>
            @endif
          </div>
        </div>
      </div>

      <!-- Profile Info -->
      <div class="flex-grow-1">
        <h2 class="fw-bold mb-1">{{ $user->name }}</h2>
        <div class="text-muted">
          <i class="mdi mdi-email-outline me-1"></i>{{ $user->email }} •
          <i class="mdi mdi-phone me-1"></i>{{ $user->phone }}
        </div>
      </div>

      <!-- Verification Status -->
      <div>
        <strong>Status:</strong>
        <span
          class="badge px-3 py-2 
                @if ($user->verification_status == 'verified') bg-success
                @elseif($user->verification_status == 'pending') bg-warning
                @elseif($user->verification_status == 'rejected') bg-danger
                @else bg-secondary @endif">
          {{ ucfirst($user->verification_status) }}
        </span>

        <form action="{{ route('member.guards.update-verification', $user->id) }}" method="POST"
          class="d-flex align-items-center mt-2">
          @csrf @method('PATCH')

          <select class="form-select form-select-sm w-auto me-2" name="verification_status">
            <option value="unverified" {{ $user->verification_status == 'unverified' ? 'selected' : '' }}>Unverified
            </option>
            <option value="pending" {{ $user->verification_status == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="verified" {{ $user->verification_status == 'verified' ? 'selected' : '' }}>Verified</option>
            <option value="rejected" {{ $user->verification_status == 'rejected' ? 'selected' : '' }}>Rejected</option>
          </select>

          <button class="btn btn-primary btn-sm">Update</button>
        </form>
      </div>

    </div>

    {{-- BASIC PROFILE DETAILS --}}
    <div class="glass-card mb-4">
      <div class="gradient-header p-3">
        <h5 class="fw-bold m-0"><i class="mdi mdi-account-details-outline"></i> Profile Details</h5>
      </div>

      <div class="p-4 row">

        {{-- BASIC INFO --}}
        <div class="col-md-4 mb-3">
          <div class="section-title">Basic Info</div>
          <p><strong>Full Name:</strong> {{ $user->details->full_name ?? '-' }}</p>
          <p><strong>Gender:</strong> {{ $user->details->gender ?? '-' }}</p>
          <p><strong>DOB:</strong> {{ $user->details->date_of_birth ?? '-' }}</p>
        </div>

        {{-- CONTACT --}}
        <div class="col-md-4 mb-3">
          <div class="section-title">Contact</div>
          <p><strong>Email:</strong> {{ $user->details->email ?? '-' }}</p>
          <p><strong>Phone:</strong> {{ $user->details->phone ?? '-' }}</p>
          <p><strong>Address:</strong> {{ $user->details->address ?? '-' }}</p>

          <div class="section-title mt-4">Work</div>
          <p><strong>Occupation:</strong> {{ $user->details->occupation ?? '-' }}</p>
          <p><strong>Marital Status:</strong> {{ $user->details->marital_status ?? '-' }}</p>
        </div>

        {{-- RESIDENCE --}}
        <div class="col-md-4 mb-3">
          <div class="section-title">Residence</div>
          <p><strong>Society:</strong>
            <span class="badge bg-primary info-badge">
              {{ $user->details->society->name ?? 'N/A' }}
            </span>
          </p>

          {{-- Flat --}}
          {{-- <div class="p-3 bg-light border rounded mb-3 shadow-sm">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-box me-3"><i class="mdi mdi-home-city-outline"></i></div>
                        <h6 class="fw-bold mb-0">Flat</h6>
                    </div>

                    <p><strong>Building:</strong> {{ $user->details->flat->building_no ?? 'N/A' }}</p>
                    <p><strong>Owner:</strong> {{ $user->details->flat->owner_name ?? 'N/A' }}</p>
                    <p><strong>Status:</strong>
                        <span class="badge bg-info">{{ $user->details->flat->status ?? 'N/A' }}</span>
                    </p> --}}
        </div>

        {{-- Emergency --}}
        <div class="section-title">Emergency Contact</div>
        <p><strong>Name:</strong> {{ $user->details->emergency_contact_name ?? '-' }}</p>
        <p><strong>Phone:</strong> {{ $user->details->emergency_contact_phone ?? '-' }}</p>

        <p class="mt-3"><strong>Joined:</strong> {{ $user->created_at->format('d M Y') }}</p>
      </div>

    </div>
  </div>

  </div>
@endsection
