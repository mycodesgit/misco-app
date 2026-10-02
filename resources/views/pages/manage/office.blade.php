@extends('layouts.app')

@section('title')
    MIS Ticketing | All Offices
@endsection

@section('body')
    <div class="row">
        <div class="col-12">
            <div class="mb-4">

                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1">IT Support Offices</h1>
                        <p class="text-muted small mb-0">Manage IT Support Offices.</p>
                    </div>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-4">
                        <div class="card card-animate">
                            <div class="card-header pt-3">
                                <h6 class="card-title">
                                    <i class="ti ti-plus"></i> Add New
                                </h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" id="addOffice">
                                    @csrf

                                    <div class="form-group mb-3">
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">Office Name: <span class="text-danger">*</span></label>
                                                <input type="text" name="office_name" class="form-control form-control-sm" oninput="this.value = this.value.toUpperCase()" placeholder="Enter office name" required>
                                            </div>

                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">Abbreviation: <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control form-control-sm" oninput="this.value = this.value.toUpperCase()" name="office_abbr" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="form-row">
                                            <div class="col-md-12">
                                                <button type="submit" class="btn btn-outline-success">
                                                    <i class="fas fa-save"></i> Save
                                                </button>
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
                                    <i class="fas fa-list"></i> List
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive p-2">
                                    <table id="officeTable" class="table table-hover" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>Office</th>
                                                <th>Abbreviation</th>
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

    <div class="modal fade" id="editOfficeModal" tabindex="-1" role="dialog" aria-labelledby="editOfficeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="editOfficeModalLabel"><i class="ti ti-pencil"></i> Edit Office Name</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editOfficeForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editOfficeId">
                        <div class="col-md-12 mb-3">
                            <label for="editOfficeName" class="form-label fw-semibold">Office Name: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editOfficeName" oninput="this.value = this.value.toUpperCase()" name="office_name" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="editOfficeAbbr" class="form-label fw-semibold">Abbreviation: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editOfficeAbbr" oninput="this.value = this.value.toUpperCase()" name="office_abbr" required>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Save changes</button>
                    </div>
                </form>
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
        var officeReadRoute = "{{ route('office.show') }}";
        var officeCreateRoute = "{{ route('office.create') }}";
        var officeUpdateRoute = "{{ route('office.update', ['id' => ':id']) }}";
        var categoryDeleteRoute = "{{ route('category.delete', ['id' => ':id']) }}";
    </script>
@endsection
