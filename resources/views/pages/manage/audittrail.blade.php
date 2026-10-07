@extends('layouts.app')

@section('title')
    {{ auth()->user()->pageTitle('Audit Trail Logs') }}
@endsection

@section('body')
    <div class="row">
        <div class="col-12">
            <div class="mb-4">

                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1">{{ auth()->user()->pageTitle('Audit Trail Logs') }}</h1>
                        <p class="text-muted small mb-0">Search audit logs per category, month and year.</p>
                    </div>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-12">
                        <div class="card card-animate">
                            <div class="card-header pt-3">
                                <h6 class="card-title">
                                    <i class="ti ti-search"></i> Search to show data
                                </h6>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="#" id="audittrailForm" class="mb-3">
                                    @csrf

                                    <div class="form-group">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Audit Category: <span class="text-danger">*</span></label>
                                                <select id="filterCategory" class="form-control form-control-sm" required>
                                                    @foreach ($categories as $key => $label)
                                                        <option value="{{ $key }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-3">
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

                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Year: <span class="text-danger">*</span></label>
                                                <select id="filterYear" class="form-control form-control-sm" required>
                                                    @php $currentYear = date('Y'); @endphp
                                                    @for ($y = $currentYear; $y >= $currentYear - 5; $y--)
                                                        <option value="{{ $y }}">{{ $y }}</option>
                                                    @endfor
                                                </select>
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
                            <div class="card-header pt-3">
                                <h6 class="card-title">
                                    <i class="ti ti-list"></i> Audit Trail List Section
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive p-2">
                                    <table id="audittrailTable" class="table table-hover" style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Email</th>
                                                <th>Action</th>
                                                <th>IP Address</th>
                                                <th>User Agent</th>
                                                <th>Date</th>
                                                <th width="5%">View</th>
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

    <div class="modal fade" id="actionDataModal" tabindex="-1" aria-labelledby="actionDataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="actionDataModalLabel">Action Data Payload</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <pre id="actionDataPayload" class="json-viewer mb-0"><code></code></pre>
                </div>
            </div>
        </div>
    </div>

    <style>
        .json-viewer {
            background: #0d0d0d;
            border-radius: 10px;
            padding: 16px 18px;
            max-height: 420px;
            overflow: auto;
            font-family: Consolas, Menlo, monospace;
            font-size: 0.82rem;
            line-height: 1.6;
            color: #d4d4d4;
            white-space: pre;
        }
        .json-viewer .jk { color: #9cdcfe; }
        .json-viewer .js { color: #ce9178; }
        .json-viewer .jn { color: #b5cea8; }
        .json-viewer .jb { color: #569cd6; }
        .json-viewer .jnl { color: #808080; }
    </style>

    <script>
        var audittrailReadRoute = "{{ route('audittrail.show') }}";
    </script>
@endsection
