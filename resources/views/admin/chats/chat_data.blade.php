@extends('admin.layouts.app')

@section('content')
  <style>
    :root {
      --primary-color: #4e73df;
      --secondary-color: #6c757d;
      --light-bg: #f8f9fa;
      --border-color: #e9ecef;
      --message-left: #e9ecef;
      --message-right: #4e73df;
      --hover-bg: #f1f5fd;
    }

    .chat-container {
      height: calc(100vh - 100px);
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
    }

    /* Chat List Styles */
    .chat-list-container {
      background: var(--light-bg);
      border-right: 1px solid var(--border-color);
    }

    .chat-list-header {
      background: white;
      padding: 1.25rem;
      border-bottom: 1px solid var(--border-color);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    .chat-search {
      background: #f8f9fa;
      border: 1px solid #e9ecef;
      border-radius: 8px;
      padding: 0.5rem 1rem;
      font-size: 0.875rem;
    }

    .chat-search:focus {
      outline: none;
      box-shadow: 0 0 0 2px rgba(78, 115, 223, 0.1);
      background: white;
    }

    .chat-item {
      padding: 1rem 1.25rem;
      border-bottom: 1px solid var(--border-color);
      transition: all 0.2s ease;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .chat-item:hover {
      background: var(--hover-bg);
      transform: translateX(2px);
    }

    .chat-item.active {
      background: linear-gradient(90deg, rgba(78, 115, 223, 0.1) 0%, rgba(78, 115, 223, 0.05) 100%);
      border-left: 4px solid var(--primary-color);
      padding-left: 1rem;
    }

    .chat-avatar {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary-color), #6a11cb);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 600;
      font-size: 1rem;
      flex-shrink: 0;
    }

    .chat-info {
      flex: 1;
      min-width: 0;
    }

    .chat-users {
      font-weight: 600;
      color: #2d3748;
      margin-bottom: 4px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .chat-preview {
      font-size: 0.875rem;
      color: var(--secondary-color);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .chat-meta {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 4px;
      min-width: 60px;
    }

    .chat-time {
      font-size: 0.75rem;
      color: #a0aec0;
    }

    .chat-badge {
      background: var(--primary-color);
      color: white;
      font-size: 0.7rem;
      padding: 2px 6px;
      border-radius: 10px;
      min-width: 20px;
      text-align: center;
    }

    /* Chat History Styles */
    .chat-history-container {
      display: flex;
      flex-direction: column;
      background: white;
    }

    .chat-header {
      padding: 1.25rem 1.5rem;
      background: white;
      border-bottom: 1px solid var(--border-color);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .chat-title {
      font-weight: 700;
      color: #2d3748;
      font-size: 1.25rem;
    }

    .chat-subtitle {
      font-size: 0.875rem;
      color: var(--secondary-color);
    }

    .chat-actions {
      display: flex;
      gap: 8px;
    }

    .btn-chat-action {
      width: 38px;
      height: 38px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 1px solid var(--border-color);
      background: white;
      color: var(--secondary-color);
      transition: all 0.2s ease;
    }

    .btn-chat-action:hover {
      background: var(--light-bg);
      color: var(--primary-color);
      transform: translateY(-1px);
    }

    .messages-container {
      flex: 1;
      overflow-y: auto;
      padding: 1.5rem;
      background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
    }

    .message-date {
      text-align: center;
      margin: 1.5rem 0;
      position: relative;
    }

    .message-date::before {
      content: '';
      position: absolute;
      left: 0;
      right: 0;
      top: 50%;
      height: 1px;
      background: var(--border-color);
      z-index: 1;
    }

    .date-label {
      background: white;
      padding: 0.25rem 1rem;
      border-radius: 20px;
      font-size: 0.875rem;
      color: var(--secondary-color);
      border: 1px solid var(--border-color);
      position: relative;
      z-index: 2;
      display: inline-block;
    }

    .message-row {
      display: flex;
      margin-bottom: 1.25rem;
      gap: 12px;
    }

    .message-row.right {
      flex-direction: row-reverse;
    }

    .message-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 600;
      font-size: 0.875rem;
      flex-shrink: 0;
      margin-top: 4px;
    }

    .message-row.right .message-avatar {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .message-content {
      max-width: 65%;
    }

    .message-bubble {
      padding: 12px 16px;
      border-radius: 18px;
      position: relative;
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .message-row.left .message-bubble {
      background: white;
      border: 1px solid var(--border-color);
      border-top-left-radius: 4px;
    }

    .message-row.right .message-bubble {
      background: var(--message-right);
      color: white;
      border-top-right-radius: 4px;
    }

    .message-sender {
      font-weight: 600;
      font-size: 0.875rem;
      margin-bottom: 4px;
      color: #4a5568;
    }

    .message-row.right .message-sender {
      color: rgba(255, 255, 255, 0.9);
    }

    .message-text {
      line-height: 1.5;
      word-wrap: break-word;
    }

    .message-time {
      font-size: 0.75rem;
      margin-top: 4px;
      opacity: 0.8;
      text-align: right;
    }

    .message-row.right .message-time {
      color: rgba(255, 255, 255, 0.8);
    }

    .message-row.left .message-time {
      color: var(--secondary-color);
    }

    /* Empty State */
    .empty-state {
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 2rem;
    }

    .empty-icon {
      width: 80px;
      height: 80px;
      background: var(--light-bg);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
      color: var(--secondary-color);
      font-size: 2rem;
    }

    .empty-title {
      font-weight: 600;
      color: #2d3748;
      margin-bottom: 0.5rem;
    }

    .empty-text {
      color: var(--secondary-color);
      max-width: 300px;
      margin: 0 auto;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .chat-container {
        height: calc(100vh - 120px);
        border-radius: 0;
      }

      .chat-avatar {
        width: 40px;
        height: 40px;
      }

      .message-content {
        max-width: 80%;
      }

      .chat-header {
        padding: 1rem;
      }

      .chat-list-header {
        padding: 1rem;
      }
    }

    @media (max-width: 576px) {
      .chat-meta {
        display: none;
      }

      .message-content {
        max-width: 85%;
      }
    }

    /* Scrollbar Styling */
    .messages-container::-webkit-scrollbar,
    #chatList::-webkit-scrollbar {
      width: 6px;
    }

    .messages-container::-webkit-scrollbar-track,
    #chatList::-webkit-scrollbar-track {
      background: #f1f1f1;
    }

    .messages-container::-webkit-scrollbar-thumb,
    #chatList::-webkit-scrollbar-thumb {
      background: #c1c1c1;
      border-radius: 3px;
    }

    .messages-container::-webkit-scrollbar-thumb:hover,
    #chatList::-webkit-scrollbar-thumb:hover {
      background: #a8a8a8;
    }

    /* Modal Styles */
    .modal-chat-info .modal-body {
      padding: 1.5rem;
    }

    .info-item {
      display: flex;
      align-items: center;
      padding: 0.75rem 0;
      border-bottom: 1px solid var(--border-color);
    }

    .info-item:last-child {
      border-bottom: none;
    }

    .info-label {
      min-width: 120px;
      font-weight: 600;
      color: #4a5568;
    }

    .info-value {
      color: #2d3748;
    }
  </style>

  <div class="container-fluid py-3">
    <div class="chat-container bg-white">
      <div class="row h-100">
        <!-- Chat List Column -->
        <div class="col-lg-4 col-md-5 h-100 chat-list-container">
          <div class="chat-list-header">
            <h5 class="mb-3 fw-bold text-dark">All Conversations</h5>
            <input type="text" id="chatSearch" class="form-control chat-search" placeholder="Search conversations...">
          </div>

          <div id="chatList" class="h-100 overflow-auto" style="height: calc(100% - 85px) !important;">
            <!-- Chat items will be loaded here -->
            <div class="empty-state" id="emptyChatList">
              <div>
                <div class="empty-icon">
                  <i class="fas fa-comments"></i>
                </div>
                <h5 class="empty-title">No conversations yet</h5>
                <p class="empty-text">When users start chatting, conversations will appear here.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Chat History Column -->
        <div class="col-lg-8 col-md-7 h-100 chat-history-container">
          <div class="chat-header">
            <div>
              <div class="chat-title" id="chatTitle">Select a conversation</div>
              <div class="chat-subtitle" id="chatSubtitle">Click on a chat to view messages</div>
            </div>
            <div class="chat-actions">
              <button class="btn-chat-action" id="refreshChat" title="Refresh Messages">
                <i class="fas fa-sync-alt"></i>
              </button>
              <button class="btn-chat-action" id="chatInfo" title="Chat Information">
                <i class="fas fa-info-circle"></i>
              </button>
            </div>
          </div>

          <div class="messages-container" id="messages">
            <!-- Empty state when no chat selected -->
            <div class="empty-state" id="emptyMessages">
              <div>
                <div class="empty-icon">
                  <i class="fas fa-comment-alt"></i>
                </div>
                <h5 class="empty-title">No conversation selected</h5>
                <p class="empty-text">Select a conversation from the list to view messages</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Chat Info Modal -->
  <div class="modal fade" id="chatInfoModal" tabindex="-1" aria-labelledby="chatInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="chatInfoModalLabel">Chat Information</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body modal-chat-info">
          <div class="info-item">
            <span class="info-label">Chat ID:</span>
            <span class="info-value" id="modalChatId">-</span>
          </div>
          <div class="info-item">
            <span class="info-label">Participants:</span>
            <span class="info-value" id="modalParticipants">-</span>
          </div>
          <div class="info-item">
            <span class="info-label">Total Messages:</span>
            <span class="info-value" id="modalMessageCount">0</span>
          </div>
          <div class="info-item">
            <span class="info-label">Last Active:</span>
            <span class="info-value" id="modalLastActive">-</span>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-firestore-compat.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>

  <script>
    // 🔐 Web App config ONLY
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

    const usersMap = @json($users);
    let unsubscribeMessages = null;
    let currentChatId = null;
    let currentChatData = null;
    let messageCount = 0;

    // Helper function to get initials from name
    function getInitials(name) {
      if (!name || typeof name !== 'string') return '??';
      return name
        .split(' ')
        .map(word => word[0] || '')
        .join('')
        .toUpperCase()
        .slice(0, 2);
    }

    // Robust converter: Firestore Timestamp | JS Date | ISO string -> Date
    function toDate(ts) {
      if (!ts) return null;
      // Firestore Timestamp
      if (ts.seconds && typeof ts.seconds === 'number') {
        const ms = ts.seconds * 1000 + Math.floor((ts.nanoseconds || 0) / 1000000);
        return new Date(ms);
      }
      if (ts instanceof Date) return ts;
      // If number (ms) or string
      const d = new Date(ts);
      return isNaN(d) ? null : d;
    }

    // Helper function to format time (relative)
    function formatTime(timestamp) {
      const date = toDate(timestamp);
      if (!date) return '';

      const now = new Date();
      const diffMs = now - date;
      const diffMins = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMs / 3600000);

      if (diffMins < 1) return 'Just now';
      if (diffMins < 60) return `${diffMins}m ago`;
      if (diffHours < 24) return `${diffHours}h ago`;
      if (diffHours < 168) return date.toLocaleDateString('en-US', {
        weekday: 'short'
      });

      return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
      });
    }

    // Helper function to format message timestamp for bubble
    function formatMessageTime(timestamp) {
      const date = toDate(timestamp);
      if (!date) return '';
      return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
      });
    }

    // Format date for message grouping
    function formatMessageDate(timestamp) {
      const date = toDate(timestamp);
      if (!date) return '';

      const today = new Date();
      const yesterday = new Date(today);
      yesterday.setDate(yesterday.getDate() - 1);

      if (date.toDateString() === today.toDateString()) {
        return 'Today';
      } else if (date.toDateString() === yesterday.toDateString()) {
        return 'Yesterday';
      } else {
        return date.toLocaleDateString('en-US', {
          weekday: 'long',
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });
      }
    }

    // Function to show loading spinner
    function showLoading() {
      const messagesContainer = document.getElementById('messages');
      messagesContainer.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading messages...</p>
        </div>
    `;
    }

    // Function to show empty state
    function showEmptyState() {
      const messagesContainer = document.getElementById('messages');
      messagesContainer.innerHTML = `
        <div class="empty-state">
            <div>
                <div class="empty-icon">
                    <i class="fas fa-comment-alt"></i>
                </div>
                <h5 class="empty-title">No conversation selected</h5>
                <p class="empty-text">Select a conversation from the list to view messages</p>
            </div>
        </div>
    `;
    }

    // Function to show error state
    function showErrorState(error) {
      const messagesContainer = document.getElementById('messages');
      messagesContainer.innerHTML = `
        <div class="empty-state">
            <div>
                <div class="empty-icon text-danger">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h5 class="empty-title">Error loading messages</h5>
                <p class="empty-text">${error || 'Failed to load chat messages. Please try again.'}</p>
                <button class="btn btn-primary mt-3" onclick="reloadCurrentChat()">
                    <i class="fas fa-redo me-2"></i>Retry
                </button>
            </div>
        </div>
    `;
    }

    // Function to reload current chat
    function reloadCurrentChat() {
      if (currentChatId) {
        openChat(currentChatId);
      }
    }

    // Function to update chat info modal (uses users array from currentChatData)
    function updateChatInfoModal() {
      if (!currentChatData) return;

      const users = currentChatData.users || [];
      const userName1 = usersMap[users[0]] || users[0] || '-';
      const userName2 = usersMap[users[1]] || users[1] || '-';

      document.getElementById('modalChatId').textContent = currentChatId;
      document.getElementById('modalParticipants').textContent = users.length ? `${userName1} & ${userName2}` : '-';
      document.getElementById('modalMessageCount').textContent = messageCount || 0;
      document.getElementById('modalLastActive').textContent =
        currentChatData.lastMessageTime ? formatTime(currentChatData.lastMessageTime) : 'Never';
    }

    /* ---------- Load chats ---------- */
    db.collection('chats')
      .orderBy('lastMessageTime', 'desc')
      .onSnapshot(snapshot => {
        const list = document.getElementById('chatList');
        const emptyState = document.getElementById('emptyChatList');

        if (snapshot.empty) {
          list.innerHTML = '';
          emptyState.classList.remove('d-none');
          return;
        }

        emptyState.classList.add('d-none');
        list.innerHTML = '';

        snapshot.forEach(doc => {
          const data = doc.data();
          const users = data.users || [];

          // Create chat title from user names
          const userNames = users.map(id => usersMap[id] || 'Unknown User');
          const title = userNames.join(' ↔ ');

          // Get initials for avatar
          const firstUser = users[0] || '';
          const firstUserName = usersMap[firstUser] || '';
          const initials = getInitials(firstUserName);

          // Format last message time
          const lastTime = formatTime(data.lastMessageTime);

          // Truncate last message
          const lastMessage = data.lastMessage || '';
          const truncatedMessage = lastMessage.length > 40 ?
            lastMessage.substring(0, 40) + '...' :
            lastMessage;

          const chatItem = document.createElement('div');
          chatItem.className = `chat-item ${currentChatId === doc.id ? 'active' : ''}`;
          chatItem.dataset.id = doc.id;
          chatItem.innerHTML = `
                <div class="chat-avatar">${initials}</div>
                <div class="chat-info">
                    <div class="chat-users">${title}</div>
                    <div class="chat-preview">${truncatedMessage || 'No messages yet'}</div>
                </div>
                <div class="chat-meta">
                    <div class="chat-time">${lastTime}</div>
                </div>
            `;

          chatItem.addEventListener('click', (e) => {
            e.stopPropagation();
            // Pass the users array to openChat to avoid deriving IDs from doc.id
            openChat(doc.id, userNames);
          });

          list.appendChild(chatItem);
        });
      }, error => {
        console.error('Error loading chats:', error);
      });

    /* ---------- Open chat (FIXED) ---------- */
    function openChat(chatId, userNames = null) {
      if (currentChatId === chatId) return;

      // Unsubscribe from previous chat messages
      if (unsubscribeMessages) {
        try {
          unsubscribeMessages();
        } catch (e) {
          /* ignore */
        }
        unsubscribeMessages = null;
      }

      currentChatId = chatId;

      // Update active state in chat list
      document.querySelectorAll('.chat-item').forEach(el => {
        el.classList.toggle('active', el.dataset.id === chatId);
      });

      // Hide empty state
      document.getElementById('emptyMessages').classList.add('d-none');

      // Get chat data first
      db.collection('chats').doc(chatId).get()
        .then(doc => {
          if (!doc.exists) {
            showErrorState('Chat not found');
            return;
          }

          currentChatData = doc.data();

          // Use the users array from the chat document
          const users = currentChatData.users || [];

          // If userNames not provided, derive from users array & usersMap
          if (!userNames) {
            userNames = users.map(id => usersMap[id] || id);
          }

          const userName1 = userNames[0] || 'Unknown';
          const userName2 = userNames[1] || (userNames.length > 1 ? userNames[1] : 'Unknown');

          document.getElementById('chatTitle').textContent = `${userName1} & ${userName2}`;
          document.getElementById('chatSubtitle').textContent = 'Chat history';

          // Show loading state
          showLoading();

          // Load messages and pass participants array
          loadMessages(chatId, users);
        })
        .catch(error => {
          console.error('Error loading chat:', error);
          showErrorState(error.message);
        });
    }

    /* ---------- Load messages (uses participants list) ---------- */
    function loadMessages(chatId, participants = []) {
      const messagesContainer = document.getElementById('messages');
      messageCount = 0;
      let lastDate = null;

      // Ensure any prior listener removed
      if (unsubscribeMessages) {
        try {
          unsubscribeMessages();
        } catch (e) {
          /* ignore */
        }
        unsubscribeMessages = null;
      }

      unsubscribeMessages = db.collection('chats')
        .doc(chatId)
        .collection('messages')
        .orderBy('createdAt')
        .onSnapshot(snapshot => {
          if (snapshot.empty) {
            messagesContainer.innerHTML = `
                    <div class="empty-state">
                        <div>
                            <div class="empty-icon">
                                <i class="fas fa-comment-slash"></i>
                            </div>
                            <h5 class="empty-title">No messages yet</h5>
                            <p class="empty-text">This conversation doesn't have any messages yet.</p>
                        </div>
                    </div>
                `;
            return;
          }

          messagesContainer.innerHTML = '';
          messageCount = snapshot.size;
          lastDate = null;

          snapshot.forEach(doc => {
            const message = doc.data();

            // If participants list is provided, ensure sender is part of it
            if (participants.length > 0 && !participants.includes(message.senderId)) return;

            // Decide left/right based on first participant in the chat (keeps your layout)
            const isLeft = participants.length ? (message.senderId === participants[0]) : false;
            const senderName = usersMap[message.senderId] || message.senderId || 'Unknown';
            const initials = getInitials(senderName);
            const time = formatMessageTime(message.createdAt);
            const messageDate = formatMessageDate(message.createdAt);

            // Add date separator if date changed
            if (messageDate !== lastDate) {
              messagesContainer.insertAdjacentHTML('beforeend', `
                        <div class="message-date">
                            <span class="date-label">${messageDate}</span>
                        </div>
                    `);
              lastDate = messageDate;
            }

            messagesContainer.insertAdjacentHTML('beforeend', `
                    <div class="message-row ${isLeft ? 'left' : 'right'}">
                        ${isLeft ? `<div class="message-avatar">${initials}</div>` : ''}
                        <div class="message-content">
                            ${isLeft ? `<div class="message-sender">${senderName}</div>` : ''}
                            <div class="message-bubble">
                                <div class="message-text">${message.text || ''}</div>
                                <div class="message-time">${time}</div>
                            </div>
                        </div>
                        ${!isLeft ? `<div class="message-avatar">${initials}</div>` : ''}
                    </div>
                `);
          });

          // Scroll to bottom
          setTimeout(() => {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
          }, 100);

        }, error => {
          console.error('Error loading messages:', error);
          showErrorState(error.message);
        });
    }

    /* ---------- Button Event Listeners ---------- */
    document.addEventListener('DOMContentLoaded', function() {
      // Refresh button
      document.getElementById('refreshChat').addEventListener('click', function() {
        if (!currentChatId) {
          showToast('Please select a chat first', 'warning');
          return;
        }

        showToast('Refreshing messages...', 'info');
        reloadCurrentChat();

        // Add rotation animation to refresh button
        const icon = this.querySelector('i');
        icon.style.transition = 'transform 0.5s ease';
        icon.style.transform = 'rotate(360deg)';

        setTimeout(() => {
          icon.style.transform = 'rotate(0deg)';
        }, 500);
      });

      // Info button
      document.getElementById('chatInfo').addEventListener('click', function() {
        if (!currentChatId || !currentChatData) {
          showToast('Please select a chat first', 'warning');
          return;
        }

        updateChatInfoModal();
        const modal = new bootstrap.Modal(document.getElementById('chatInfoModal'));
        modal.show();
      });

      // Search functionality
      document.getElementById('chatSearch').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase().trim();
        const chatItems = document.querySelectorAll('.chat-item');
        let visibleCount = 0;

        chatItems.forEach(item => {
          const text = item.textContent.toLowerCase();
          if (searchTerm === '' || text.includes(searchTerm)) {
            item.style.display = 'flex';
            visibleCount++;
          } else {
            item.style.display = 'none';
          }
        });

        // Show/hide empty state
        const emptyState = document.getElementById('emptyChatList');
        if (searchTerm !== '' && visibleCount === 0) {
          emptyState.classList.remove('d-none');
          emptyState.querySelector('.empty-title').textContent = 'No matches found';
          emptyState.querySelector('.empty-text').textContent = 'No conversations match your search';
        } else if (searchTerm === '' && chatItems.length === 0) {
          emptyState.classList.remove('d-none');
        } else {
          emptyState.classList.add('d-none');
        }
      });
    });

    /* ---------- Toast Notification ---------- */
    function showToast(message, type = 'info') {
      // Remove existing toasts
      const existingToasts = document.querySelectorAll('.toast-container');
      existingToasts.forEach(toast => toast.remove());

      // Create toast container
      const toastContainer = document.createElement('div');
      toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';

      // Create toast
      const toast = document.createElement('div');
      toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0`;
      toast.setAttribute('role', 'alert');
      toast.setAttribute('aria-live', 'assertive');
      toast.setAttribute('aria-atomic', 'true');

      toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

      toastContainer.appendChild(toast);
      document.body.appendChild(toastContainer);

      // Initialize and show toast
      const bsToast = new bootstrap.Toast(toast, {
        delay: 3000
      });
      bsToast.show();
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      // Any initial setup if needed
    });
  </script>
@endsection
