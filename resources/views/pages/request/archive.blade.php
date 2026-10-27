@extends('layouts.app')

@section('title')
    {{ auth()->user()->pageTitle('Ticket Archive') }}
@endsection

@section('body')
    <div class="row">
        <div class="col-12">
            <div class="mb-4">

                <!-- Page Header -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1">{{ auth()->user()->pageTitle('Ticket Archive') }}</h1>
                        <p class="text-muted small mb-0">Resolved and closed tickets from previous years. Tickets move here automatically when the year changes.</p>
                    </div>

                    <!-- Year Filter -->
                    @if ($years->isNotEmpty())
                        <div class="d-flex align-items-center gap-2">
                            <label for="archiveYearSelect" class="form-label mb-0 small text-muted">Year:</label>
                            <select id="archiveYearSelect" class="form-select form-select-sm">
                                @foreach ($years as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-12">
                        <div class="card card-animate">
                            <div class="card-header pt-3">
                                <h6 class="card-title">
                                    <i class="ti ti-archive"></i> Archived Tickets
                                    @if ($years->isNotEmpty())
                                        &middot; <span id="archiveYearLabel">{{ $years->first() }}</span>
                                    @endif
                                </h6>
                            </div>
                            <div class="card-body">
                                @if ($years->isEmpty())
                                    <div class="text-center text-muted py-5">
                                        <i class="ti ti-archive fs-1 d-block mb-2"></i>
                                        <div class="fw-semibold">No archived tickets yet</div>
                                        <div class="small">Resolved and closed tickets appear here once their year has ended.</div>
                                    </div>
                                @else
                                    <div class="table-responsive p-2">
                                        <table id="ticketArchiveTable" class="table table-hover" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>Ticket No.</th>
                                                    <th>Requester</th>
                                                    <th>Subject</th>
                                                    <th>Category</th>
                                                    <th>Sub-Category</th>
                                                    <th>Support Desk</th>
                                                    <th>Priority</th>
                                                    <th>Status</th>
                                                    <th>Resolved By</th>
                                                    <th>Date Created</th>
                                                    <th>Date Resolved</th>
                                                    <th width="10%">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
                                @endif
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
        var ticketArchiveRoute = "{{ route('tickets.archive.show') }}";
        var ticketViewRoute = "{{ route('tickets.store') }}";
    </script>
@endsection
