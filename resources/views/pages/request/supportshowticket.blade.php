@extends('layouts.app')

@section('title')
    {{ auth()->user()->pageTitle('Ticketing Request Details') }}
@endsection

@section('body')
    <div class="row">
        <div class="col-12">
            <div class="mb-4">

                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div class="d-flex align-items-start gap-2">
                        @if(Auth::guard('web')->user()->role != 'Requester')
                            <a href="{{ route('tickets.index') }}" class="btn btn-light btn-sm" title="Back to Requests" aria-label="Back to Requests">
                                <i class="ti ti-arrow-left"></i>
                            </a>
                        @else
                            <a href="{{ route('ticketsrequester.index') }}" class="btn btn-light btn-sm" title="Back to Requests" aria-label="Back to Requests">
                                <i class="ti ti-arrow-left"></i>
                            </a>
                        @endif
                        <div>
                            <h1 class="h4 fw-bold mb-1">
                                <span class="text-primary">{{ $ticket->ticket_number }}</span> : &nbsp;
                                <span>{{ $ticket->category->ticketcatname }}</span>
                                <span>( {{ $ticket->subcategory->ticketsubcatname }} )</span>
                            </h1>
                            <p class="text-muted small mb-0">Submitted by <strong>{{ $ticket->requester->fname }} {{ $ticket->requester->lname }}</strong> &bull; {{ $ticket->requesteroffice->office_abbr }}</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @php
                            $priorityConfig = match(strtolower($ticket->priority)) {
                                'low' => [
                                    'class' => 'bg-info text-dark',
                                    'icon'  => 'ti-info-circle'
                                ],
                                'medium' => [
                                    'class' => 'bg-primary',
                                    'icon'  => 'ti-adjustments'
                                ],
                                'high' => [
                                    'class' => 'bg-warning text-dark',
                                    'icon'  => 'ti-alert-circle'
                                ],
                                'urgent' => [
                                    'class' => 'bg-danger',
                                    'icon'  => 'ti-alert-triangle'
                                ],
                                default => [
                                    'class' => 'bg-secondary',
                                    'icon'  => 'ti-help-circle'
                                ]
                            };
                        @endphp
                        <span class="badge {{ $priorityConfig['class'] }} rounded-pill px-3 py-2">
                            <i class="ti {{ $priorityConfig['icon'] }} me-1"></i> {{ $ticket->priority }}
                        </span>
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2"><i class="ti ti-clock me-1"></i> Pending</span>
                    </div>
                </div>

                <div class="row g-3 mb-5">
                    <!-- LEFT COLUMN: Problem Details & Attached Screenshot (col-md-5) -->
                    <div class="col-md-5">
                        <div class="card h-100">
                            <div class="card-header p-3 border-bottom d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <h6 class="mb-0 fw-bold"><i class="ti ti-file-description me-1 text-primary"></i> Ticket Details</h6>
                                </div>
                                <button class="btn btn-sm btn-danger badge">
                                    <i class="ti ti-lock-open me-1"></i> Close Ticket
                                </button>
                            </div>
                            <div class="card-body">
                                <!-- Category & Date Meta -->
                                <div class="mb-3 p-2 card-body-content-bg-color rounded border">
                                    <div class="row text-center">
                                        <div class="col-6 border-end">
                                            <span class="text-muted small d-block">Category</span>
                                            <span class="fw-semibold small"><i class="ti ti-server me-1"></i> {{ $ticket->category->ticketcatname }}</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted small d-block">Submitted Date</span>
                                            <span class="fw-semibold small">
                                                {{ $ticket->created_at?->format('M d, Y h:i A') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Detailed Complaint Description -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Issue Description:</label>
                                    <div class="p-3 card-body-content-bg-color shadow-sm rounded border">
                                        <p class="mb-0">
                                            " {{ $ticket->issue_description }} "
                                        </p>
                                    </div>
                                </div>

                                <!-- Photo Attached During Ticket Creation -->
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold d-block mb-2">
                                        <i class="ti ti-paperclip me-1 text-primary"></i> Attached Proof / Screenshot
                                    </label>

                                    <div class="attachment-card border rounded-3 p-2 shadow-sm bg-body">
                                        <div class="d-flex align-items-center gap-3">
                                            <!-- Image Thumbnail / Preview Trigger -->
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal" class="attachment-thumb-wrapper flex-shrink-0 text-decoration-none">
                                                <div class="attachment-thumb rounded-2 d-flex align-items-center justify-content-center bg-light border">
                                                    <i class="ti ti-photo fs-3 text-secondary attachment-icon"></i>
                                                </div>
                                            </a>

                                            <!-- File Info -->
                                            <div class="flex-grow-1 min-w-0">
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal" class="fw-semibold text-decoration-none d-block text-truncate small mb-1">
                                                    {{ basename($ticket->attachment) }}
                                                </a>
                                                <span class="badge text-muted border fw-normal">
                                                    {{ strtoupper(pathinfo($ticket->attachment, PATHINFO_EXTENSION)) }} Attachment
                                                </span>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="d-flex align-items-center gap-1 pe-1">
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal" class="btn btn-sm btn-light border text-secondary" title="View Image">
                                                    <i class="ti ti-eye"></i> View Attachment
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- IT Management Action -->
                                <hr>
                                @if(Auth::guard('web')->user()->role != 'Requester')
                                <div class="d-flex justify-content-between">
                                    <button class="btn btn-outline-success">
                                        <i class="ti ti-check me-1"></i> Mark as Resolved & Close
                                    </button>
                                    <button class="btn btn-info">
                                        <i class="ti ti-progress me-1"></i> Mark as 'In Progress / Working'
                                    </button>
                                </div>
                                @endif

                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Pure Message Chat Area (col-md-7) -->
                    <div class="col-md-7">

                        <div class="card h-100 d-flex flex-column">

                            <!-- Chat Box Header -->
                            <div class="card-header p-3 border-bottom d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-warning text-white fw-bold me-2">
                                        {{ strtoupper(substr($ticket->requester?->fname ?? 'U', 0, 1) . substr($ticket->requester?->lname ?? 'N', 0, 1)) }}
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">Live Ticket Chat</h6>
                                        <small class="text-muted">
                                            Chatting with:
                                            <strong>
                                                @if(auth()->id() === $ticket->user_id)
                                                    {{-- Logged in user is the Requester -> Show Assigned Support or Default --}}
                                                    {{ $ticket->supportoffice->office_abbr ?? 'Support Team' }} Support Personnel
                                                @else
                                                    {{-- Logged in user is IT Support -> Show Requester --}}
                                                    {{ $ticket->requester?->fname ?? 'User' }} {{ $ticket->requester?->lname ?? '' }}
                                                @endif
                                            </strong>
                                        </small>
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark border"><i class="ti ti-circle-filled text-success me-1"></i> Chat Active</span>
                            </div>

                            <!-- Chat Messages Body -->
                            <div class="card-body p-3 flex-grow-1 overflow-y-auto ticket-chat-body" id="chatBox">
                                <div class="text-center my-2">
                                    <small class="text-muted border px-2 py-1 rounded">
                                        Chat opened for Ticket #{{ $ticket->ticket_number ?? $ticket->id }}
                                    </small>
                                </div>

                                {{-- Messages will dynamically render here via JS --}}
                            </div>

                            <!-- Chat Input Footer -->
                            <div class="card-footer bg-transparent p-3 border-top">
                                <!-- Selected File Preview Container -->
                                <div id="attachmentPreview" class="d-none mb-2 p-2 bg-light rounded border align-items-center justify-content-between">
                                    <div class="d-flex align-items-center text-truncate me-2">
                                        <i class="ti ti-photo me-2 text-primary fs-5"></i>
                                        <span id="fileNameDisplay" class="small fw-semibold text-truncate"></span>
                                    </div>
                                    <button type="button" class="btn-close btn-sm ms-2" id="removeAttachmentBtn" aria-label="Remove attachment"></button>
                                </div>
                                <form method="POST" id="chatOnlyForm" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" id="ticket_id" value="{{ $ticket->id }}">
                                    <input type="file" id="chatAttachment" name="attachment" accept="image/*" class="d-none">

                                    <div class="position-relative d-flex align-items-center">
                                        <input type="text" id="chatMessageInput" class="form-control custom-input-btn" placeholder="Type a text message to requester..." autocomplete="off">

                                        <!-- Action buttons container placed inside the input -->
                                        <div class="chat-input-actions d-flex align-items-center gap-1">
                                            <!-- Attach Icon -->
                                            <label for="chatAttachment" class="btn btn-link text-secondary p-1 mb-0 border-0" title="Attach Image">
                                                <i class="ti ti-paperclip fs-5"></i>
                                            </label>

                                            <!-- Send Button -->
                                            <button class="btn btn-success btn-inside-send" type="submit" id="sendChatBtn">
                                                <i class="ti ti-send me-1"></i> Send
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted mt-1 d-block" style="font-size: 0.72rem;">
                                        <i class="ti ti-info-circle me-1"></i> Supports text & image attachments (PNG, JPG, JPEG up to 5MB).
                                    </small>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image View Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">
                        <i class="ti ti-photo me-1"></i> Attached Issue Screenshot
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-2">
                    @if(!empty($ticket->attachment))
                        <img src="{{ asset($ticket->attachment) }}"
                            class="img-fluid rounded"
                            alt="{{ basename($ticket->attachment) }}"
                            style="max-height: 75vh; object-fit: contain;">
                    @else
                        <div class="text-white py-5">
                            <i class="ti ti-photo-off fs-1 d-block mb-2 text-muted"></i>
                            <p class="mb-0 text-muted">No attachment available for this ticket.</p>
                        </div>
                    @endif
                </div>
                {{-- @if(!empty($ticket->attachment))
                    <div class="modal-footer py-2">
                        <a href="{{ asset($ticket->attachment) }}"
                        target="_blank"
                        download="{{ basename($ticket->attachment) }}"
                        class="btn btn-sm btn-primary">
                            <i class="ti ti-download me-1"></i> Download File
                        </a>
                    </div>
                @endif --}}
            </div>
        </div>
    </div>

    <style>
        /* Custom Attachment Card Styles */
        .attachment-card {
            transition: all 0.2s ease-in-out;
        }

        .attachment-card:hover {
            border-color: #cbd5e1 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
        }

        .attachment-thumb {
            width: 48px;
            height: 48px;
            transition: background-color 0.2s ease;
        }

        .attachment-thumb-wrapper:hover .attachment-thumb {
            background-color: #e2e8f0 !important;
        }

        .attachment-thumb-wrapper:hover .attachment-icon {
            color: #0f172a !important;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .avatar-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }

        .ticket-chat-body {
            max-height: 420px;
            min-height: 350px;
        }

        .chat-bubble {
            max-width: 100%; /* Prevents bubbles from taking the entire screen width */
            width: fit-content; /* Shrinks to text size when message is short */
            word-wrap: break-word;
            position: relative;
        }

        /* Base style for both tails */
        .chat-bubble::before {
            content: '';
            position: absolute;
            top: 12px; /* Adjust vertical placement of the tail */
            width: 0;
            height: 0;
            border: 7px solid transparent;
        }

        .incoming-bubble {
            background-color: var(--bs-light, #f8f9fa);
            border: 1px solid var(--bs-border-color, #dee2e6);
        }

        .incoming-bubble::before {
            left: -14px;
            border-right-color: var(--bs-border-color, #dee2e6); /* Border color of tail */
        }

        .incoming-bubble::after {
            content: '';
            position: absolute;
            top: 12px;
            left: -12px;
            width: 0;
            height: 0;
            border: 7px solid transparent;
            border-right-color: var(--bs-light, #f8f9fa); /* Fill color inside tail */
        }

        [data-bs-theme="dark"] .incoming-bubble {
            background-color: #818a94;
            border: 1px solid var(--bs-border-color, #dee2e6);
        }

        .outgoing-bubble {
            background-color: #ebf4ff;
            border: 1px solid var(--bs-border-color, #dee2e6);
            color: #000;

        }

        .outgoing-bubble::before {
            right: -14px;
            border-left-color: var(--bs-border-color, #dee2e6); /* Border color of tail */
        }

        .outgoing-bubble::after {
            content: '';
            position: absolute;
            top: 12px;
            right: -12px;
            width: 0;
            height: 0;
            border: 7px solid transparent;
            border-left-color: #ebf4ff; /* Fill color inside tail */
        }

        [data-bs-theme="dark"] .outgoing-bubble {
            background-color: #4a4d50;
            border: 1px solid #6f757b;
            color: #fff;
        }
        /* Add padding on the right so the typing text doesn't slide under the button */
        /* Increased padding-right to accommodate both the clip icon and send button */
        .custom-input-btn {
            padding-right: 130px;
            border-radius: 8px;
        }

        /* Absolute position for the actions container inside the input */
        .chat-input-actions {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
        }

        .btn-inside-send {
            border-radius: 6px;
            padding: 6px 14px;
        }

        /* Optional hover state for the attach icon inside input */
        .chat-input-actions label:hover {
            color: #198754 !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var chatBox = document.querySelector('.ticket-chat-body');
            if (chatBox) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        });
    </script>
@endsection
