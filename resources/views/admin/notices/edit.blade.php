@extends('admin.layouts.app')

@section('content')
  <div class="container-fluid">
    <h4 class="mb-3">Edit Notice</h4>

    <form action="{{ route('admin.notices.update', $notice->id) }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      @include('admin.notices.form', ['notice' => $notice])
    </form>
  </div>
@endsection
