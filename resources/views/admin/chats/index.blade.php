@extends('admin.layouts.app')

@section('content')
  <style>
    .chat-container {
      height: calc(100vh - 120px);
      display: flex;
      flex-direction: column;
      background: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 0 20px rgba(0, 0, 0, .08)
    }

    .chat-header {
      padding: 15px 20px;
      border-bottom: 1px solid #eee;
      font-weight: 600;
      font-size: 18px;
      background: #fff
    }

    .messages-container {
      flex: 1;
      overflow-y: auto;
      padding: 20px;
      background: #f7f9fc
    }

    .message-row {
      display: flex;
      margin-bottom: 15px
    }

    .message-row.right {
      justify-content: flex-end
    }

    .message-bubble {
      max-width: 65%;
      padding: 10px 15px;
      border-radius: 15px;
      font-size: 14px
    }

    .message-row.left .message-bubble {
      background: #e9ecef
    }

    .message-row.right .message-bubble {
      background: #4e73df;
      color: white
    }

    .message-time {
      font-size: 11px;
      margin-top: 3px;
      opacity: .7;
      text-align: right
    }
  </style>


  <div class="container-fluid py-3">

    <div class="chat-container">

      <div class="chat-header">

        {{ $resident->name }} ↔ {{ $employer->name }}

      </div>


      <div id="messages" class="messages-container">

        <div class="text-center text-muted">
          Loading messages...
        </div>

      </div>

    </div>

  </div>


  <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-firestore-compat.js"></script>


  <script>
    const firebaseConfig = {

      apiKey: "77648a1a3336554e3cdfd29e59aa9d3f48e4de67",

      authDomain: "legacycarry.firebaseapp.com",

      projectId: "legacycarry",

      storageBucket: "legacycarry.appspot.com",

      messagingSenderId: "102855073007460176706",

      appId: "102855073007460176706"

    };

    firebase.initializeApp(firebaseConfig);

    const db = firebase.firestore();

    let currentChatId = "{{ $chatId }}";

    let usersMap = @json($users);


    function formatTime(ts) {

      if (!ts) return "";

      const d = ts.toDate();

      return d.toLocaleTimeString([], {

        hour: '2-digit',

        minute: '2-digit'

      })

    }


    function loadMessages() {

      db.collection("chats")

        .doc(currentChatId)

        .collection("messages")

        .orderBy("createdAt")

        .onSnapshot(snapshot => {

          const container = document.getElementById("messages");

          container.innerHTML = "";

          if (snapshot.empty) {

            container.innerHTML = '<div class="text-center text-muted">No messages yet</div>';

            return;

          }

          snapshot.forEach(doc => {

            const m = doc.data();

            const isRight = m.senderId == "{{ $employer->id }}";

            const time = formatTime(m.createdAt);

            container.insertAdjacentHTML("beforeend",

              `

<div class="message-row ${isRight?'right':'left'}">

<div class="message-bubble">

<div>${m.text}</div>

<div class="message-time">${time}</div>

</div>

</div>

`

            );

          });

          container.scrollTop = container.scrollHeight;

        });

    }

    loadMessages();
  </script>
@endsection
