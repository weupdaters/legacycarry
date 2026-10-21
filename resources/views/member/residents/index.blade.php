@extends('member.layouts.app')

@section('content')
  <!-- Start Content-->
  <div class="container-fluid">

    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
      <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0">Residents Management </h4>


      </div>


      {{-- <div class="text-end">
            <a href="{{ route('users.create') }}" class="btn btn-success btn-sm">Add New User</a>
            
        </div> --}}
    </div>


    <div class="row">
      <div class="col-12">
        <div class="card">


          <div class="card-body">
            @if (session('success'))
              <div class="alert alert-success">{{ session('success') }}</div>
            @endif


            <div class="table-responsive">
              <table class="table datatable" id="datatable_1">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Society</th>

                    {{-- New Fields Added --}}
                    <th>Flat</th>
                    <th>Village/Town</th>

                    <th>Active</th>
                    <th>Action</th>
                  </tr>
                </thead>

                <tbody>
                  @foreach ($residents as $resident)
                    @php
                      $user = $resident instanceof \App\Models\User ? $resident : ($resident->user ?? $resident);
                      $detail = $user->details ?? ($resident->details ?? null);
                      $flat = $detail->flat ?? ($user->residentAssignment?->flat ?? ($resident->flat ?? null));
                      $societyName = $detail->society->name 
                          ?? $user->society->name 
                          ?? $user->residentAssignment->society->name 
                          ?? assingedSocietyName($user->id) 
                          ?? null;
                    @endphp

                    <tr>
                      <td>{{ $user->id }}</td>
                      <td>{{ $user->name }}</td>
                      <td>{{ $user->phone ?? '-' }}</td>
                      <td>{{ $user->email ?? '-' }}</td>
                      <td>
                        @if ($societyName)
                          <span class="badge bg-soft-primary text-primary fw-semibold">{{ $societyName }}</span>
                        @else
                          <span class="text-muted">-</span>
                        @endif
                      </td>

                      {{-- New fields from user_details / flat table --}}
                      <td>{{ $flat->building_no ?? ($flat->flat_no ?? '-') }}</td>
                      <td>{{ $detail->address ?? ($user->address ?? '-') }}</td>

                      <td>
                        @if ($user->is_active)
                          <span class="badge bg-success">Active</span>
                        @else
                          <span class="badge bg-danger">Banned</span>
                        @endif
                      </td>

                      <td>
                        @if (has_permission('resident_show'))
                          <a href="{{ route('member.residents.view', $user->id) }}" class="btn btn-info btn-sm">
                            View
                          </a>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

          </div><!-- end card -->
        </div><!-- end col-12 -->
      </div><!-- end row -->
    </div><!-- end container-fluid -->
  @endsection
