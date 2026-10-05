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
        // Handle File Attachment Selection Preview
        $('#chatAttachment').on('change', function () {
            const file = this.files[0];
            if (file) {
                $('#fileNameDisplay').text(file.name);
                $('#attachmentPreview').removeClass('d-none').addClass('d-flex');
            }
        });

        // Handle File Attachment Removal
        $('#removeAttachmentBtn').on('click', function () {
            $('#chatAttachment').val('');
            $('#attachmentPreview').addClass('d-none').removeClass('d-flex');
        });


        // Handle send form submit
        $('#chatOnlyForm').on('submit', function (e) {
            e.preventDefault();

            const messageInput = $('#chatMessageInput');
            const fileInput = $('#chatAttachment')[0];

            // Prevent submission if both inputs are completely empty
            if (messageInput.val().trim() === '' && fileInput.files.length === 0) {
                alert('Please type a message or select an image attachment.');
                return;
            }

            // Explicitly build FormData to prevent missing name attributes
            const formData = new FormData();
            formData.append('_token', $('input[name="_token"]').val() || $('meta[name="csrf-token"]').attr('content'));
            formData.append('ticket_id', ticketId);
            formData.append('message', messageInput.val());

            if (fileInput.files.length > 0) {
                formData.append('attachment', fileInput.files[0]);
            }

            $('#sendChatBtn').prop('disabled', true);

            $.ajax({
                url: sendUrl,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
                },
                success: function (response) {
                    if (response.success) {
                        messageInput.val('');
                        $('#chatAttachment').val('');
                        $('#attachmentPreview').addClass('d-none').removeClass('d-flex');
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
            let attachmentHtml = '';
            if (msg.attachment) {
                attachmentHtml = `
                    <div class="mt-2">
                        <a href="${msg.attachment}" target="_blank">
                            <img src="${msg.attachment}" class="img-fluid rounded border" style="max-height: 200px; object-fit: cover;" alt="Attachment">
                        </a>
                    </div>`;
            }
            let html = '';
            if (msg.is_me) {
                html = `
                    <div class="d-flex mb-3 align-items-start justify-content-end">
                        <div class="d-flex flex-column align-items-end min-w-0">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <small class="text-muted">${msg.time}</small>
                                <span class="fw-semibold small">You</span>
                            </div>
                            <div class="chat-bubble outgoing-bubble p-3 rounded-3">
                                ${msg.message ? `<p class="mb-0 text-break">${escapeHtml(msg.message)}</p>` : ''}${attachmentHtml}
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
                        <div class="d-flex flex-column align-items-start min-w-0">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="fw-semibold small">${escapeHtml(msg.sender_name)}</span>
                                <small class="text-muted">${msg.time}</small>
                            </div>
                            <div class="chat-bubble shadow-sm incoming-bubble p-3 rounded-3">
                                ${msg.message ?`<p class="mb-0 text-break">${escapeHtml(msg.message)}</p>` : ''}${attachmentHtml}
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
