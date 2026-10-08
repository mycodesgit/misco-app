@extends('layouts.app')

@section('title')
    {{ auth()->user()->pageTitle('Client Feedback Report') }}
@endsection

@section('body')
    <div class="row">
        <div class="col-12">
            <div class="mb-4">

                <!-- Dashboard Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1">{{ auth()->user()->pageTitle('Client Feedback Report') }}</h1>
                        <p class="text-muted small mb-0">Search client feedback per month and year.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary">
                            Export Report
                        </button>
                    </div>
                </div>

                <!-- Top Row: Metric Cards -->
                <div class="row g-3 mb-5">
                    <div class="col-md-12">
                        <div class="card card-animate">
                            <div class="card-header pt-3">
                                <h6 class="card-title">
                                    <i class="ti ti-search"></i> Search to show data
                                </h6>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="#" id="clientfeedbackForm" class="mb-3">
                                    @csrf

                                    <div class="form-group">
                                        <div class="row g-3">
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">Month: <span class="text-danger">*</span></label>
                                                <select id="filterMonth" class="form-control form-control-sm" required>
                                                    <option value="01">January</option>
                                                    <option value="02">February</option>
                                                    <option value="03">March</option>
                                                    <option value="04">April</option>
                                                    <option value="05">May</option>
                                                    <option value="06">June</option>
                                                    <option value="07">July</option>
                                                    <option value="08">August</option>
                                                    <option value="09">September</option>
                                                    <option value="10">October</option>
                                                    <option value="11">November</option>
                                                    <option value="12">December</option>
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">Year: <span class="text-danger">*</span></label>
                                                <select id="filterYear" class="form-control form-control-sm" required>
                                                    @php $currentYear = date('Y'); @endphp
                                                    @for ($y = $currentYear; $y >= $currentYear - 5; $y--)
                                                        <option value="{{ $y }}">{{ $y }}</option>
                                                    @endfor
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Office:</label>
                                                <select id="filterOffice" class="form-control form-control-sm select2bs4" {{ ($isAdmin ?? false) ? '' : 'disabled' }}>
                                                    @if ($isAdmin ?? false)
                                                        <option value="">All Offices</option>
                                                    @endif
                                                    @foreach ($offices as $office)
                                                        <option value="{{ $office->id }}">{{ $office->office_abbr }} - {{ $office->office_name }}</option>
                                                    @endforeach
                                                </select>
                                                @unless ($isAdmin ?? false)
                                                    <small class="text-muted">Locked to your office.</small>
                                                @endunless
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">User:</label>
                                                <select id="filterUser" class="form-control form-control-sm" disabled>
                                                    <option value="">All Users</option>
                                                </select>
                                                <small class="text-muted" id="filterUserHint">Select an office first.</small>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="d-flex flex-column h-100">
                                                    <label class="form-label fw-semibold opacity-0 d-none d-md-block">Action</label>
                                                    <button type="submit" class="btn btn-success btn-sm btn-block">Search</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <div class="page-header" style="border-bottom: 1px solid #04401f;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="card card-animate">
                            <div class="card-header pt-3 d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0">
                                    <i class="ti ti-star"></i> Total Rating Summary
                                </h6>
                                <span class="badge bg-light text-dark border" id="cf-scope-label">-</span>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-3 text-center border-end">
                                        <div class="text-muted small">Total Responses</div>
                                        <h2 class="fw-bold mb-0" id="cf-total-count">0</h2>
                                    </div>
                                    <div class="col-md-3 text-center border-end">
                                        <div class="text-muted small">Average Rating</div>
                                        <h2 class="fw-bold mb-0"><span id="cf-avg-value">0</span><small class="text-muted fs-6"> / 5</small></h2>
                                        <div id="cf-avg-stars"></div>
                                    </div>
                                    <div class="col-md-6">
                                        @foreach ([5, 4, 3, 2, 1] as $star)
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <small class="fw-semibold text-muted" style="width: 28px;">{{ $star }}★</small>
                                                <div class="progress flex-grow-1" style="height: 8px;">
                                                    <div class="progress-bar bg-warning" id="cf-bar-{{ $star }}" role="progressbar" style="width: 0%;"></div>
                                                </div>
                                                <small class="fw-bold" style="width: 30px;" id="cf-count-{{ $star }}">0</small>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="card card-animate">
                            <div class="card-header pt-3">
                                <h6 class="card-title">
                                    <i class="ti ti-file-report"></i> Client Feedback List Section
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive p-2">
                                    <table id="clientfeedbackTable" class="table table-hover" style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th>Ticket No.</th>
                                                <th>Requester</th>
                                                <th>Office</th>
                                                <th>Resolved By</th>
                                                <th>Category</th>
                                                <th>Sub Category</th>
                                                <th>Rating</th>
                                                <th>Feedback</th>
                                                <th>Date</th>
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

    <script>
        var clientfeedbackReadRoute = "{{ route('clientfeedback.show') }}";
        var clientfeedbackUsersRoute = "{{ route('clientfeedback.users') }}";
    </script>
@endsection
