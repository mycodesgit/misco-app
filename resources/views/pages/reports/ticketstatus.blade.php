@extends('layouts.app')

@section('title')
    {{ auth()->user()->pageTitle('Ticket Status Report') }}
@endsection

@section('body')
    <div class="row">
        <div class="col-12">
            <div class="mb-4">

                <!-- Dashboard Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1">{{ auth()->user()->pageTitle('Ticket Status Report') }}</h1>
                        <p class="text-muted small mb-0">View all Daily Ticket Request statuses and generate a PDF report.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="tsOpenPdfBtn" disabled>
                            Export Report
                        </button>
                    </div>
                </div>

                <div class="row g-3 mb-5">
                    <!-- Search Card -->
                    <div class="col-md-12">
                        <div class="card card-animate">
                            <div class="card-header pt-3">
                                <h6 class="card-title">
                                    <i class="ti ti-search"></i> Search to show data
                                </h6>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="#" id="ticketStatusForm" class="mb-3">
                                    @csrf

                                    <div class="form-group">
                                        <div class="row g-3">
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">Month: <span class="text-danger">*</span></label>
                                                <select id="tsFilterMonth" class="form-control form-control-sm" required>
                                                    @foreach ([
                                                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
                                                    ] as $monthValue => $monthName)
                                                        <option value="{{ str_pad($monthValue, 2, '0', STR_PAD_LEFT) }}" @selected($monthValue === (int) date('n'))>
                                                            {{ $monthName }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">Year: <span class="text-danger">*</span></label>
                                                <select id="tsFilterYear" class="form-control form-control-sm" required>
                                                    @php $currentYear = date('Y'); @endphp
                                                    @for ($y = $currentYear; $y >= $currentYear - 5; $y--)
                                                        <option value="{{ $y }}">{{ $y }}</option>
                                                    @endfor
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Support Office:</label>
                                                <select id="tsFilterOffice" class="form-control form-control-sm select2bs4" {{ ($isAdmin ?? false) ? '' : 'disabled' }}>
                                                    @if ($isAdmin ?? false)
                                                        <option value="">All Offices</option>
                                                    @endif
                                                    @foreach ($offices as $office)
                                                        <option value="{{ $office->id }}" @selected(!($isAdmin ?? false) && auth()->user()->office_id === $office->id)>
                                                            {{ $office->office_abbr }} - {{ $office->office_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @unless ($isAdmin ?? false)
                                                    <small class="text-muted">Locked to your office.</small>
                                                @endunless
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">Status:</label>
                                                <select id="tsFilterStatus" class="form-control form-control-sm">
                                                    <option value="">All Statuses</option>
                                                    <option value="Pending">Pending</option>
                                                    <option value="In Progress">In Progress</option>
                                                    <option value="Resolved">Resolved</option>
                                                    <option value="Cancelled">Cancelled</option>
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="d-flex flex-column h-100">
                                                    <label class="form-label fw-semibold opacity-0 d-none d-md-block">Action</label>
                                                    <button type="submit" class="btn btn-success btn-sm btn-block">Search</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <small class="text-muted d-block">
                                    <i class="ti ti-info-circle me-1"></i>
                                    The PDF always summarises every status for the selected month. Choosing a status only filters the ticket list.
                                </small>

                                <div class="page-header mt-3" style="border-bottom: 1px solid #04401f;"></div>

                                <div class="chart-grid mt-3">
                                    <div class="chart-card">
                                        <div class="chart-card-header"></div>
                                        <div>
                                            <!-- Placeholder icon (visible when no report) -->
                                            <div id="tsNoReportIcon" class="text-center" style="margin-top: 30px;">
                                                <i class="fas fa-file-pdf fa-10x text-muted"></i>
                                                <p class="mt-2">No report generated yet. Please select a month and year, then search.</p>
                                            </div>

                                            <!-- Iframe (hidden initially, shown after search) -->
                                            <div id="tsPdfContainer" style="display: none;">
                                                <iframe id="tsPdfIframe" src="" width="100%" height="750px" style="border: none;"></iframe>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
