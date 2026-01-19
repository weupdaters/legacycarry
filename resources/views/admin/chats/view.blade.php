@extends('admin.layouts.app')

@section('content')
<div class="row">

    <!-- LEFT SIDE CHAT LIST -->
    <div class="col-md-3 border-end">
        <h5 class="mb-3">Chats</h5>

        @php
            $all = DB::table('chat_rooms')->orderBy('last_message_time','desc')->get();
        @endphp

        @foreach ($all as $c)
            <a href="{{ url('admin/chats/'.$c->chat_id) }}"
               class="d-block p-2 mb-1 {{ $c->chat_id == $chat->chat_id ? 'bg-primary text-white' : 'bg-light' }}">
                <strong>{{ implode(", ", json_decode($c->user_names, true)) }}</strong>
                <div class="small">{{ $c->last_message }}</div>
            </a>
        @endforeach
    </div>

    <!-- RIGHT SIDE CHAT WINDOW -->
    <div class="col-md-9">

        <h5>Chat: {{ implode(", ", json_decode($chat->user_names, true)) }}</h5>
        <hr>

        <div style="height: 70vh; overflow-y: auto; background: #f9f9f9; padding: 20px;">

            @foreach ($messages as $msg)
                <div class="mb-3 {{ $msg->sender == 'admin' ? 'text-end' : '' }}">
                    <div style="
                        display:inline-block;
                        padding:10px 15px;
                        border-radius:10px;
                        background: {{ $msg->sender == 'admin' ? '#d1f7d1' : 'white' }};
                    ">
                        {{ $msg->message }}

                        @if ($msg->file_url)
                            <br><a href="{{ $msg->file_url }}" target="_blank">📎 Attachment</a>
                        @endif
                    </div>

                    <div class="text-muted small">
                        {{ $msg->created_at }}
                    </div>
                </div>
            @endforeach

        </div>

    </div>
</div>
@endsection
