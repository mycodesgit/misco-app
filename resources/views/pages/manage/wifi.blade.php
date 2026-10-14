@extends('layouts.app')

@section('title')
    Ticketing | Campus Wifi Vouchers
@endsection

@section('body')
    <div class="row">
        <div class="col-12">
            <div class="mb-4">

                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1">Campus Wifi Vouchers</h1>
                        <p class="text-muted small mb-0">Upload voucher codes and track Available / Used status per term.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Available: <strong id="wifi-available-count">0</strong></span>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Used: <strong id="wifi-used-count">0</strong></span>
                    </div>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-4">
                        <div class="card card-animate">
                            <div class="card-header pt-3">
                                <h6 class="card-title">
                                    <i class="ti ti-upload"></i> Upload Vouchers
                                </h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" id="uploadVouchers" enctype="multipart/form-data">
                                    @csrf

                                    <div class="form-group mb-3">
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">School Year: <span class="text-danger">*</span></label>
                                                <input type="text" name="schlyear" id="uploadSchlyear" list="schlyearList" class="form-control form-control-sm" placeholder="e.g. 2026-2027" required>
                                                <datalist id="schlyearList">
                                                    @foreach ($years as $year)
                                                        <option value="{{ $year }}"></option>
                                                    @endforeach
                                                </datalist>
                                            </div>

                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">Semester: <span class="text-danger">*</span></label>
                                                <input type="text" name="semester" id="uploadSemester" list="semesterList" class="form-control form-control-sm" placeholder="e.g. 1st Sem" required>
                                                <datalist id="semesterList">
                                                    @foreach ($semesters as $sem)
                                                        <option value="{{ $sem }}">{{ $sem }}</option>
                                                    @endforeach
                                                </datalist>
                                            </div>

                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">CSV File: <span class="text-danger">*</span></label>
                                                <input type="file" name="csv_file" id="uploadCsvFile" class="form-control form-control-sm" accept=".csv,.txt" required>
                                                <small class="text-muted">One voucher code per row, first column. Header row is skipped. Imported rows default to <strong>Available</strong>.</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-12 d-flex justify-content-between">
                                                <button type="reset" class="btn btn-light"><i class="ti ti-restore"></i> Clear</button>
                                                <button type="submit" class="btn btn-success" id="uploadVouchersBtn"><i class="ti ti-upload"></i> Upload</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card card-animate">
                            <div class="card-header pt-3">
                                <h6 class="card-title">
                                    <i class="ti ti-ticket"></i> Voucher Codes
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-2 mb-3">
                                    <div class="col-md-4">
                                        <select id="filterSchlyear" class="form-control form-control-sm">
                                            <option value="">All School Years</option>
                                            @foreach ($years as $year)
                                                <option value="{{ $year }}">{{ $year }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select id="filterSemester" class="form-control form-control-sm">
                                            <option value="">All Semesters</option>
                                            @foreach ($semesters as $sem)
                                                <option value="{{ $sem }}">{{ ['1' => '1st Sem', '2' => '2nd Sem', '3' => '3rd Sem'][$sem] ?? $sem }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select id="filterStatus" class="form-control form-control-sm">
                                            <option value="">All Status</option>
                                            <option value="1">Available</option>
                                            <option value="0">Used</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="table-responsive p-2">
                                    <table id="wifiTable" class="table table-hover" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>Voucher Code</th>
                                                <th>School Year</th>
                                                <th>Semester</th>
                                                <th>Status</th>
                                                <th width="15%">Actions</th>
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
        var wifiReadRoute = "{{ route('wifi.show') }}";
        var wifiUploadRoute = "{{ route('wifi.upload') }}";
        var wifiStatusRoute = "{{ route('wifi.status') }}";
        var wifiDeleteRoute = "{{ route('wifi.delete', ['id' => ':id']) }}";
    </script>
@endsection
