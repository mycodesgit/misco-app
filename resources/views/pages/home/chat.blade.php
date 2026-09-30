@extends('layouts.app')

@section('title')
    IT Helpdesk & Ticketing Chat
@endsection

@section('body')
    <div class="row">
        <div class="col-12">
            <div class="mb-4">
                
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1">
                            <i class="ti ti-headset me-1 text-primary"></i> IT Support & Helpdesk
                        </h1>
                        <p class="text-muted small mb-0">Manage active support tickets, communicate with staff, and resolve system issues.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                            <i class="ti ti-filter me-1"></i> Filter Tickets
                        </button>
                        <button class="btn btn-primary btn-sm rounded-pill px-3">
                            <i class="ti ti-plus me-1"></i> Submit New Ticket
                        </button>
                    </div>
                </div>

                <!-- 3-Column Helpdesk Layout -->
                <div class="row g-3">
                    
                    <!-- 1. Ticket Queue List (col-md-3) -->
                    <div class="col-md-3">
                        <div class="card h-100">
                            <!-- Queue Header & Search -->
                            <div class="card-header bg-transparent p-3 border-bottom">
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text bg-transparent text-muted"><i class="ti ti-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Search ticket # or subject...">
                                </div>
                                <div class="btn-group w-100 btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary active">Open</button>
                                    <button type="button" class="btn btn-outline-primary">Pending</button>
                                    <button type="button" class="btn btn-outline-primary">Closed</button>
                                </div>
                            </div>

                            <!-- Tickets List -->
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush ticket-list-scroll">
                                    
                                    <!-- Active Ticket Item -->
                                    <a href="#" class="list-group-item list-group-item-action p-3 active border-bottom">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge bg-danger rounded-pill"><i class="ti ti-alert-triangle me-1"></i> High</span>
                                            <small class="text-muted opacity-75">#TK-8942</small>
                                        </div>
                                        <h6 class="mb-1 text-truncate fw-bold">Database Connection Timeout</h6>
                                        <p class="mb-2 small text-truncate opacity-75">
                                            User unable to access student grading portal.
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center small opacity-75">
                                            <span><i class="ti ti-user me-1"></i> Maria Santos</span>
                                            <small>10m ago</small>
                                        </div>
                                    </a>

                                    <!-- Ticket Item 2 -->
                                    <a href="#" class="list-group-item list-group-item-action p-3 border-bottom">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge bg-warning text-dark rounded-pill">Medium</span>
                                            <small class="text-muted">#TK-8939</small>
                                        </div>
                                        <h6 class="mb-1 text-truncate fw-bold">Printer Driver Installation</h6>
                                        <p class="mb-2 small text-muted text-truncate">
                                            Need network printer setup in Admin Office.
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center small text-muted">
                                            <span><i class="ti ti-user me-1"></i> John Doe</span>
                                            <small>2h ago</small>
                                        </div>
                                    </a>

                                    <!-- Ticket Item 3 -->
                                    <a href="#" class="list-group-item list-group-item-action p-3 border-bottom">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge bg-info rounded-pill">Low</span>
                                            <small class="text-muted">#TK-8921</small>
                                        </div>
                                        <h6 class="mb-1 text-truncate fw-bold">Password Reset Request</h6>
                                        <p class="mb-2 small text-muted text-truncate">
                                            Locked out of portal after multiple attempts.
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center small text-muted">
                                            <span><i class="ti ti-user me-1"></i> Alex Cruz</span>
                                            <small> Yesterday</small>
                                        </div>
                                    </a>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Active Ticket Chat Box (col-md-6) -->
                    <div class="col-md-6">
                        <div class="card h-100 d-flex flex-column">
                            
                            <!-- Chat Box Header -->
                            <div class="card-header bg-transparent p-3 border-bottom d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h6 class="mb-0 fw-bold">#TK-8942: Database Connection Timeout</h6>
                                        <span class="badge bg-success rounded-pill"><i class="ti ti-circle-filled me-1"></i> Open</span>
                                    </div>
                                    <small class="text-muted">
                                        <i class="ti ti-category me-1"></i> Server Infrastructure &bull; Created by <strong>Maria Santos</strong>
                                    </small>
                                </div>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Refresh Feed">
                                        <i class="ti ti-refresh"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Ticket Logs">
                                        <i class="ti ti-history"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Chat Messages Body -->
                            <div class="card-body p-3 flex-grow-1 overflow-y-auto helpdesk-chat-area">
                                
                                <!-- Ticket Opening System Log -->
                                <div class="text-center my-3">
                                    <span class="badge bg-light text-muted border px-3 py-1 fw-normal">
                                        <i class="ti ti-ticket me-1"></i> Ticket submitted on Sep 29, 2026 at 10:15 AM
                                    </span>
                                </div>

                                <!-- User Message (Ticket Creator) -->
                                <div class="d-flex mb-3 align-items-start">
                                    <div class="avatar-circle bg-primary text-white fw-bold me-2 flex-shrink-0">MS</div>
                                    <div class="w-100">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-semibold small">Maria Santos <span class="badge bg-light text-dark border ms-1">Requester</span></span>
                                            <small class="text-muted">10:15 AM</small>
                                        </div>
                                        <div class="chat-bubble incoming-bubble p-3 rounded-3">
                                            <p class="mb-0 text-dark dark-text-light">
                                                Hi IT Support, I am getting a 500 error and database timeout whenever I try to generate the batch report on the grading system.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- System Action Alert -->
                                <div class="text-center my-3">
                                    <small class="text-muted fst-italic">
                                        <i class="ti ti-user-check me-1"></i> Ticket assigned to <strong>System Administrator</strong>
                                    </small>
                                </div>

                                <!-- Staff/Tech Response -->
                                <div class="d-flex mb-3 align-items-start justify-content-end">
                                    <div class="w-100">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-muted">10:22 AM</small>
                                            <span class="fw-semibold small"><span class="badge bg-primary me-1">Staff</span> You</span>
                                        </div>
                                        <div class="chat-bubble outgoing-bubble p-3 rounded-3 ms-auto">
                                            <p class="mb-0 text-white">
                                                Hello Maria, thanks for reporting. We are checking the MariaDB query logs and server status now.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="avatar-circle bg-dark text-white fw-bold ms-2 flex-shrink-0">SA</div>
                                </div>

                                <!-- User Reply -->
                                <div class="d-flex mb-3 align-items-start">
                                    <div class="avatar-circle bg-primary text-white fw-bold me-2 flex-shrink-0">MS</div>
                                    <div class="w-100">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-semibold small">Maria Santos</span>
                                            <small class="text-muted">10:25 AM</small>
                                        </div>
                                        <div class="chat-bubble incoming-bubble p-3 rounded-3">
                                            <p class="mb-0 text-dark dark-text-light">
                                                Here is a screenshot of the error modal for reference.
                                            </p>
                                            <div class="mt-2 p-2 border rounded bg-body d-flex align-items-center gap-2">
                                                <i class="ti ti-file-text fs-3 text-danger"></i>
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <div class="fw-semibold small text-truncate">error_log_screenshot.png</div>
                                                    <small class="text-muted">245 KB</small>
                                                </div>
                                                <a href="#" class="btn btn-sm btn-outline-secondary"><i class="ti ti-download"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Chat Input Footer -->
                            <div class="card-footer bg-transparent p-3 border-top">
                                <form action="#" method="POST" id="ticketChatForm">
                                    @csrf
                                    <div class="mb-2">
                                        <textarea class="form-control" rows="2" placeholder="Type your response or update..." required></textarea>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="tooltip" title="Attach Files">
                                                <i class="ti ti-paperclip"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="tooltip" title="Insert Canned Response">
                                                <i class="ti ti-file-code"></i>
                                            </button>
                                        </div>
                                        <button class="btn btn-primary btn-sm px-3" type="submit">
                                            <i class="ti ti-send me-1"></i> Send Update
                                        </button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>

                    <!-- 3. Ticket Details Panel (col-md-3) -->
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-header bg-transparent p-3 border-bottom">
                                <h6 class="mb-0 fw-bold"><i class="ti ti-info-circle me-1"></i> Ticket Metadata</h6>
                            </div>
                            <div class="card-body p-3">
                                
                                <!-- Quick Actions -->
                                <div class="d-grid gap-2 mb-3">
                                    <button class="btn btn-success btn-sm">
                                        <i class="ti ti-check me-1"></i> Mark as Resolved
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm">
                                        <i class="ti ti-lock me-1"></i> Close Ticket
                                    </button>
                                </div>

                                <hr>

                                <!-- Ticket Details List -->
                                <div class="mb-3">
                                    <label class="text-muted small fw-semibold d-block mb-1">Assigned Agent</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-circle-sm bg-dark text-white fw-bold">SA</div>
                                        <span class="fw-semibold small">System Admin</span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="text-muted small fw-semibold d-block mb-1">Priority Level</label>
                                    <select class="form-select form-select-sm">
                                        <option value="high" selected>High Priority</option>
                                        <option value="medium">Medium Priority</option>
                                        <option value="low">Low Priority</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="text-muted small fw-semibold d-block mb-1">Category</label>
                                    <span class="badge bg-light text-dark border"><i class="ti ti-server me-1"></i> Infrastructure</span>
                                </div>

                                <div class="mb-3">
                                    <label class="text-muted small fw-semibold d-block mb-1">Requester Info</label>
                                    <div class="small">
                                        <div><i class="ti ti-user me-1 text-muted"></i> Maria Santos</div>
                                        <div><i class="ti ti-mail me-1 text-muted"></i> m.santos@clinic.edu</div>
                                        <div><i class="ti ti-building me-1 text-muted"></i> Registrar Dept</div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- UI Component Custom Styles -->
    <style>
        /* Avatar Circular Badges */
        .avatar-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
        }

        .avatar-circle-sm {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
        }

        /* Scroll Area Controls */
        .ticket-list-scroll {
            max-height: 520px;
            overflow-y: auto;
        }

        .helpdesk-chat-area {
            max-height: 420px;
            min-height: 360px;
        }

        /* Message Bubbles */
        .chat-bubble {
            font-size: 0.9rem;
        }

        .incoming-bubble {
            background-color: var(--bs-light, #f8f9fa);
            border: 1px solid var(--bs-border-color, #dee2e6);
        }

        .outgoing-bubble {
            background-color: var(--bs-primary, #0d6efd);
            max-width: 85%;
        }

        /* Dark Mode Adjustments */
        [data-bs-theme="dark"] .incoming-bubble {
            background-color: #1a222c;
            border-color: #2b3544;
        }

        [data-bs-theme="dark"] .dark-text-light {
            color: #f8f9fa !important;
        }

        /* Active Ticket Item */
        .ticket-list-scroll .list-group-item.active {
            background-color: var(--bs-primary, #0d6efd);
            border-color: var(--bs-primary, #0d6efd);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Bootstrap tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Auto-scroll chat area to bottom
            var chatBox = document.querySelector('.helpdesk-chat-area');
            if (chatBox) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        });
    </script>
@endsection