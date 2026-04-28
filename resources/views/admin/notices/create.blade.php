@extends('admin.layouts.app')

@section('content')
  <div class="container-fluid">
    <h4 class="mb-3">Add Notice</h4>

    <form action="{{ route('admin.notices.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      @include('admin.notices.form', ['notice' => null])
    </form>
  </div>
@endsection
