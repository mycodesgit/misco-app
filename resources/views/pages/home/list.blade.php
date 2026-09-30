@extends('layouts.app')

@section('title')
    IT Requests & Tickets
@endsection

@section('body')
    <div class="row">
        <div class="col-12">
            <div class="mb-4">
                
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1">
                            <i class="ti ti-list-check me-1 text-primary"></i> IT Support Requests
                        </h1>
                        <p class="text-muted small mb-0">Manage incoming user issues, track ongoing fixes, and view closed tickets.</p>
                    </div>
                    <a href="{{ url('/tickets/create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
                        <i class="ti ti-plus me-1"></i> Submit New Ticket
                    </a>
                </div>

                <!-- Requests Card -->
                <div class="card">
                    <div class="card-header bg-transparent p-3 border-bottom">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                            
                            <!-- Navigation Tabs -->
                            <ul class="nav nav-tabs card-header-tabs" id="ticketTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active fw-semibold" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                                        <i class="ti ti-clock me-1 text-warning"></i> Pending 
                                        <span class="badge bg-warning text-dark rounded-pill ms-1">3</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fw-semibold" id="progress-tab" data-bs-toggle="tab" data-bs-target="#progress" type="button" role="tab">
                                        <i class="ti ti-progress me-1 text-primary"></i> In Progress / Working 
                                        <span class="badge bg-primary rounded-pill ms-1">2</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fw-semibold" id="closed-tab" data-bs-toggle="tab" data-bs-target="#closed" type="button" role="tab">
                                        <i class="ti ti-circle-check me-1 text-success"></i> Closed 
                                        <span class="badge bg-secondary rounded-pill ms-1">12</span>
                                    </button>
                                </li>
                            </ul>

                            <!-- Search Filter -->
                            <div class="input-group input-group-sm style-search-box" style="max-width: 260px;">
                                <span class="input-group-text bg-transparent text-muted"><i class="ti ti-search"></i></span>
                                <input type="text" class="form-control" placeholder="Search ticket # or subject...">
                            </div>

                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="tab-content" id="ticketTabsContent">
                            
                            <!-- TAB 1: PENDING -->
                            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Ticket ID</th>
                                                <th>Subject & Category</th>
                                                <th>Requester</th>
                                                <th>Priority</th>
                                                <th>Submitted</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold">#TK-8942</td>
                                                <td>
                                                    <div class="fw-semibold text-dark">Database Connection Timeout</div>
                                                    <small class="text-muted"><i class="ti ti-server me-1"></i> Infrastructure</small>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="avatar-circle-sm bg-primary text-white fw-bold">MS</div>
                                                        <div>
                                                            <div class="fw-semibold small">Maria Santos</div>
                                                            <small class="text-muted">Registrar Dept</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-danger rounded-pill"><i class="ti ti-alert-triangle me-1"></i> High</span></td>
                                                <td class="small text-muted">10 mins ago</td>
                                                <td class="text-end">
                                                    <a href="{{ url('/tickets/8942') }}" class="btn btn-outline-primary btn-sm rounded-pill">
                                                        <i class="ti ti-eye me-1"></i> View & Process
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">#TK-8941</td>
                                                <td>
                                                    <div class="fw-semibold text-dark">Network Printer Offline</div>
                                                    <small class="text-muted"><i class="ti ti-printer me-1"></i> Hardware</small>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="avatar-circle-sm bg-info text-white fw-bold">JD</div>
                                                        <div>
                                                            <div class="fw-semibold small">John Doe</div>
                                                            <small class="text-muted">Finance Office</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-warning text-dark rounded-pill">Medium</span></td>
                                                <td class="small text-muted">1 hour ago</td>
                                                <td class="text-end">
                                                    <a href="{{ url('/tickets/8941') }}" class="btn btn-outline-primary btn-sm rounded-pill">
                                                        <i class="ti ti-eye me-1"></i> View & Process
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- TAB 2: IN PROGRESS -->
                            <div class="tab-pane fade" id="progress" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Ticket ID</th>
                                                <th>Subject & Category</th>
                                                <th>Requester</th>
                                                <th>Assigned Tech</th>
                                                <th>Priority</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold">#TK-8935</td>
                                                <td>
                                                    <div class="fw-semibold text-dark">Portal Password Reset</div>
                                                    <small class="text-muted"><i class="ti ti-user-lock me-1"></i> Account Access</small>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold small">Alex Cruz</div>
                                                    <small class="text-muted">Clinic Dept</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark border"><i class="ti ti-user-check me-1"></i> Admin Staff</span>
                                                </td>
                                                <td><span class="badge bg-info rounded-pill">Low</span></td>
                                                <td class="text-end">
                                                    <a href="{{ url('/tickets/8935') }}" class="btn btn-primary btn-sm rounded-pill">
                                                        <i class="ti ti-message-dots me-1"></i> Open Chat
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- TAB 3: CLOSED -->
                            <div class="tab-pane fade" id="closed" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Ticket ID</th>
                                                <th>Subject</th>
                                                <th>Requester</th>
                                                <th>Resolved Date</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold text-muted">#TK-8901</td>
                                                <td>
                                                    <div class="fw-semibold text-muted">Monitor Display Flicker</div>
                                                </td>
                                                <td><span class="small text-muted">Maria Santos</span></td>
                                                <td class="small text-muted">Sep 25, 2026</td>
                                                <td class="text-end">
                                                    <a href="{{ url('/tickets/8901') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                                                        <i class="ti ti-file-text me-1"></i> View Summary
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
        .avatar-circle-sm {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
        }
    </style>
@endsection