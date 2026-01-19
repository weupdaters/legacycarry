@extends('admin.layouts.app')

@section('content')
<div class="row">
    <div class="col-md-4">
        <h5 class="mb-3">Chats</h5>

        <div class="list-group">
            @foreach ($chats as $c)
                <a href="{{ url('admin/chats/'.$c->chat_id) }}"
                    class="list-group-item list-group-item-action">
                    <strong>{{ implode(", ", json_decode($c->user_names, true)) }}</strong>
                    <div class="text-muted small">{{ $c->last_message }}</div>
                </a>
            @endforeach
        </div>
    </div>
</div>
@endsection
