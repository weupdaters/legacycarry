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

    .profile-avatar {
      width: 85px;
      height: 85px;
      border-radius: 50%;
      background: linear-gradient(135deg, #0d6efd, #6610f2);
      color: white;
      font-size: 36px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
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
      text-transform: uppercase;
      margin-bottom: 6px;
    }

    .info-badge {
      padding: 5px 10px;
      font-size: 12px;
      border-radius: 10px;
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

    {{-- PAGE TITLE --}}
    <div class="py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center">
      <h4 class="fw-semibold m-0">Residents Management</h4>

      <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="{{ route('member.residents.index') }}">Residents</a></li>
        <li class="breadcrumb-item active">View</li>
      </ol>
    </div>

    </div>

    @php $avatar = $user->avatar_url; @endphp

    <!-- Modal -->
    <div class="modal fade" id="avatarModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-3">
          @if ($avatar)
            <img src="{{ $avatar }}" class="img-fluid rounded">
          @else
            <div class="rounded-circle bg-light border mx-auto d-flex justify-content-center align-items-center"
              style="width:120px;height:120px;font-size:48px;font-weight:600;">
              {{ strtoupper($user->name[0]) }}
            </div>
          @endif
        </div>
      </div>
    </div>

    {{-- PROFILE HEADER --}}
    <div class="glass-card p-4 mb-4 d-flex align-items-center gap-4">


      <!-- Avatar -->
      <div data-bs-toggle="modal" data-bs-target="#avatarModal" style="cursor:pointer;">
        @if ($avatar)
          <img src="{{ $avatar }}" class="rounded-circle" style="width:60px;height:60px;object-fit:cover;">
        @else
          <div class="rounded-circle bg-light border d-flex justify-content-center align-items-center"
            style="width:60px;height:60px;font-size:24px;font-weight:600;">
            {{ strtoupper($user->name[0]) }}
          </div>
        @endif
      </div>




      <div class="flex-grow-1">
        <h2 class="fw-bold mb-1">{{ $user->name }}</h2>
        <div class="text-muted">
          <i class="mdi mdi-email-outline me-1"></i>{{ $user->email }} •
          <i class="mdi mdi-phone me-1"></i>{{ $user->phone }}
        </div>
      </div>



      <div>
        {{-- <strong>Status:</strong> --}}
        {{-- @if ($user->is_active)
                <span class="badge bg-success info-badge">Active</span>
            @else
                <span class="badge bg-danger info-badge">Banned</span>
            @endif --}}

        {{-- Current Status Badge --}}
        <div class="mb-2 d-flex align-items-center">
          <strong class="me-2">Current Status:</strong>

          <span
            class="badge 
                    @if ($user->verification_status == 'verified') bg-success 
                    @elseif($user->verification_status == 'pending') bg-warning 
                    @elseif($user->verification_status == 'rejected') bg-danger 
                    @else bg-secondary @endif
                    px-3 py-2">
            <i data-feather="
                        @if ($user->verification_status == 'verified') check-circle
                        @elseif($user->verification_status == 'pending') clock
                        @elseif($user->verification_status == 'rejected') x-circle
                        @else help-circle @endif
                    "
              class="me-1"></i>

            {{ ucfirst($user->verification_status) }}
          </span>
        </div>


        {{-- DROPDOWN + SUBMIT BUTTON --}}
        <form action="{{ route('member.residents.update-verification', $user->id) }}" method="POST"
          class="d-flex align-items-center mt-2">
          @csrf
          @method('PATCH')

          <strong class="me-2">Change:</strong>

          <select class="form-select form-select-sm w-auto me-3" name="verification_status">
            <option value="unverified" {{ $user->verification_status == 'unverified' ? 'selected' : '' }}>
              Unverified
            </option>
            <option value="pending" {{ $user->verification_status == 'pending' ? 'selected' : '' }}>
              Pending
            </option>
            <option value="verified" {{ $user->verification_status == 'verified' ? 'selected' : '' }}>
              Verified
            </option>
            <option value="rejected" {{ $user->verification_status == 'rejected' ? 'selected' : '' }}>
              Rejected
            </option>
          </select>

          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-arrow-repeat me-1"></i> Update
          </button>
        </form>
      </div>
    </div>

    {{-- MAIN PROFILE CARD --}}
    <div class="glass-card mb-4">
      <div class="gradient-header p-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0"><i class="mdi mdi-account-details-outline"></i> Profile Details</h5>

        @if (!empty($user->details->society->profile_image))
          <img src="{{ asset('storage/' . $user->details->society->profile_image) }}"
            style="height:55px;width:55px;object-fit:cover" class="rounded shadow">
        @endif
      </div>

      <div class="p-4 row">

        {{-- BASIC INFO --}}
        <div class="col-md-4 mb-4">
          <div class="section-title">Basic Info</div>

          <p><strong>Full Name:</strong> {{ $user->details->full_name ?? '-' }}</p>
          <p><strong>Gender:</strong> {{ $user->details->gender ?? '-' }}</p>
          <p><strong>Date of Birth:</strong> {{ $user->details->date_of_birth ?? '-' }}</p>
        </div>

        {{-- CONTACT --}}
        <div class="col-md-4 mb-4">
          <div class="section-title">Contact</div>

          <p><strong>Email:</strong> {{ $user->details->email ?? '-' }}</p>
          <p><strong>Phone:</strong> {{ $user->details->phone ?? '-' }}</p>
          <p><strong>Address:</strong> {{ $user->details->address ?? '-' }}</p>

          <div class="section-title mt-4">Work & Family</div>
          <p><strong>Occupation:</strong> {{ $user->details->occupation ?? '-' }}</p>
          <p><strong>Marital Status:</strong> {{ $user->details->marital_status ?? '-' }}</p>
        </div>

        {{-- RESIDENCE --}}
        <div class="col-md-4 mb-4">
          <div class="section-title">Residence</div>

          <p><strong>Society:</strong>
            <span class="badge bg-primary info-badge">
              {{ $user->details->society->name ?? 'N/A' }}
            </span>
          </p>

          {{-- Flat Info --}}
          <div class="p-3 bg-light rounded border shadow-sm mb-3">
            <div class="d-flex align-items-center mb-2">
              <div class="icon-box me-3"><i class="mdi mdi-home-city-outline"></i></div>
              <h6 class="fw-bold mb-0">Flat Information</h6>
            </div>

            <p><strong>Building No:</strong> {{ $user->details->flat->building_no ?? 'N/A' }}</p>
            <p><strong>Owner:</strong> {{ $user->details->flat->owner_name ?? 'N/A' }}</p>
            <p><strong>Floor No:</strong> {{ $user->details->floor_number ?? 'N/A' }}</p>
            <p><strong>Status:</strong>
              <span class="badge bg-info info-badge">{{ $user->details->flat->status ?? 'N/A' }}</span>
            </p>
          </div>

          <div class="section-title">Emergency</div>
          <p><strong>Name:</strong> {{ $user->details->emergency_contact_name ?? '-' }}</p>
          <p><strong>Phone:</strong> {{ $user->details->emergency_contact_phone ?? '-' }}</p>

          <p class="mt-3"><strong>Joined:</strong> {{ $user->created_at->format('d M Y') }}</p>
        </div>
      </div>
    </div>




    {{-- JOBS CREATED --}}
    {{-- <div class="glass-card mb-5">
      <div class="gradient-header p-3">
        <h5 class="fw-bold mb-0"><i class="mdi mdi-briefcase-outline"></i> Jobs Created</h5>
      </div>

      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="bg-light">
            <tr>
              <th>ID</th>
              <th>Job Title</th>
              <th>Price</th>
              <th>Status</th>
              <th>Created Date</th>
            </tr>
          </thead>
          <tbody>
            @forelse($jobs as $job)
              <tr>
                <td>{{ $job->id }}</td>
                <td><strong>{{ $job->job_title }}</strong></td>
                <td>{{ 'INR ' . $job->pay_amount ?? '-' }}</td>
                <td><span class="badge bg-info info-badge">{{ $job->status }}</span></td>
                <td>{{ $job->created_at->format('d M Y') }}</td>
                {{-- <td><button class="btn btn-sm btn-primary"
                                onclick="window.location='{{ route('jobs.view', $job->id) }}'">
                                View Job
                            </button></td> 
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-3">
                  <i class="mdi mdi-information-outline"></i> No jobs created by this user.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div> --}}

  </div>

  {{-- VEHICLES --}}
  <div class="glass-card mb-5">
    <div class="gradient-header p-3">
      <h5 class="fw-bold mb-0"><i class="mdi mdi-briefcase-outline"></i> Registered Vehicles</h5>
    </div>
    <div class="card-body">
      @if ($vehicles->count())
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="bg-light">
              <tr>
                <th>ID</th>
                <th>Vehicle Number</th>
                <th>Type</th>
                <th>Color</th>
                <th>Update Status</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($vehicles as $vehicle)
                <tr>
                  <td>{{ $vehicle->id }}</td>
                  <td>{{ strtoupper($vehicle->vehicle_number) }}</td>
                  <td>{{ ucfirst($vehicle->vehicle_type ?? '-') }}</td>
                  <td>{{ $vehicle->color ?? '-' }}</td>
                  <td>
                    <form action="{{ route('member.resiVehicle.update-status', $vehicle->id) }}" method="POST"
                      class="d-flex gap-2">
                      @csrf
                      @method('PATCH')

                      <select name="status" class="form-select form-select-sm w-auto">
                        <option value="pending" {{ $vehicle->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="active" {{ $vehicle->status == 'active' ? 'selected' : '' }}>Active
                        </option>
                        <option value="inactive" {{ $vehicle->status == 'inactive' ? 'selected' : '' }}>Inactive
                        </option>
                      </select>

                      <button class="btn btn-primary btn-sm">
                        Update
                      </button>
                    </form>
                  </td>

                  <td>
                    <span class="badge {{ $vehicle->status == 'active' ? 'bg-success' : 'bg-secondary' }}">
                      {{ ucfirst($vehicle->status) }}
                    </span>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @else
          <div class="text-muted text-center">No vehicles registered</div>
      @endif
    </div>
  </div>
  </div>

  {{-- DOCUMENT VERIFICATION SECTION --}}
  <div class="glass-card mb-4">
    <div class="gradient-header p-3 d-flex justify-content-between align-items-center">
      <h5 class="fw-bold mb-0">
        <i data-feather="file-text"></i> Documents Verification
      </h5>
    </div>

    <div class="p-4">

      @if ($documents->count())
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="bg-light">
              <tr>
                <th>#</th>
                <th>Document Type</th>
                <th>File</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Updated At</th>
                <th>Action</th>
              </tr>
            </thead>

            <tbody>
              @foreach ($documents as $doc)
                <tr>
                  <td>{{ $doc->id }}</td>

                  <td class="fw-semibold text-capitalize">{{ str_replace('_', ' ', $doc->document_type) }}</td>

                  <td>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                      data-bs-target="#docModal{{ $doc->id }}">
                      View
                    </button>
                    </a>
                  </td>

                  {{-- STATUS DROPDOWN --}}
                  <td>
                    <form action="{{ route('member.residents.documents.update-status', $doc->id) }}" method="POST"
                      class="d-flex">
                      @csrf
                      @method('PATCH')

                      <select name="status" class="form-select form-select-sm w-auto">
                        <option value="pending" {{ $doc->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ $doc->status == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ $doc->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                      </select>
                  </td>

                  {{-- REMARKS --}}
                  <td>
                    <input type="text" name="remarks" class="form-control form-control-sm"
                      value="{{ $doc->remarks ?? '' }}" placeholder="Add notes...">
                  </td>

                  <td>{{ $doc->updated_at->format('d M Y | h:i A') }}</td>

                  {{-- SUBMIT --}}
                  <td>
                    <button type="submit" class="btn btn-primary btn-sm">
                      <i data-feather="check-circle"></i> Update
                    </button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <div class="text-center text-muted py-3">
          <i data-feather="info"></i> No documents uploaded by this user.
        </div>
      @endif

    </div>
  </div>

  @foreach ($documents as $doc)
    <div class="modal fade" id="docModal{{ $doc->id }}" tabindex="-1"
      aria-labelledby="docModal{{ $doc->id }}Label" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title text-capitalize">
              {{ str_replace('_', ' ', $doc->document_type) }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body p-3" style="max-height:75vh; overflow:auto;">
            @php
              $ext = strtolower(pathinfo($doc->file_path, PATHINFO_EXTENSION));
            @endphp

            @if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp']))
              <div class="text-center">
                <img src="{{ asset('storage/' . $doc->file_path) }}" class="rounded shadow"
                  style="max-width:100%; max-height:70vh; object-fit:contain;">
              </div>
            @else
              <iframe src="{{ asset('storage/' . $doc->file_path) }}" style="width:100%; height:70vh; border:none;">
              </iframe>
            @endif
          </div>

        </div>
      </div>
    </div>
  @endforeach
@endsection
