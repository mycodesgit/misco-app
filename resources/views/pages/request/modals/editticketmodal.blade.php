<div class="modal fade" id="editTicketModal" tabindex="-1" aria-labelledby="editTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="editTicketModalLabel">
                    <i class="fas fa-pen me-2"></i> Edit Ticket <span class="text-muted" id="editTicketNumber"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST" id="editTicketForm">
                @csrf
                <input type="hidden" name="ticket_id" id="edit_ticket_id">

                <!-- Modal Body -->
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Name -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Name: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" value="{{ auth()->user()->fname }} {{ auth()->user()->lname }}" readonly>
                        </div>

                        <!-- Office / Department -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Requesting Office / Department: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" value="{{ auth()->user()->office->office_abbr }}" readonly>
                        </div>

                        <!-- Category (read-only) -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="edit_category" readonly>
                        </div>

                        <!-- Subcategory (read-only) -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Subcategory: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="edit_subcategory" readonly>
                        </div>

                        <!-- Priority Level -->
                        <div class="col-md-6">
                            <label for="edit_priority" class="form-label fw-semibold">Priority Level: <span class="text-danger">*</span></label>
                            <select class="form-control form-control-sm" id="edit_priority" name="priority" required>
                                <option value="" disabled>Select Priority Level</option>
                                <option value="Low">Low - Minor Issue</option>
                                <option value="Medium">Medium - Work Affected</option>
                                <option value="High">High - Major Impact</option>
                                <option value="Urgent">Urgent - Critical / System Down</option>
                            </select>
                        </div>

                        <!-- Contact Number -->
                        <div class="col-md-6">
                            <label for="edit_contactno" class="form-label fw-semibold">Contact Number: <span class="text-danger">(Optional)</span></label>
                            <input type="text" class="form-control form-control-sm" id="edit_contactno" name="contactno" placeholder="e.g. Local 104 / 09123456789">
                        </div>

                        <!-- Detailed Complaint Description -->
                        <div class="col-md-12">
                            <label for="edit_issue_description" class="form-label fw-semibold">Issue Description: <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="edit_issue_description" name="issue_description" rows="4" placeholder="Explain the problem in detail, including error codes or steps to reproduce..." required></textarea>
                        </div>

                        <!-- Remarks / Notes -->
                        <div class="col-md-12">
                            <label for="edit_remarks" class="form-label fw-semibold">Additional Remarks / Notes:</label>
                            <textarea class="form-control" id="edit_remarks" name="remarks" rows="2" placeholder="Any additional notes (e.g., best time to visit desk, temporary workaround)..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                    <button type="submit" class="btn btn-success text-white">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
