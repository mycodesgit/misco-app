<script>
    $(document).ready(function () {
    const ticketId = $('#ticket_id').val();
    const fetchUrl = "{{ route('ticket.chat.fetch', ['ticketId' => ':ticketId']) }}".replace(':ticketId', ticketId);
    const sendUrl = "{{ route('ticket.chat.send') }}";

    // Track scroll position to prevent interrupting user if they scrolled up to read history
    let userHasScrolledUp = false;

    $('#chatBox').on('scroll', function() {
        const chatBox = this;
        // Check if user is near the bottom (within 50px threshold)
        const isAtBottom = chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight < 50;
        userHasScrolledUp = !isAtBottom;
    });

    // Initial load
    loadMessages(true);

    // Auto-poll for new messages every 3 seconds
    setInterval(function() {
        loadMessages(false);
    }, 3000);

    // Load messages from controller
    function loadMessages(isInitialLoad = false) {
        $.ajax({
            url: fetchUrl,
            type: 'GET',
            success: function (response) {
                if (response.success) {
                    const chatBox = $('#chatBox');
                    
                    // Reset header line
                    chatBox.html(`
                        <div class="text-center my-2">
                            <small class="text-muted border px-2 py-1 rounded">
                                Chat opened for Ticket #{{ $ticket->ticket_number ?? $ticket->id }}
                            </small>
                        </div>
                    `);

                    response.messages.forEach(function (msg) {
                        appendMessage(msg);
                    });

                    // Scroll to bottom on initial load OR if user hasn't scrolled up manually
                    if (isInitialLoad || !userHasScrolledUp) {
                        scrollToBottom();
                    }
                }
            }
        });
    }

    // Scroll container to bottom
    function scrollToBottom() {
        const chatBox = document.getElementById('chatBox');
        if (chatBox) {
            // Using setTimeout ensures DOM rendering is finished before measuring scrollHeight
            setTimeout(function() {
                chatBox.scrollTop = chatBox.scrollHeight;
            }, 50);
        }
    }

    // Handle send form submit
    $('#chatOnlyForm').on('submit', function (e) {
        e.preventDefault();

        const messageInput = $('#chatMessageInput');
        const messageText = messageInput.val().trim();

        if (messageText === '') return;

        $('#sendChatBtn').prop('disabled', true);

        $.ajax({
            url: sendUrl,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                ticket_id: ticketId,
                message: messageText
            },
            success: function (response) {
                if (response.success) {
                    messageInput.val('');
                    appendMessage(response.data);
                    userHasScrolledUp = false; // Reset user scroll state on new message send
                    scrollToBottom();
                }
            },
            complete: function () {
                $('#sendChatBtn').prop('disabled', false);
            }
        });
    });

    function appendMessage(msg) {
        let html = '';
        if (msg.is_me) {
            html = `
                <div class="d-flex mb-3 align-items-start justify-content-end">
                    <div class="text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2 mb-1">
                            <small class="text-muted">${msg.time}</small>
                            <span class="fw-semibold small">You</span>
                        </div>
                        <div class="chat-bubble outgoing-bubble p-3 rounded-3 ms-auto">
                            <p class="mb-0">${escapeHtml(msg.message)}</p>
                        </div>
                    </div>
                    <div class="avatar-circle bg-secondary text-white fw-bold ms-2 flex-shrink-0">
                        ${getInitials(msg.sender_name)}
                    </div>
                </div>`;
        } else {
            html = `
                <div class="d-flex mb-3 align-items-start">
                    <div class="avatar-circle bg-warning text-white fw-bold me-2 flex-shrink-0">
                        ${getInitials(msg.sender_name)}
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fw-semibold small">${escapeHtml(msg.sender_name)}</span>
                            <small class="text-muted">${msg.time}</small>
                        </div>
                        <div class="chat-bubble incoming-bubble p-3 rounded-3">
                            <p class="mb-0">${escapeHtml(msg.message)}</p>
                        </div>
                    </div>
                </div>`;
        }
        $('#chatBox').append(html);
    }

    function getInitials(name) {
        if (!name) return 'U';
        const parts = name.split(' ');
        return parts.length >= 2 ? (parts[0][0] + parts[1][0]).toUpperCase() : name.substring(0, 2).toUpperCase();
    }

    function escapeHtml(text) {
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});
</script>