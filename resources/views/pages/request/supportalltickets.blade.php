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
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-12">
                        <ul class="nav nav-pills bg-light p-2 card-animate rounded-2 d-inline-flex mb-2" id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="pills-one-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-one" type="button" role="tab"
                                    aria-controls="pills-one" aria-selected="true">
                                    Pending Tickets
                                </button>
                            </li>
                            &nbsp;
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-two-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-two" type="button" role="tab"
                                    aria-controls="pills-two" aria-selected="false" tabindex="-1">
                                    In Progress Tickets
                                </button>
                            </li>
                            &nbsp;
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-three-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-three" type="button" role="tab"
                                    aria-controls="pills-three" aria-selected="false" tabindex="-1">
                                    Resolved Tickets
                                </button>
                            </li>
                            &nbsp;
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-four-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-four" type="button" role="tab"
                                    aria-controls="pills-four" aria-selected="false" tabindex="-1">
                                    Closed Tickets
                                </button>
                            </li>
                            &nbsp;
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-five-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-five" type="button" role="tab"
                                    aria-controls="pills-five" aria-selected="false" tabindex="-1">
                                    My Tickets
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="pills-one" role="tabpanel" aria-labelledby="pills-one-tab" tabindex="0">
                                <div class="card card-animate">
                                    <div class="card-header pt-3">
                                        <h6 class="card-title">
                                            <i class="fas fa-server"></i> List of all Pending Ticket Section
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table id="ticketpendingTable" class="table table-hover" style="width: 100%">
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

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pills-two" role="tabpanel" aria-labelledby="pills-two-tab" tabindex="0">
                                <div class="card card-animate">
                                    <div class="card-header pt-3">
                                        <h6 class="card-title">
                                            <i class="fas fa-server"></i> List of all In-progress Ticket Section
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table id="ticketprogressTable" class="table table-hover" style="width: 100%">
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

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pills-three" role="tabpanel" aria-labelledby="pills-three-tab" tabindex="0">
                                <div class="card card-animate">
                                    <div class="card-header pt-3">
                                        <h6 class="card-title">
                                            <i class="fas fa-server"></i> List of all Resolved Ticket Section
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table id="ticketresolvedTable" class="table table-hover" style="width: 100%">
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

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pills-four" role="tabpanel" aria-labelledby="pills-four-tab" tabindex="0">
                                <div class="card card-animate">
                                    <div class="card-header pt-3">
                                        <h6 class="card-title">
                                            <i class="fas fa-server"></i> List of all Closed Ticket Section
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table id="ticketclosedTable" class="table table-hover" style="width: 100%">
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

    <script>
        var ticketPendingReadRoute = "{{ route('tickets.showsupportpending') }}";
        var ticketProgressRoute = "{{ route('tickets.showprogress') }}";
        var ticketResolvedRoute = "{{ route('tickets.showresolved') }}";
        var ticketClosedRoute = "{{ route('tickets.showclosed') }}";
        var ticketCreateRoute = "";
    </script>
@endsection
