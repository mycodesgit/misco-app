@extends('layouts.app')

@section('title')
    {{ auth()->user()->pageTitle('Ticketing Requests') }}
@endsection

@section('body')
    <div class="row">
        <div class="col-12">
            <div class="mb-4">

                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1">{{ auth()->user()->pageTitle('Ticket Requests') }}</h1>
                        <p class="text-muted small mb-0">Manage incoming user issues, track ongoing fixes, and view closed tickets.</p>
                    </div>
                    <a href="{{ url('/tickets/create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
                        <i class="ti ti-plus me-1"></i> Submit New Ticket
                    </a>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-12">
                        <div class="card card-animate">
                            <div class="card-header pt-3">
                                <h6 class="card-title">
                                    <i class="fas fa-server"></i> List of all Ticket Section
                                </h6>
                            </div>
                            <div class="card-body">
                                <table id="categoryTable" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Ticket No.</th>
                                            <th>Requester</th>
                                            <th>Subject</th>
                                            <th>Category</th>
                                            <th>Sub-Category</th>
                                            <th>Status</th>
                                            <th width="10%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold">#TK-8942</td>
                                            <td>
                                                <div class="fw-semibold">Database Connection Timeout</div>
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
                                            <td><span class="badge bg-primary"><i class="ti ti-alert-triangle me-1"></i> New Ticket</span></td>
                                            <td>
                                                <a href="{{ url('/tickets/8942') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                                                    <i class="ti ti-eye me-1"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">#TK-8941</td>
                                            <td>
                                                <div class="fw-semibold">Network Printer Offline</div>
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
                                            <td><span class="badge bg-primary"><i class="ti ti-alert-triangle me-1"></i> New Ticket</span></td>
                                            <td>
                                                <a href="{{ url('/tickets/8941') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                                                    <i class="ti ti-eye me-1"></i> View
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
