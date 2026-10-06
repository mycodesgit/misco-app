<script>
    $(document).ready(function () {
        const ticketId = $('#ticket_id').val();
        const currentUserId = {{ auth()->id() }};
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
        // setInterval(function() {
        //     loadMessages(false);
        // }, 3000);

        if (typeof Echo !== 'undefined') {
            Echo.private(`ticket.${ticketId}`)
                .listen('MessageSent', (e) => {
                    // Skip if sent by the logged-in user (already handled by AJAX submit response)
                    if (e.sender_id == currentUserId) return;

                    const incomingMessage = {
                        id: e.id,
                        message: e.message,
                        attachment: e.attachment,
                        sender_name: e.sender_name,
                        time: e.time,
                        is_me: false
                    };

                    appendMessage(incomingMessage);

                    if (!userHasScrolledUp) {
                        scrollToBottom();
                    }
                })
                .listen('TicketStatusUpdated', (e) => {
                    // Other browser changed status — update left card + badge live
                    if (e.status) {
                        updateTicketUI(e.status, e.ticket_id || ticketId);
                    }
                });
        }

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

    $(document).on('click', '.ticket-status-btn', function(e) {
        e.preventDefault();

        var ticketId = $(this).data('id');
        var action = $(this).data('action'); // 'resolved', 'in_progress', or 'cancelled'

        // Configure confirmation dialog options per action
        var confirmConfig = {
            'resolved': {
                title: 'Mark as Resolved?',
                text: 'This will set the status to Resolved and log the completion timestamp.',
                confirmText: 'Yes, resolve it!',
                confirmColor: '#28a745'
            },
            'in_progress': {
                title: 'Mark as In Progress?',
                text: 'This will set the status to In Progress and log the start time.',
                confirmText: 'Yes, start working!',
                confirmColor: '#17a2b8'
            },
            'cancelled': {
                title: 'Close / Cancel Ticket?',
                text: 'Are you sure you want to close this ticket?',
                confirmText: 'Yes, close ticket!',
                confirmColor: '#dc3545'
            }
        };

        var config = confirmConfig[action] || {
            title: 'Update Status?',
            text: 'Are you sure you want to update this ticket?',
            confirmText: 'Yes, update!',
            confirmColor: '#3085d6'
        };

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        Swal.fire({
            title: config.title,
            text: config.text,
            icon: action === 'cancelled' ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: config.confirmColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: config.confirmText
        }).then((result) => {
            if (result.isConfirmed) {
                var url = ticketStatusUpdateRoute.replace(':id', ticketId);

                $.ajax({
                    type: "POST",
                    url: url,
                    data: {
                        status: action
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Updated!',
                                text: response.message || 'Ticket status updated successfully.',
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            // Update left card + badge in place (no reload)
                            if (response.ticket && response.ticket.status) {
                                updateTicketUI(response.ticket.status, ticketId);
                            }
                        } else {
                            Swal.fire('Error!', response.message || 'Something went wrong.', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', 'An error occurred while updating the ticket.', 'error');
                    }
                });
            }
        });
    });

    // Mirror of supportshowticket.blade.php left-card if/else — updates in place, no reload
    var isSupportUser = {{ Auth::guard('web')->user()->role != 'Requester' ? 'true' : 'false' }};

    function updateTicketUI(status, ticketId) {
        var lower = (status || '').toLowerCase();

        // 1. Update top status badge (#ticket-status-badge)
        var badgeConfig = {
            'pending':     { cls: 'bg-warning text-dark', icon: 'ti-clock' },
            'in progress': { cls: 'bg-info',              icon: 'ti-progress' },
            'resolved':    { cls: 'bg-success',            icon: 'ti-circle-check' },
            'cancelled':   { cls: 'bg-danger',             icon: 'ti-circle-x' }
        };
        var badge = badgeConfig[lower] || { cls: 'bg-secondary', icon: 'ti-help-circle' };
        var $badge = $('#ticket-status-badge');
        if ($badge.length) {
            $badge.removeClass('bg-warning text-dark bg-info bg-success bg-danger bg-secondary')
                .addClass(badge.cls)
                .html('<i class="ti ' + badge.icon + ' me-1"></i> ' + escapeHtmlTicketStatus(status));
        }

        // 2. Re-render left-card action container (#ticket-action-container)
        var $container = $('#ticket-action-container');
        if ($container.length) {
            $container.html(renderActionButtons(lower, ticketId));
        }
    }

    function escapeHtmlTicketStatus(text) {
        return String(text == null ? '' : text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function renderActionButtons(lower, ticketId) {
        if (isSupportUser) {
            if (lower === 'cancelled') {
                return '<div class="alert alert-danger w-100 mb-0 d-flex align-items-center py-2 px-3">' +
                    '<i class="ti ti-lock me-2 fs-5"></i>' +
                    '<span>This ticket has been <strong>Closed / Cancelled</strong>.</span></div>';
            }
            if (lower === 'resolved') {
                return '<div class="alert alert-success w-100 mb-0 d-flex align-items-center" role="alert">' +
                    '<i class="ti ti-circle-check fs-4 me-2"></i>' +
                    '<div>This ticket has been marked as <strong>Resolved</strong>.</div></div>';
            }
            if (lower === 'in progress') {
                return '<button class="btn btn-outline-success ticket-status-btn" data-id="' + ticketId + '" data-action="resolved">' +
                        '<i class="ti ti-check me-1"></i> Mark as Resolved</button>' +
                    '<button class="btn btn-outline-danger ticket-status-btn" data-id="' + ticketId + '" data-action="cancelled">' +
                        '<i class="ti ti-lock me-1"></i> Close Ticket</button>';
            }
            // Default / Pending
            return '<div class="d-flex justify-content-between align-items-center w-100">' +
                    '<button class="btn btn-outline-danger ticket-status-btn" data-id="' + ticketId + '" data-action="cancelled">' +
                        '<i class="ti ti-lock me-1"></i> Close Ticket</button>' +
                    '<div class="d-flex gap-2">' +
                        '<button class="btn btn-success ticket-status-btn" data-id="' + ticketId + '" data-action="resolved">' +
                            '<i class="ti ti-check me-1"></i> Mark as Resolved</button>' +
                        '<button class="btn btn-info ticket-status-btn" data-id="' + ticketId + '" data-action="in_progress">' +
                            '<i class="ti ti-progress me-1"></i> Mark as In Progress</button>' +
                    '</div></div>';
        }

        // Requester view (alerts only, mirrors Blade)
        if (lower === 'cancelled') {
            return '<div class="alert alert-danger w-100 mb-0 d-flex align-items-center py-2 px-3">' +
                '<i class="ti ti-lock me-2 fs-5"></i>' +
                '<span>This ticket has been <strong>Closed / Cancelled</strong>.</span></div>';
        }
        if (lower === 'resolved') {
            return '<div class="alert alert-success w-100 mb-0 d-flex align-items-center" role="alert">' +
                '<i class="ti ti-circle-check fs-4 me-2"></i>' +
                '<div>This ticket has been marked as <strong>Resolved</strong>.</div></div>';
        }
        if (lower === 'in progress') {
            return '<div class="alert alert-info w-100 mb-0 d-flex align-items-center" role="alert">' +
                '<i class="ti ti-circle-check fs-4 me-2"></i>' +
                '<div>This ticket has been marked as <strong>In Progress/Working on it</strong>.</div></div>';
        }
        return '';
    }
</script>
