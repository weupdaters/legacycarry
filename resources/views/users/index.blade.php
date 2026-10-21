@extends('layouts.app')

@section('content')
  <!-- Start Content-->
  <div class="container-fluid">

    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Users Management </h4>
            
            
        </div>

        <div class="text-end">
            <a href="{{ route('users.create') }}" class="btn btn-success btn-sm">Add New User</a>
            
        </div>
    </div>

    
    <div class="row">
        <div class="col-12">
            <div class="card">
               

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                   
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datatable" id="datatable_1">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Active</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->phone }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->role }}</td>
                                    <td>{{ $user->is_active ? 'Yes' : 'No' }}</td>
                                    <td>{{ $user->created_at->format('d M Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div><!-- end card-body -->
            </div><!-- end card -->
        </div><!-- end col-12 -->
    </div><!-- end row -->
</div><!-- end container-fluid -->
@endsection
