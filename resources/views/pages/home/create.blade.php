@extends('layouts.app')

@section('title')
    Create New IT Request
@endsection

@section('body')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="mb-4">
                
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1">
                            <i class="ti ti-ticket me-1 text-primary"></i> Submit New IT Request
                        </h1>
                        <p class="text-muted small mb-0">Fill in the details below and attach photos of the error if applicable.</p>
                    </div>
                    <a href="{{ url('/requests') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="ti ti-x me-1"></i> Cancel
                    </a>
                </div>

                <!-- Create Ticket Form Card -->
                <div class="card">
                    <div class="card-header bg-transparent p-3 border-bottom">
                        <h6 class="mb-0 fw-bold text-primary"><i class="ti ti-forms me-1"></i> Request Information Form</h6>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ url('/requests') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <!-- Subject / Title -->
                            <div class="mb-3">
                                <label for="subject" class="form-label fw-semibold">Issue Title / Subject <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="subject" name="subject" placeholder="e.g. Cannot connect to network printer" required>
                            </div>

                            <div class="row">
                                <!-- Category -->
                                <div class="col-md-6 mb-3">
                                    <label for="category" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="" selected disabled>Select Category</option>
                                        <option value="Hardware">Hardware / Equipment</option>
                                        <option value="Software">Software & Applications</option>
                                        <option value="Network">Network & Wi-Fi</option>
                                        <option value="Account">Account & Portal Access</option>
                                        <option value="Infrastructure">Server & Database</option>
                                    </select>
                                </div>

                                <!-- Priority Level -->
                                <div class="col-md-6 mb-3">
                                    <label for="priority" class="form-label fw-semibold">Priority Level <span class="text-danger">*</span></label>
                                    <select class="form-select" id="priority" name="priority" required>
                                        <option value="Low">Low - Minor Issue</option>
                                        <option value="Medium" selected>Medium - Normal Work Affected</option>
                                        <option value="High">High - Urgent / System Down</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Detailed Complaint Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label fw-semibold">Complaint / Issue Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="5" placeholder="Explain the problem in detail, including error codes or steps to reproduce..." required></textarea>
                            </div>

                            <!-- Photo Attachment Field (Only permitted on Creation) -->
                            <div class="mb-4">
                                <label for="issue_photo" class="form-label fw-semibold">
                                    <i class="ti ti-camera me-1 text-primary"></i> Attach Photo or Error Screenshot
                                </label>
                                <input class="form-control" type="file" id="issue_photo" name="issue_photo" accept="image/*" onchange="previewImage(event)">
                                <div class="form-text small">Upload an image file (.png, .jpg, .jpeg) showing the error message or physical hardware issue.</div>
                                
                                <!-- Image Preview Container -->
                                <div id="preview-container" class="mt-3 d-none">
                                    <span class="d-block small text-muted mb-1">Image Preview:</span>
                                    <img id="image-preview" src="#" alt="Photo Preview" class="img-thumbnail rounded" style="max-height: 200px;">
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Form Buttons -->
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ url('/requests') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="ti ti-file-upload me-1"></i> Submit Request
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Image Preview Script -->
    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('image-preview');
                output.src = reader.result;
                document.getElementById('preview-container').classList.remove('d-none');
            };
            if(event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        }
    </script>
@endsection