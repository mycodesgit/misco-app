<div class="modal fade" id="createNewTicketModal" tabindex="-1" aria-labelledby="createNewTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="createNewTicketModalLabel">
                    <i class="fas fa-ticket me-2"></i> Add New Ticket
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST" id="addNewTicket" enctype="multipart/form-data">
                @csrf

                <!-- Modal Body -->
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Name -->
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">Name: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" value="{{ auth()->user()->fname }} {{ auth()->user()->lname }}" readonly>
                        </div>

                        <!-- Office / Department -->
                        <div class="col-md-6">
                            <label for="off_id" class="form-label fw-semibold">Requesting Office / Department: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" value="{{ auth()->user()->office->office_abbr }}" readonly>
                        </div>

                        <!-- Support Type / Role (Optional Routing) -->
                        <div class="col-md-4">
                            <label for="support_type" class="form-label fw-semibold">Support Type: <span class="text-danger">*</span></label>
                            <select class="form-control form-control-sm select2bs4" id="support_type"  name="support_type" required>
                                <option value="" selected disabled>Select Support Type</option>
                                @foreach ($urole as $role)
                                    <option value="{{ $role->rolename }}" data-off-id="{{ $role->off_id }}">{{ $role->rolename }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" id="support_type_hidden" name="off_id" value="">
                        </div>

                        <!-- Category -->
                        <div class="col-md-4">
                            <label for="cat_id" class="form-label fw-semibold">Category: <span class="text-danger">*</span></label>
                            <select class="form-control form-control-sm select2bs4" id="category" name="cat_id" required disabled>
                                <option value="" selected disabled>Select Category</option>
                            </select>
                        </div>

                        <!-- Subcategory -->
                        <div class="col-md-4">
                            <label for="subcat_id" class="form-label fw-semibold">Subcategory: <span class="text-danger">*</span></label>
                            <select class="form-control form-control-sm select2bs4" id="subcategory" name="subcat_id" required disabled>
                                <option value="" selected disabled>Select Subcategory</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label for="assigned_to_display" class="form-label fw-semibold">Assigned to personnel: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="assigned_to_display" name="assigned_to[]" placeholder="Select a category first" readonly>
                            <input type="hidden" name="assigned_to" id="assigned_to">
                        </div>

                        <!-- Priority Level -->
                        <div class="col-md-6">
                            <label for="priority" class="form-label fw-semibold">Priority Level: <span class="text-danger">*</span></label>
                            <select class="form-control form-control-sm" id="priority" name="priority" required>
                                <option value="" selected disabled>Select Priority Level</option>
                                <option value="Low">Low - Minor Issue</option>
                                <option value="Medium">Medium - Work Affected</option>
                                <option value="High">High - Major Impact</option>
                                <option value="Urgent">Urgent - Critical / System Down</option>
                            </select>
                        </div>

                        <!-- Contact Number -->
                        <div class="col-md-6">
                            <label for="contactno" class="form-label fw-semibold">Contact Number: <span class="text-danger">(Optional)</span></label>
                            <input type="text" class="form-control form-control-sm" id="contactno" name="contactno" placeholder="e.g. Local 104 / 09123456789">
                        </div>

                        <!-- Detailed Complaint Description -->
                        <div class="col-md-12">
                            <label for="issue_description" class="form-label fw-semibold">Issue Description: <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="issue_description" name="issue_description" rows="4" placeholder="Explain the problem in detail, including error codes or steps to reproduce..." required></textarea>
                        </div>

                        <!-- Remarks / Notes -->
                        <div class="col-md-12">
                            <label for="remarks" class="form-label fw-semibold">Additional Remarks / Notes:</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="2" placeholder="Any additional notes (e.g., best time to visit desk, temporary workaround)..."></textarea>
                        </div>

                        <!-- Attachment Field -->
                        <div class="col-md-12">
                            <label for="attachment" class="form-label fw-semibold">
                                <i class="ti ti-camera me-1 text-primary"></i> Attach Photo or Screenshot
                            </label>
                            <input class="form-control form-control-sm" type="file" id="attachment" name="attachment" accept="image/*" onchange="previewImage(event)">
                            <div class="form-text small">Upload an image file (.png, .jpg, .jpeg) showing the error message or hardware issue.</div>

                            <!-- Image Preview Container -->
                            <div id="preview-container" class="mt-3 d-none">
                                <span class="d-block small text-muted mb-1">Image Preview:</span>
                                <img id="image-preview" src="#" alt="Photo Preview" class="img-thumbnail rounded" style="max-height: 200px;">
                            </div>
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
