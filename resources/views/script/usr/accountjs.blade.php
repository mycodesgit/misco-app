<script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right"
    };

    $(document).ready(function() {
        $('#updatePasswordForm').submit(function(event) {
            event.preventDefault();

            var $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true);

            $.ajax({
                url: accountPasswordRoute,
                type: "POST",
                data: $(this).serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#updatePasswordForm')[0].reset();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    var errorMessage = 'An error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        try {
                            errorMessage = JSON.parse(xhr.responseText).message || errorMessage;
                        } catch (e) {
                            errorMessage = 'Request failed (HTTP ' + xhr.status + ')';
                        }
                    }
                    toastr.error(errorMessage);
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });

        $('#updateContactForm').submit(function(event) {
            event.preventDefault();

            var $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true);

            $.ajax({
                url: accountContactRoute,
                type: "POST",
                data: $(this).serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        // Show the saved number in the Personal Information card
                        $('#accountContactDisplay').text(response.contactno || 'N/A');
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    var errorMessage = (xhr.responseJSON && xhr.responseJSON.message) || 'An error occurred';
                    toastr.error(errorMessage);
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });
    });
</script>
