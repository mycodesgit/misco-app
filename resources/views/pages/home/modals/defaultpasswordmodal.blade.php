<div class="modal fade" id="defaultPasswordModal" tabindex="-1" aria-labelledby="defaultPasswordModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="defaultPasswordModalLabel">
                    <i class="fas fa-lock me-2"></i> Change Your Default Password
                </h5>
            </div>

            <form action="#" method="POST" id="defaultPasswordForm">
                @csrf
                <input type="hidden" name="current_password" value="12345678">

                <!-- Modal Body -->
                <div class="modal-body">
                    <div class="alert alert-warning small mb-3">
                        <i class="fas fa-triangle-exclamation me-1"></i>
                        You are using the default password. For your account's security, please set a new password and your contact number before continuing.
                    </div>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">New Password: <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control form-control-sm" placeholder="Minimum 8 characters" required minlength="8" autocomplete="new-password">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Confirm New Password: <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control form-control-sm" placeholder="Re-type new password" required minlength="8" autocomplete="new-password">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Contact Number: <span class="text-danger">*</span></label>
                            <input type="text" name="contactno" class="form-control form-control-sm" placeholder="e.g. Local 104 / 09123456789"
                                   value="{{ auth()->user()->contactno }}" required maxlength="50" autocomplete="tel">
                            <div class="form-text">Used to reach you about your ticket requests.</div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success text-white"><i class="fas fa-floppy-disk me-1"></i> Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Wait for full page load so jQuery and Bootstrap's $.fn.modal (from main.js) are ready
    window.addEventListener('load', function () {
        $('#defaultPasswordModal').modal({ backdrop: 'static', keyboard: false }).modal('show');

        $('#defaultPasswordForm').submit(function (event) {
            event.preventDefault();

            var $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true);

            $.ajax({
                url: "{{ route('account.password') }}",
                type: "POST",
                data: $(this).serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        toastr.success(response.message);

                        // Pre-fill the ticket forms on this page with the new
                        // number (defaultValue so a form reset keeps it)
                        var contactNo = $.trim($('#defaultPasswordForm [name="contactno"]').val());
                        $('#contactno, #selfreset_contactno').each(function () {
                            this.value = contactNo;
                            this.defaultValue = contactNo;
                        });

                        $('#defaultPasswordForm')[0].reset();
                        $('#defaultPasswordModal').modal('hide');
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    var errorMessage = 'An error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    toastr.error(errorMessage);
                },
                complete: function () {
                    $btn.prop('disabled', false);
                }
            });
        });
    });
</script>
