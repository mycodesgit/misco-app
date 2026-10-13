<div class="modal fade" id="stationModal" tabindex="-1" aria-labelledby="stationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stationModalLabel">
                    <i class="ti ti-map-pin me-2"></i> Update My Station
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <p class="text-muted small mb-3">Where are you working right now? This updates everywhere instantly.</p>

                <div class="d-flex flex-column gap-2" role="radiogroup" aria-label="Station">
                    <label class="border rounded-3 px-3 py-2 d-flex align-items-center gap-2 mb-0 station-option" style="cursor:pointer;">
                        <input class="form-check-input mt-0" type="radio" name="station" value="Office" {{ auth()->user()->station === 'Office' ? 'checked' : '' }}>
                        <i class="ti ti-building"></i>
                        <span class="fw-semibold">Office</span>
                        <small class="text-muted ms-auto">On-site at the office</small>
                    </label>
                    <label class="border rounded-3 px-3 py-2 d-flex align-items-center gap-2 mb-0 station-option" style="cursor:pointer;">
                        <input class="form-check-input mt-0" type="radio" name="station" value="Field" {{ auth()->user()->station === 'Field' ? 'checked' : '' }}>
                        <i class="ti ti-briefcase"></i>
                        <span class="fw-semibold">Field</span>
                        <small class="text-muted ms-auto">Out on field work</small>
                    </label>
                    <label class="border rounded-3 px-3 py-2 d-flex align-items-center gap-2 mb-0 station-option" style="cursor:pointer;">
                        <input class="form-check-input mt-0" type="radio" name="station" value="Leave" {{ auth()->user()->station === 'Leave' ? 'checked' : '' }}>
                        <i class="ti ti-calendar-off"></i>
                        <span class="fw-semibold">Leave</span>
                        <small class="text-muted ms-auto">On leave / unavailable</small>
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="saveStationBtn">
                    <i class="ti ti-check me-1"></i> Save Station
                </button>
            </div>
        </div>
    </div>
</div>
