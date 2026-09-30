@extends('layouts.app')

@section('title')
    Ticket #TK-8942 - Details & Chat
@endsection

@section('body')
    <div class="row">
        <div class="col-12">
            <div class="mb-4">
                
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <a href="{{ url('/requests') }}" class="btn btn-outline-secondary btn-sm mb-2">
                            <i class="ti ti-arrow-left me-1"></i> Back to Requests
                        </a>
                        <h1 class="h4 fw-bold mb-1">
                            #TK-8942: Database Connection Timeout
                        </h1>
                        <p class="text-muted small mb-0">Submitted by <strong>Maria Santos</strong> &bull; Registrar Department</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-danger rounded-pill px-3 py-2"><i class="ti ti-alert-triangle me-1"></i> High Priority</span>
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2"><i class="ti ti-clock me-1"></i> Pending</span>
                    </div>
                </div>

                <div class="row g-3">
                    
                    <!-- LEFT COLUMN: Problem Details & Attached Screenshot (col-md-5) -->
                    <div class="col-md-5">
                        <div class="card h-100">
                            <div class="card-header bg-transparent p-3 border-bottom">
                                <h6 class="mb-0 fw-bold"><i class="ti ti-file-description me-1 text-primary"></i> Complaint Details</h6>
                            </div>
                            <div class="card-body p-3">
                                
                                <!-- Category & Date Meta -->
                                <div class="mb-3 p-2 bg-light rounded border">
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
                                    <div class="p-3 bg-light rounded border">
                                        <p class="mb-0 text-dark">
                                            "Whenever I attempt to click 'Generate PDF Report' inside the student grading module, the page loads for 30 seconds and throws a 500 MySQL connection timeout error. This is blocking our deadline processing."
                                        </p>
                                    </div>
                                </div>

                                <!-- Photo Attached During Ticket Creation -->
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold"><i class="ti ti-photo me-1"></i> Attached Proof / Screenshot:</label>
                                    <div class="border rounded p-2 text-center bg-light">
                                        <!-- Attached Image Preview -->
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal">
                                            <img src="https://via.placeholder.com/600x350/1a222c/ffffff?text=Database+Error+Screenshot+Proof" 
                                                 class="img-fluid rounded border shadow-sm mb-2" 
                                                 alt="Attached Issue Photo" 
                                                 style="max-height: 220px; object-fit: cover; width: 100%;">
                                        </a>
                                        <div class="d-flex justify-content-between align-items-center px-1">
                                            <small class="text-muted"><i class="ti ti-paperclip me-1"></i> error_screen_log.png (340 KB)</small>
                                            <a href="#" class="btn btn-sm btn-outline-primary py-0 px-2 fs-7">
                                                <i class="ti ti-download me-1"></i> Download
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- IT Management Action -->
                                <hr>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary btn-sm">
                                        <i class="ti ti-progress me-1"></i> Mark as 'In Progress / Working'
                                    </button>
                                    <button class="btn btn-success btn-sm">
                                        <i class="ti ti-check me-1"></i> Mark as Resolved & Close
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Pure Message Chat Area (col-md-7) -->
                    <div class="col-md-7">
                        <div class="card h-100 d-flex flex-column">
                            
                            <!-- Chat Box Header -->
                            <div class="card-header bg-transparent p-3 border-bottom d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary text-white fw-bold me-2">MS</div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">Live Ticket Chat</h6>
                                        <small class="text-muted">Chatting with Requester: <strong>Maria Santos</strong></small>
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark border"><i class="ti ti-lock-open me-1"></i> Chat Active</span>
                            </div>

                            <!-- Chat Messages Body -->
                            <div class="card-body p-3 flex-grow-1 overflow-y-auto ticket-chat-body">
                                
                                <div class="text-center my-2">
                                    <small class="text-muted bg-light border px-2 py-1 rounded">
                                        Chat opened for Ticket #TK-8942
                                    </small>
                                </div>

                                <!-- Requester Message -->
                                <div class="d-flex mb-3 align-items-start">
                                    <div class="avatar-circle bg-primary text-white fw-bold me-2 flex-shrink-0">MS</div>
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
                                            <p class="mb-0 text-white">Hello Maria! Received your request. We are inspecting the MariaDB query execution logs now.</p>
                                        </div>
                                    </div>
                                    <div class="avatar-circle bg-dark text-white fw-bold ms-2 flex-shrink-0">IT</div>
                                </div>

                            </div>

                            <!-- Chat Input Footer (PURE TEXT MESSAGES ONLY) -->
                            <div class="card-footer bg-transparent p-3 border-top">
                                <form action="#" method="POST" id="chatOnlyForm">
                                    @csrf
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Type a text message to requester..." required>
                                        <button class="btn btn-primary px-3" type="submit">
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

        .outgoing-bubble {
            background-color: var(--bs-primary, #0d6efd);
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