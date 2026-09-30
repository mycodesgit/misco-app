@extends('layouts.app')

@section('title')
    All Tickets
@endsection

@section('body')
    <div class="row">
        <div class="col-12">
            <div class="mb-4">

                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div class="d-flex align-items-start gap-2">
                        <a href="{{ url('/requests') }}" class="btn btn-light btn-sm" title="Back to Requests" aria-label="Back to Requests">
                            <i class="ti ti-arrow-left"></i>
                        </a>
                        <div>
                            <h1 class="h4 fw-bold mb-1">
                                #TK-8942: Database Connection Timeout
                            </h1>
                            <p class="text-muted small mb-0">Submitted by <strong>Maria Santos</strong> &bull; Registrar Department</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-danger rounded-pill px-3 py-2"><i class="ti ti-alert-triangle me-1"></i> High Priority</span>
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
                                            <span class="fw-semibold small"><i class="ti ti-server me-1"></i> Infrastructure</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted small d-block">Submitted Date</span>
                                            <span class="fw-semibold small">Sep 29, 2026 10:15 AM</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Detailed Complaint Description -->
                                <div class="mb-4">
                                    <label class="form-label text-muted small fw-bold">Issue Description:</label>
                                    <div class="p-3 card-body-content-bg-color rounded border">
                                        <p class="mb-0">
                                            "Whenever I attempt to click 'Generate PDF Report' inside the student grading module, the page loads for 30 seconds and throws a 500 MySQL connection timeout error. This is blocking our deadline processing."
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
                                                    error_screen_log.png
                                                </a>
                                                <span class="badge text-muted border fw-normal">PNG &bull; 340 KB</span>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="d-flex align-items-center gap-1 pe-1">
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal" class="btn btn-sm btn-icon btn-light border text-secondary" title="View Image">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                <a href="#" class="btn btn-sm btn-icon btn-primary" title="Download File">
                                                    <i class="ti ti-download"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- IT Management Action -->
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <button class="btn btn-outline-success">
                                        <i class="ti ti-check me-1"></i> Mark as Resolved & Close
                                    </button>
                                    <button class="btn btn-secondary">
                                        <i class="ti ti-progress me-1"></i> Mark as 'In Progress / Working'
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Pure Message Chat Area (col-md-7) -->
                    <div class="col-md-7">
                        <div class="card h-100 d-flex flex-column">

                            <!-- Chat Box Header -->
                            <div class="card-header p-3 border-bottom d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-warning text-white fw-bold me-2">MS</div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">Live Ticket Chat</h6>
                                        <small class="text-muted">Chatting with Requester: <strong>Maria Santos</strong></small>
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark border"><i class="ti ti-circle-filled text-success me-1"></i> Chat Active</span>
                            </div>

                            <!-- Chat Messages Body -->
                            <div class="card-body p-3 flex-grow-1 overflow-y-auto ticket-chat-body">

                                <div class="text-center my-2">
                                    <small class="text-muted border px-2 py-1 rounded">
                                        Chat opened for Ticket #TK-8942
                                    </small>
                                </div>

                                <!-- Requester Message -->
                                <div class="d-flex mb-3 align-items-start">
                                    <div class="avatar-circle bg-warning text-white fw-bold me-2 flex-shrink-0">MS</div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="fw-semibold small">Maria Santos</span>
                                            <small class="text-muted">10:15 AM</small>
                                        </div>
                                        <div class="chat-bubble incoming-bubble p-3 rounded-3">
                                            <p class="mb-0">Hi IT Support! I attached the error screenshot on the left side of this page. Let me know if you need more details.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- IT Staff Message -->
                                <div class="d-flex mb-3 align-items-start justify-content-end">
                                    <div class="text-end">
                                        <div class="d-flex align-items-center justify-content-end gap-2 mb-1">
                                            <small class="text-muted">10:20 AM</small>
                                            <span class="fw-semibold small">You (IT Support)</span>
                                        </div>
                                        <div class="chat-bubble outgoing-bubble p-3 rounded-3 ms-auto">
                                            <p class="mb-0">Hello Maria! Received your request. We are inspecting the MariaDB query execution logs now.</p>
                                        </div>
                                    </div>
                                    <div class="avatar-circle bg-secondary text-white fw-bold ms-2 flex-shrink-0">IT</div>
                                </div>

                            </div>

                            <!-- Chat Input Footer (PURE TEXT MESSAGES ONLY) -->
                            <div class="card-footer bg-transparent p-3 border-top">
                                <form action="#" method="POST" id="chatOnlyForm">
                                    @csrf
                                    <div class="position-relative d-flex align-items-center">
                                        <input type="text" class="form-control custom-input-btn" placeholder="Type a text message to requester..." required>
                                        <button class="btn btn-success btn-inside" type="submit">
                                            <i class="ti ti-send me-1"></i> Send
                                        </button>
                                    </div>
                                    <small class="text-muted mt-1 d-block" style="font-size: 0.72rem;">
                                        <i class="ti ti-info-circle me-1"></i> Direct chat supports pure text messages only. Photo attachments are managed on creation.
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
                    <h6 class="modal-title fw-bold"><i class="ti ti-photo me-1"></i> Attached Issue Screenshot</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-2 bg-dark">
                    <img src="https://via.placeholder.com/800x500/1a222c/ffffff?text=Database+Error+Screenshot+Proof" class="img-fluid rounded" alt="Full Preview">
                </div>
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
            font-size: 0.9rem;
            max-width: 85%;
        }

        .incoming-bubble {
            background-color: var(--bs-light, #f8f9fa);
            border: 1px solid var(--bs-border-color, #dee2e6);
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

        [data-bs-theme="dark"] .outgoing-bubble {
            background-color: #4a4d50;
            border: 1px solid #6f757b;
            color: #fff;
        }
        /* Add padding on the right so the typing text doesn't slide under the button */
        .custom-input-btn {
            padding-right: 95px;
            border-radius: 8px; /* Optional: adjusts rounding for a modern feel */
        }

        /* Absolute position for the button inside the input */
        .btn-inside {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            border-radius: 6px;
            padding: 6px 14px;
            z-index: 5;
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
