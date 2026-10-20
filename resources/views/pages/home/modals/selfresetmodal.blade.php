<div class="modal fade" id="createSelfReseticketModal" tabindex="-1" aria-labelledby="createSelfResetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="createSelfResetModalLabel">
                    <i class="fas fa-ticket me-2"></i> Add New Ticket - Reset Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST" id="addNewSelfResetTicket">
                @csrf

                <!-- Modal Body -->
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Support Type / Role (Optional Routing) -->
                        <div class="col-md-4">
                            <label for="selfresetsupport_type" class="form-label fw-semibold">Support Type: <span class="text-danger">*</span></label>
                            <select class="form-control form-control-sm" id="selfresetsupport_type" name="off_id" required>
                                @foreach ($selfresetrole as $dataselfresetrole)
                                    <option value="{{ $dataselfresetrole->id }}">{{ $dataselfresetrole->rolename }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" class="form-control form-control-sm" name="cat_id" value="{{ $selfresetcat->first()->id }}">
                            <input type="hidden" class="form-control form-control-sm" name="subcat_id" value="{{ $selfresetsubcat->first()->id }}">
                            <input type="hidden" class="form-control form-control-sm" name="priority" value="Low">
                            <input type="hidden" class="form-control form-control-sm" name="issue_description" value="Reset Password">
                        </div>

                        <!-- Category -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Category: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" value="Institutional Email / MS Teams" readonly>
                        </div>

                        <!-- Subcategory -->
                        <div class="col-md-4">
                            <label for="subcat_id" class="form-label fw-semibold">Subcategory: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" value="Password Reset" readonly>
                        </div>

                        <div class="col-md-12">
                            <label for="selfreset_assigned_to_display" class="form-label fw-semibold">Assigned to personnel: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="selfreset_assigned_to_display" value="{{ $selfresetpersonnel->map(fn($p) => $p->fname . ' ' . $p->lname)->implode(', ') }}" readonly>
                            <input type="hidden" name="assigned_to" value="{{ $selfresetpersonnel->pluck('id')->implode(',') }}">
                            <input type="hidden" name="off_id" value="{{ $selfresetpersonnel->first()->office_id }}">
                        </div>

                        <!-- Remarks / Notes -->
                        <div class="col-md-12">
                            <label for="remarks" class="form-label fw-semibold">Email:</label>
                            <input class="form-control form-control-sm" id="remarks" name="remarks" value="{{ Auth::guard('web')->user()->email }}">
                        </div>

                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                    <button type="submit" class="btn btn-success text-white">
                        <i class="fas fa-save me-1"></i> Submit Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
