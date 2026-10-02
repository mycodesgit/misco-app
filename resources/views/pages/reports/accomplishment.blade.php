@extends('layouts.app')

@section('title')
    {{ auth()->user()->pageTitle('Ticketing Accomplishment Report') }}
@endsection

@section('body')
    <div class="row">
        <div class="col-12">
            <div class="mb-4">

                <!-- Dashboard Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1">{{ auth()->user()->pageTitle('Ticketing Accomplishment Report') }}</h1>
                        <p class="text-muted small mb-0">Generate your accomplishment reports.</p>
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
                                <form method="GET" action="#" id="reportForm" class="mb-3">
                                    @csrf

                                    <div class="form-group">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Select Date Range: <span class="text-danger">*</span></label>
                                                <input type="text" name="datesearch" class="form-control form-control-sm" id="reservation">
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
                                <div class="chart-grid mt-3">
                                    <div class="chart-card">
                                        <div class="chart-card-header">
                                        </div>
                                        <div class="">
                                            <!-- Placeholder icon (visible when no report) -->
                                            <div id="noReportIcon" class="text-center" style="margin-top: 30px;">
                                                <i class="fas fa-file-pdf fa-10x text-muted"></i>
                                                <p class="mt-2">No report generated yet. Please select dates and generate.</p>
                                            </div>

                                            <!-- Iframe (hidden initially, shown on load) -->
                                            <div id="pdfContainer" style="display: none;">
                                                <iframe id="pdfIframe" src="" width="100%" height="600px" style="border: none;"></iframe>
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
