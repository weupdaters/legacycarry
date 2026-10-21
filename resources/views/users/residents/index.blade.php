@extends('users.layouts.app')

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
                    @if(session('success'))
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
                
                                {{-- New Fields Added --}}
                                <th>Flat</th>
                                <th>Village/Town</th>
                
                                <th>Active</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                
                        <tbody>
                            @foreach($residents as $resident)
                                @php
                                    $detail = $resident->details ?? null;
                                @endphp
                
                                <tr>
                                    <td>{{ $resident->id }}</td>
                                    <td>{{ $resident->user->name }}</td>
                                    <td>{{ $resident->user->phone }}</td>
                                    <td>{{ $resident->user->email }}</td>
                
                                    {{-- New fields from user_details table --}}
                                    <td>{{ $resident->flat->building_no ?? '-' }}</td>
                                    <td>{{ $resident->address ?? '-' }}</td>
                
                                    <td>
                                        @if($resident->user->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Banned</span>
                                        @endif
                                    </td>
                
                                    <td>
                                        <a href="{{ route('users.residents.view', $resident->user->id) }}" 
                                            class="btn btn-info btn-sm">
                                            View
                                         </a>
                                         

                                      
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
